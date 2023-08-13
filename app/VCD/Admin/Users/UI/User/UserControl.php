<?php

namespace VCD\Admin\Users\UI;

use Psr\Container\ContainerInterface;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Database\Context;
use Nette\Utils\Arrays;
use Nextras\Orm\Collection\ICollection;
use Nextras\Orm\InvalidStateException;
use Tomaj\Form\Renderer\BootstrapRenderer;
use VCD2\Orm;
use VCD2\UI\Admin\Forms\AdminFormRenderer;
use VCD2\Users\Child;
use VCD2\Users\User;
use VCD2\Users\UserRole;

// todo $user->children->set() is broken in ORM
class UserControl extends Control {

    private $container;

    function __construct(ContainerInterface $container, $id = NULL) {
        $this->container = $container;

        $this->onAnchor[] = function() use ($id) {
            
            $orm = $this->container->get(Orm::class);
            
            $children = [];
            foreach($orm->children->findAll()->orderBy('id', ICollection::DESC) as $child) {
                /** @var Child $child */
                $children[$child->id] = sprintf('#%s %s (%s)', $child->id, $child->name, $child->dateBorn->format('d. m. Y'));
            }
            
            $roles = [
                UserRole::ROLE_ADMIN => UserRole::ROLE_ADMIN,
                UserRole::ROLE_NOTIFY => UserRole::ROLE_NOTIFY,
            ];
            
            $f = new Form;
            $f->setRenderer(new AdminFormRenderer);

            $f->addGroup('Základní údaje');
            $f->addText('name', 'Jméno a příjmení');
            $f->addText('email', 'E-mail');
            $f->addText('phone', 'Telefon');
            $f->addText('city', 'Město');
            $f->addText('street', 'Ulice');
            $f->addText('zip', 'PSČ');
            $f->addXMultiSelect('userChildren', 'Děti', $children, '(Žádné)', FALSE, TRUE);
            $f->addXMultiSelect('roles', 'Role', $roles, '(Žádné)', FALSE, TRUE);

            $f->addGroup('Příznaky');
            $f->addCheckbox('emailVerified', 'E-mail ověřen');
            $f->addCheckbox('phoneVerified', 'Telefon ověřen');
            $f->addCheckbox('agreedPersonalData', 'Souhlas se zpracováním osobních údajů');
            $f->addCheckbox('agreedTermsAndConditions', 'Souhlas se smluvními podmínkami');
            $f->addCheckbox('agreedPhotography', 'Souhlas s pořizováním snímků');
            $f->addCheckbox('zeroEventsPrice', 'Má všechny akce zdarma');
            $f->addCheckbox('isPayingOnInvoice', 'Úhrada zaměstnavatelem')
                ->addCondition(Form::EQUAL, TRUE)
                ->toggle('invoice');

            $f->addGroup('Zaměstnavatel')
                ->setOption('id', 'invoice');
            $f->addText('invoiceName', 'Název společnosti');
            $f->addText('invoiceIco', 'IČO');
            $f->addText('invoiceDic', 'DIČ');
            $f->addText('invoiceCity', 'Město');
            $f->addText('invoiceStreet', 'Ulice');
            $f->addText('invoiceZip', 'PSČ');

            //$f->addText('qr_hash', 'QR kód pro přihlášení');
            //$f->addText('events_participated', 'Počet zúčastněných událostí');

            $f->addGroup('Přihlašování');
            $f->addText('facebookId', 'Facebook ID');
            $f->addText('googleId', 'Google ID');

            $f->setCurrentGroup(NULL);
            $f->addSubmit('save', 'Uložit');
            if($id !== NULL) {
                $f->addSubmit('delete', 'Odstranit')->getControlPrototype()
                    ->attrs['onclick'] = 'if(!confirm("Opravdu smazat?")){return false;}';
            }
            $f->onSuccess[] = function(Form $f) use ($id, $orm) {
                if($f->isSubmitted() === $f['save']) {
                    $data = $f->getValues(TRUE);
                    $children = Arrays::pick($data, 'userChildren', []);
                    $roles = Arrays::pick($data, 'roles', []);
                    $zeroEventsPrice = Arrays::pick($data, 'zeroEventsPrice', false);

                    /** @var User $user */
                    if($id === NULL) {
                        $user = User::createFromArray($data);
                    } else {
                        $user = $orm->users->get($id);
                        $user->setValues($data);
                    }

                    foreach($user->roles as $role) {
                        $orm->remove($role);
                    }
                    foreach($roles as $role) {
                        $user->roles->add(new UserRole($user, $role));
                    }

                    $user->children->set($children);

                    if (!$zeroEventsPrice && $user->vipLevel > 1) {
                        $user->vipLevel = 0;
                    } elseif ($zeroEventsPrice) {
                        $user->vipLevel = 2;
                    }

                    $orm->persistAndFlush($user);

                    $this->presenter->flashMessage('Uloženo.', 'success');
                    $this->presenter->redirect('users');
                } else if($id !== NULL && $f->isSubmitted() === $f['delete']) {
                    $user = $orm->users->get($id);
                    try {
                        $orm->remove($user);
                        $orm->flush();
                    } catch (InvalidStateException $e) {
                        $this->presenter->flashMessage('Uživatele není možné odstranit, protože v databázi existují přidružené záznamy (např. přihlášky, pohyby kreditů atd).', 'danger');
                        $this->presenter->redirect('this');
                    }

                    $this->presenter->flashMessage('Uživatel byl smazán.', 'success');
                    $this->presenter->redirect('users');
                }
            };
            if($id !== NULL) {
                /** @var User $user */
                $user = $orm->users->get($id);
                $data = $user->getValues();
                unset($data['roles']);
                $data['zeroEventsPrice'] = $user->vipLevel > 1;
                $f->setValues($data);
                $f['userChildren']->setValue($user->children->get()->fetchPairs(NULL, 'id'));
                $f['roles']->setValue($user->roles->get()->fetchPairs(NULL, 'role'));
            }
            $this->addComponent($f, 'form');
        };
    }

    function render() {
        $this->template->setFile(__DIR__ . '/default.latte');
        $this->template->render();
    }

}
