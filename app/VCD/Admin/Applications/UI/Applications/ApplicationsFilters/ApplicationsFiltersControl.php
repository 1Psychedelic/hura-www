<?php

namespace VCD\Admin\Applications\UI;

use Nette\Application\UI\Form;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use Nextras\Orm\Collection\ICollection;
use VCD2\Applications\Application;
use VCD2\Events\Event;
use VCD2\UI\Admin\Filters\AdminFiltersControl;
use VCD2\UI\Admin\Forms\AdminFormRenderer;

class ApplicationsFiltersControl extends AdminFiltersControl
{
    public const STATUS_NEW_OR_ACCEPTED = 0;
    public const STATUS_NEW = 1;
    public const STATUS_ALL_EXCEPT_UNFINISHED = 2;
    public const STATUS_ACCEPTED_ONLY = 3;
    public const STATUS_REJECTED_ONLY = 4;
    public const STATUS_UNFINISHED_ONLY = 5;

    public const PAYMENT_STATUS_ALL = 0;
    public const PAYMENT_STATUS_UNPAID = 1;
    public const PAYMENT_STATUS_PAID = 2;

    public const RESERVE_STATUS_ALL = 0;
    public const RESERVE_STATUS_NO = 1;
    public const RESERVE_STATUS_YES = 2;

    public const VIP_STATUS_ALL = 0;
    public const VIP_STATUS_YES = 1;
    public const VIP_STATUS_NO = 2;

    public const ADDON_ALL = 0;
    public const ADDON_YES = 1;
    public const ADDON_NO = 2;

    protected function getGroupName(): string
    {
        return 'applications';
    }

    protected function createForm(): Form
    {
        $cache = new Cache($this->container->get(IStorage::class), get_class($this));

        $event = $this->getEvent();

        $f = new Form();

        $f->setRenderer(new AdminFormRenderer());

        $events = $cache->load('events');
        if ($events === null) {
            $events = $this->orm->events->findSelectOptionsForAdmin();
            $cache->save('events', $events, [
                Cache::EXPIRE => '1 hour',
            ]);
        }

        $f->addGroup('Akce');
        $f->addSelect('event', 'Akce', $events)
            ->setPrompt('(Všechny akce)')
            ->getControlPrototype()->addClass('select2');

        if ($event !== null) {
            foreach ($event->steps as $step) {
                $options = [];
                foreach ($step->options as $option) {
                    $options[$option->id] = $option->option;
                }

                $f->addRadioList('step' . $step->id, $step->prompt, [0 => '*'] + $options)
                    ->setDefaultValue(0);
                //->setPrompt('---');
            }

            $discounts = [];
            foreach ($event->discounts as $discount) {
                $discounts[$discount->id] = '#' . $discount->id . ' ' . $discount->price . ' Kč / ' . $discount->priceVip . ' Kč';
            }
            $f->addSelect('discount', 'Sleva / cena', [null => '*'] + $discounts);

            $options = [];
            foreach ($event->addons as $eventAddon) {
                $options[$eventAddon->id] = $eventAddon->name;
            }
            $f->addCheckboxList('addons', 'Pouze s doplňky', $options);
        }

        $f->addGroup('Stav');

        $f->addRadioList('status', 'Stav přihlášky', [
            self::STATUS_NEW_OR_ACCEPTED => 'Pouze nové nebo schválené',
            self::STATUS_NEW => 'Pouze nové čekající',
            self::STATUS_ALL_EXCEPT_UNFINISHED => 'Všechny kromě nedokončených',
            self::STATUS_ACCEPTED_ONLY => 'Pouze schválené',
            self::STATUS_REJECTED_ONLY => 'Pouze zrušené',
            self::STATUS_UNFINISHED_ONLY => 'Pouze nedokončené',
        ])->setDefaultValue(self::STATUS_NEW_OR_ACCEPTED);

        $f->addRadioList('payment_status', 'Stav platby', [
            self::PAYMENT_STATUS_ALL => 'Všechny',
            self::PAYMENT_STATUS_UNPAID => 'Pouze nezaplacené',
            self::PAYMENT_STATUS_PAID => 'Pouze zaplacené',
        ])->setDefaultValue(self::PAYMENT_STATUS_ALL);

        $f->addRadioList('reserve', 'Náhradníci', [
            self::RESERVE_STATUS_ALL => 'Účastníci i náhradníci',
            self::RESERVE_STATUS_NO => 'Pouze účastníci',
            self::RESERVE_STATUS_YES => 'Pouze náhradníci',
        ])->setDefaultValue(self::RESERVE_STATUS_ALL);

        $f->addRadioList('isVip', 'VIP', [
            self::VIP_STATUS_ALL => 'VIP i běžní uživatelé',
            self::VIP_STATUS_YES => 'Pouze VIP',
            self::VIP_STATUS_NO => 'Pouze běžní uživatelé',
        ])->setDefaultValue(self::VIP_STATUS_ALL);

        $users = $cache->load('users');
        if ($users === null) {
            $users = $this->orm->users->findSelectOptionsForAdmin();
            $cache->save('users', $users, [
                Cache::EXPIRE => '1 hour',
            ]);
        }

        $f->addCheckbox('paying_on_invoice', 'Pouze hrazené zaměstnavatelem');

        $f->addGroup('Uživatel');
        $f->addText('id', 'ID přihlášky');
        $f->addSelect('user', 'Uživatel', $users)
            ->setPrompt('(Kdokoliv)')
            ->getControlPrototype()->addClass('select2');

        $f->addText('search_email', 'Hledat e-mail');
        $f->addText('search_name', 'Hledat jméno rodiče');
        $f->addText('search_child_name', 'Hledat jméno dítěte');

        $f->setCurrentGroup(null);

        $f->addSubmit('filter', 'Filtrovat');
        $f->onSuccess[] = function (Form $f) {
            if ($f->isSubmitted() === $f['filter']) {
                $data = $f->getValues(true);
                if ((int)($data['event'] ?? 0) !== (int)($this->httpData['event'] ?? 0)) {
                    unset($data['addons'], $data['discount']);
                }
                $this->presenter->redirect('this', ['filters' => array_filter($data), 'savedFilter' => null]);
            }
        };

        $f->setDefaults($this->httpData);

        return $f;
    }

    public function createQueryFilters(): array
    {
        $event = $this->getEvent();

        $filters = [];
        $orFilters = [
            ICollection::OR,
        ];

        switch ($this->httpData['status'] ?? self::STATUS_NEW_OR_ACCEPTED) {
            case self::STATUS_NEW_OR_ACCEPTED:
                $filters['isApplied'] = true;
                $filters['isRejected'] = false;
                $filters['isCanceled'] = false;
                
                break;
            case self::STATUS_NEW: // Pouze nové čekající
                $filters['isApplied'] = true;
                $filters['isAccepted'] = false;
                $filters['isRejected'] = false;
                $filters['isCanceled'] = false;
//                $filters['appliedAt!='] = null;
//                $filters['acceptedAt'] = null;
//                $filters['canceledAt'] = null;
//                $filters['rejectedAt'] = null;

                break;
            case self::STATUS_ALL_EXCEPT_UNFINISHED: // Všechny kromě nedokončených
                $filters['isApplied'] = true;
//                $filters['appliedAt!='] = null;

                break;
            case self::STATUS_ACCEPTED_ONLY: // Pouze schválené
                $filters['isApplied'] = true;
                $filters['isAccepted'] = true;
                $filters['isRejected'] = false;
                $filters['isCanceled'] = false;
//                $filters['acceptedAt!='] = null;
//                $filters['canceledAt'] = null;
//                $filters['rejectedAt'] = null;

                break;
            case self::STATUS_REJECTED_ONLY: // Pouze zrušené
                $filters['isApplied'] = true;
                $orFilters['isRejected'] = true;
                $orFilters['isCanceled'] = true;
//                $orFilters['canceledAt!='] = null;
//                $orFilters['rejectedAt!='] = null;

                break;
            case self::STATUS_UNFINISHED_ONLY: // Pouze nedokončené
                $filters['isApplied'] = false;
//                $filters['appliedAt'] = null;

                break;
        }

        switch ($this->httpData['payment_status'] ?? self::PAYMENT_STATUS_ALL) {
            case self::PAYMENT_STATUS_UNPAID: // Pouze nezaplacené
                $filters['paidAt'] = null;

                break;
            case self::PAYMENT_STATUS_PAID: // Pouze zaplacené
                $filters['paidAt!='] = null;

                break;
        }

        switch ($this->httpData['reserve'] ?? self::RESERVE_STATUS_ALL) {
            case self::RESERVE_STATUS_NO: // Pouze účastníci
                $filters['isReserve'] = false;

                break;
            case self::RESERVE_STATUS_YES: // Pouze náhradníci
                $filters['isReserve'] = true;

                break;
        }

        switch ($this->httpData['isVip'] ?? self::VIP_STATUS_ALL) {
            case self::VIP_STATUS_YES: // Pouze VIP
                $filters['isVip'] = true;

                break;
            case self::VIP_STATUS_NO: // Pouze běžní uživatelé
                $filters['isVip'] = false;

                break;
        }

        if (isset($this->httpData['event'])) {
            $filters['event'] = $this->httpData['event'];
        }

        if ($this->httpData['paying_on_invoice'] ?? false) {
            $filters['isPayingOnInvoice'] = true;
        }

        if ($event !== null) {
            foreach ($event->steps as $step) {
                $options = [];
                foreach ($step->options as $option) {
                    $options[$option->id] = $option->option;
                }

                if (isset($this->httpData['step' . $step->id])) {
                    $filters['this->stepChoices->option->id'] = $this->httpData['step' . $step->id];
                }
            }

            if (isset($this->httpData['discount'])) {
                $filters['this->discount->id'] = $this->httpData['discount'];
            }
        }

        if (isset($this->httpData['user'])) {
            $filters['user'] = $this->httpData['user'];
        }

        if (isset($this->httpData['id'])) {
            $filters['id'] = $this->httpData['id'];
        }

        return count($orFilters) === 1 ? $filters : [ICollection::AND, $filters, $orFilters];
    }

    public function postFilter(Application $application): bool
    {
        $ok = true;

        if (isset($this->httpData['search_email'])) {
            $ok = $ok && strpos($application->email, $this->httpData['search_email']) !== false;
        }

        if (isset($this->httpData['search_name'])) {
            $ok = $ok && strpos($application->name, $this->httpData['search_name']) !== false;
        }

        if (isset($this->httpData['search_child_name'])) {
            $found = false;
            foreach ($application->children as $child) {
                if (strpos($child->name, $this->httpData['search_child_name']) !== false) {
                    $found = true;

                    break;
                }
            }
            $ok = $ok && $found;
        }

        $event = $this->getEvent();
        if ($event !== null) {
            if (isset($this->httpData['addons'])) {
                $requiredAddons = array_map('intval', $this->httpData['addons']);
                $applicationAddons = [];
                foreach ($application->addons as $applicationAddon) {
                    if ($applicationAddon->amount > 0) {
                        $applicationAddons[] = $applicationAddon->addon->id;
                    }
                }

                $ok = $ok && count(array_intersect($applicationAddons, $requiredAddons)) === count($requiredAddons);
            }
        }

        return $ok;
    }

    private function getEvent(): ?Event
    {
        return isset($this->httpData['event']) ? $this->orm->events->get($this->httpData['event']) : null;
    }
}
