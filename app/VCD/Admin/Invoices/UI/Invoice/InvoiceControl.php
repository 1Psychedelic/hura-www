<?php

namespace VCD\Admin\Invoices\UI;

use Hafo\DI\Container;
use Hafo\NetteBridge\Forms\FormFactory;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Http\FileUpload;
use Nette\Utils\Arrays;
use Nette\Utils\FileSystem;
use Nextras\Dbal\Connection;
use Tomaj\Form\Renderer\BootstrapRenderer;
use VCD2\Applications\Invoice;
use VCD2\Applications\InvoiceItem;
use VCD2\Applications\PaymentMethod;
use VCD2\Orm;

class InvoiceControl extends Control {
    
    private $orm;

    private $connection;

    function __construct(Container $container, $id) {

        $this->orm = $container->get(Orm::class);
        $this->connection = $container->get(Connection::class);
        
        $this->onAnchor[] = function() use ($container, $id) {

            /** @var Form $f */
            $f = $container->get(FormFactory::class)->create();

            $f->setRenderer(new BootstrapRenderer);

            $f->addText('application', 'ID přihlášky')->setNullable();

            $f->addText('variableSymbol', 'Variabilní symbol')
                ->setNullable()
                ->addConditionOn($f['application'], Form::BLANK)
                    ->toggle('toggleVariableSymbol');

            $f->addText('name', 'Jméno a příjmení')->setRequired();
            $f->addText('street', 'Ulice')->setRequired();
            $f->addText('city', 'Město')->setRequired();
            $f->addText('zip', 'PSČ')->setRequired();
            $f->addText('ico', 'IČO')->setNullable();
            $f->addText('dic', 'DIČ')->setNullable();

            $f->addDateTimePicker('createdAt', 'Datum vystavení')->setNullable();
            $f->addDateTimePicker('payTill', 'Datum splatnosti')->setNullable();

            $f->addCheckbox('isPaid', 'Zaplaceno');
            
            $f->addSelect('paymentMethod', 'Platební metoda', $this->orm->paymentMethods->findSelectOptions())
                ->setPrompt('(Nevybráno)');

            $f->addTextArea('notes', 'Poznámka')->setNullable();

            $f->addUpload('customFile', 'Vlastní faktura (nahradí automaticky generovanou fakturu)');

            $items = $f->addContainer('invoiceItems');

            if($id !== NULL) {
                /** @var Invoice $invoice */
                $invoice = $this->orm->invoices->get($id);
                if(!$invoice) {
                    throw new BadRequestException;
                }
                foreach($invoice->items as $item) {
                    $this->createItemContainer($items, $item->id);
                }
            }

            $this->createItemContainer($items, 'empty');

            if (isset($invoice) && $invoice->customFile !== null) {
                $f->addCheckbox('deleteCustomFile', 'Smazat vlastní fakturu a přejít zpět na automaticky generovanou');
            }

            $f->addSubmit('save', 'Uložit');

            $f->onValidate[] = function(Form $f) use ($id) {
                $data = $f->getValues(TRUE);

                if($data['application']) {
                    $application = $this->orm->applications->get($data['application']);
                    if($application === NULL) {
                        $f->addError('Přihláška s tímto ID neexistuje.');
                        return;
                    }
                    if($application->invoice !== NULL && $application->invoice->id !== (int)$id) {
                        $f->addError('Přihláška s tímto ID už má fakturu.');
                        return;
                    }
                    if((bool)Arrays::get($data, 'isPaid', FALSE) && !$application->isPaid) {
                        $f->addError('Přihláška s tímto ID není zaplacená.');
                    }
                }
            };
            $f->onSuccess[] = function(Form $f) use ($id, $container) {
                if($f->isSubmitted() === $f['save']) {
                    $formData = $f->getValues(TRUE);
                    $data = $f->getHttpData();

                    $this->connection->transactional(function () use ($data, $formData, $id, $container) {

                        $lastIdThisYear = $this->orm->invoices->getCountForYear((new \DateTimeImmutable)->format('Y'));

                        $invoice = NULL;
                        if($id === NULL) {
                            $invoice = Invoice::create(
                                ++$lastIdThisYear,
                                $data['name'],
                                $data['city'],
                                $data['street'],
                                $data['zip'],
                                $data['ico'],
                                $data['dic'],
                                NULL,
                                $data['variableSymbol'],
                                (bool)Arrays::get($data, 'isPaid', FALSE),
                                $formData['payTill'],
                                $formData['createdAt']
                            );
                        } else {
                            $invoice = $this->orm->invoices->get($id);
                            $invoice->name = $data['name'];
                            $invoice->city = $data['city'];
                            $invoice->street = $data['street'];
                            $invoice->zip = $data['zip'];
                            $invoice->ico = $data['ico'];
                            $invoice->dic = $data['dic'];
                            $invoice->variableSymbol = $data['variableSymbol'];
                            if(!empty($formData['createdAt'])) {
                                $invoice->createdAt = $formData['createdAt'];
                            }
                            $invoice->payTill = $formData['payTill'];
                            $invoice->isPaid = (bool)Arrays::get($data, 'isPaid', FALSE);
                        }

                        if($data['paymentMethod']) {
                            $invoice->paymentMethod = $this->orm->paymentMethods->get($data['paymentMethod']);
                        } else {
                            $invoice->paymentMethod = NULL;
                        }

                        if($data['application']) {
                            $invoice->application = $this->orm->applications->get($data['application']);
                        } else {
                            $invoice->application = NULL;
                        }

                        $invoice->notes = $formData['notes'];

                        $file = $data['customFile'];
                        if ($file instanceof FileUpload && $file->isOk()) {
                            $dir = $container->get('invoices');
                            FileSystem::createDir($dir);
                            $filename = $invoice->invoiceId . '.pdf';
                            $file->move($dir . '/' . $filename);
                            $invoice->customFile = $filename;
                        } elseif ($data['deleteCustomFile'] ?? false) {
                            $dir = $container->get('invoices');
                            $filename = $invoice->invoiceId . '.pdf';
                            FileSystem::delete($dir . '/' . $filename);
                            $invoice->customFile = null;
                        }

                        $this->orm->persist($invoice);

                        $existing = [];
                        $new = [];
                        foreach($data['invoiceItems'] as $name => $itemData) {
                            if($name === 'empty') {
                                continue;
                            }

                            $itemId = $itemData['id'] === 'empty' ? NULL : $itemData['id'];
                            $item = NULL;
                            if($itemId === NULL) {
                                $item = new InvoiceItem($invoice, $itemData['name'], $itemData['basePrice'], $itemData['amount'], $itemData['totalPrice']);
                                $new[] = $item;
                            } else {
                                $existing[] = intval($itemId);
                                $item = $this->orm->invoiceItems->get($itemId);
                                $item->setValues($itemData);
                            }
                            $this->orm->persist($item);
                        }

                        foreach($invoice->items as $item) {
                            if($item->isPersisted() && !in_array($item->id, $existing, TRUE) && !in_array($item, $new, TRUE)) {
                                $this->orm->remove($item);
                            }
                        }

                        $this->orm->flush();

                    });

                    $this->presenter->flashMessage('Uloženo.', 'success');
                    $this->presenter->redirect('invoices');
                }
            };
            if(isset($invoice)) {
                $data = $invoice->getValues();
                unset($data['items']);
                $f->setValues($data);
                foreach($invoice->items as $item) {
                    $f['invoiceItems'][$item->id]->setValues($item->getValues());
                }
            } else {
                /** @var PaymentMethod|NULL $firstPaymentMethod */
                $firstPaymentMethod = $this->orm->paymentMethods->findEnabled()->fetch();
                if ($firstPaymentMethod !== NULL) {
                    $f['paymentMethod']->setValue($firstPaymentMethod->id);
                }
            }
            $this->addComponent($f, 'form');

            $this->template->id = isset($invoice) ? $invoice->invoiceId : NULL;
        };

    }

    private function createItemContainer(\Nette\Forms\Container $form, $name) {
        $container = $form->addContainer($name);
        $container->addHidden('id', $name);
        $container->addTextArea('name', 'Položka');
        $container->addText('basePrice', 'Cena');
        $container->addText('amount', 'Množství');
        $container->addText('totalPrice', 'Celková cena');
        return $container;
    }

    function render() {
        $this->template->setFile(__DIR__ . '/default.latte');
        $this->template->payTillDefault = Invoice::PAY_TILL_DEFAULT;
        $this->template->render();
    }

}
