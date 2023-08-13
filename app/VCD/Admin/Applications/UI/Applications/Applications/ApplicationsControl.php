<?php

namespace VCD\Admin\Applications\UI;

use Hafo\Persona\HumanAge;
use HuraTabory\API\V1\UserProfile\Transformer\ReservationTransformer;
use Psr\Container\ContainerInterface;
use VCD\UI\BaseControl;
use VCD2\Applications\Application;
use VCD2\Applications\Service\Applications;
use VCD2\Applications\Service\Invoices;
use VCD2\Applications\Service\Payments;
use VCD2\Emails\Service\Emails\ApplicationAcceptedMail;
use VCD2\Emails\Service\Emails\ApplicationAppliedMail;
use VCD2\Emails\Service\Emails\ApplicationPaidMail;
use VCD2\Emails\Service\Emails\ApplicationRejectedMail;
use VCD2\Orm;

class ApplicationsControl extends BaseControl
{
    private $container;

    private $orm;

    private $applications;

    private $payments;

    /** @var Application[] */
    private $filteredApplications = [];

    public function __construct(ContainerInterface $container, $filters = [], $savedFilter = null, $pinnedFilters = false)
    {
        $this->container = $container;
        $this->orm = $container->get(Orm::class);
        $this->applications = $container->get(Applications::class);
        $this->payments = $container->get(Payments::class);

        $this->onAnchor[] = function () use ($filters, $savedFilter, $pinnedFilters) {
            $filtersControl = new ApplicationsFiltersControl($this->container, $filters, $savedFilter, $pinnedFilters);
            $this->addComponent($filtersControl, 'filters');

            $orm = $this->container->get(Orm::class);

            $condition = $filtersControl->createQueryFilters();

            $applications = $orm->applications->findAllForAdmin()->findBy(/*$cond*/$condition);
            $filteredApplications = [];
            foreach ($applications as $application) {
                if ($filtersControl->postFilter($application)) {
                    $filteredApplications[] = $application;
                }
            }

            $this->template->filters = $filters;
            $this->template->pinnedFilters = $pinnedFilters;

            $this->template->age = function ($dateBorn) {
                return (new HumanAge($dateBorn))->yearsAt(new \DateTime);
            };
            $this->template->birthday = function ($dateBorn, $year = null) {
                $year = $year === null ? date('Y') : $year;

                return (new \DateTime)->setDate($year, $dateBorn->format('n'), $dateBorn->format('j'));
            };
            $this->template->longSentence = function ($text) {
                if (substr_count($text, ' ') < 2) {
                    return $text;
                }

                $parts = explode(' ', $text);

                return $parts[0] . '...' . $parts[count($parts) - 1];
            };

            $this->template->acceptConfirm = function (Application $application) {
                $s = 'Schválit přihlášku?';
                if ($application->user !== null) {
                    if (!$application->user->emailVerified) {
                        $s .= ' Uživatel nemá ověřenou e-mailovou adresu!';
                    }
                    if (!$application->user->phoneVerified) {
                        $s .= ' Uživatel nemá ověřené telefonní číslo!';
                    }
                }

                return $s;
            };

            $this->template->applications = $this->filteredApplications = $filteredApplications;
        };
    }

    public function handleExportJson()
    {
        $transformer = $this->container->get(ReservationTransformer::class);
        $data = [];

        foreach ($this->filteredApplications as $application) {
            $data[] = $transformer->transform($application);
        }

        header('Content-Encoding: UTF-8');
        header('Content-Transfer-Encoding: binary');
        header('Content-Type: application/json');
        header('Expires: 0');
        header('Cache-Control: no-cache, must-revalidate, post-check=0, pre-check=0');

        $this->presenter->sendJson($data);
        $this->presenter->terminate();
    }

    public function handleExportCsv()
    {
        $fields = [
            'ID přihlášky',
            'Jméno a příjmení dítěte',
            'Datum narození',
            'Plavec',
            'ADHD',
            'Zdravotní stav',
            'Poznámka',
            'Doplňky',
            'Jméno a příjmení zákonného zástupce',
            'E-mail',
            'Telefon',
            'Adresa',
        ];

        $countSteps = 0;
        foreach ($this->filteredApplications as $application) {
            foreach ($application->stepChoices as $stepChoice) {
                if (array_search($stepChoice->step->prompt, $fields, true) === false) {
                    $fields[] = $stepChoice->step->prompt;
                    $countSteps++;
                }
            }
        }


        $data = [];
        foreach ($this->filteredApplications as $application) {
            $addons = [];
            foreach ($application->addons as $applicationAddon) {
                if ($applicationAddon->amount === 0) {
                    continue;
                }
                $addons[] = $applicationAddon->amount . '× ' . $applicationAddon->addon->name;
            }
            foreach ($application->children as $child) {
                $row = [
                    $application->id,
                    $child->name,
                    $child->dateBorn->format('d.m.Y'),
                    $child->swimmer ? 'Ano' : 'Ne',
                    $child->adhd ? 'Ano' : 'Ne',
                    $child->health,
                    $application->notes,
                    implode(', ', $addons),
                    $application->name,
                    $application->email,
                    $application->phone,
                    $application->street . "\n" . $application->city . ' ' . $application->zip,
                ];
                for ($i = 0; $i < $countSteps; $i++) {
                    $row[] = '';
                }
                foreach ($application->stepChoices as $stepChoice) {
                    $key = array_search($stepChoice->step->prompt, $fields, true);
                    $row[$key] = $stepChoice->option->option;
                }
                $data[] = $row;
            }
        }

        ob_start();
        $fp = fopen('php://output', 'wb');
        fwrite($fp, "\xEF\xBB\xBF");
        fputcsv($fp, $fields);
        foreach ($data as $row) {
            fputcsv($fp, $row);
        }
        $size = ob_get_length();

        header('Content-Encoding: UTF-8');
        header('Content-Transfer-Encoding: binary');
        header('Content-Type: application/csv;charset=UTF-8');
        header('Content-Disposition: attachment; filename=export.csv');
        header('Expires: 0');
        header('Cache-Control: no-cache, must-revalidate, post-check=0, pre-check=0');
        header('Content-Length: ' . $size);

        //echo "\xEF\xBB\xBF";

        fclose($fp);

        die;
    }

    public function handleApplicationAccept($id)
    {
        $this->presenter->tryExecute(function () use ($id) {
            $this->applications->acceptApplication($id);
            /** @var Application $application */
            $application = $this->container->get(Orm::class)->applications->get($id);
            $this->presenter->flashMessage(sprintf('Přihláška byla schválena%s.', $application->event->acceptedEmail === null ? '' : ', potvrzující e-mail odeslán'), 'success');
            if ($application->event->acceptedEmail === null) {
                $this->presenter->flashMessage('Potvrzující e-mail nebyl odeslán, protože není nastaven.', 'warning');
            }
        });
        $this->presenter->redirect('this');
    }

    public function handleApplicationReject($id, $reason = null)
    {
        $this->presenter->tryExecute(function () use ($id, $reason) {
            $this->applications->rejectApplication($id, $reason);
            $this->presenter->flashMessage('Přihláška byla odmítnuta, e-mail odeslán.', 'success');
        });
        $this->presenter->redirect('this');
    }

    public function handleApplicationPay($id, $amount = null)
    {
        $this->presenter->tryExecute(function () use ($id, $amount) {
            $application = $this->orm->applications->get($id);
            $payment = $this->payments->receivePayment($application, $amount);
            $this->presenter->flashMessage('Přijata platba ' . $payment->amount . ' Kč, e-mail odeslán.', 'success');
        });
        $this->presenter->redirect('this');
    }

    public function handleMailApplied($id)
    {
        $this->container->get(ApplicationAppliedMail::class)->send($id);
        $this->presenter->flashMessage('E-mail odeslán.', 'success');
        $this->presenter->redirect('this');
    }
    public function handleMailAccepted($id)
    {
        $this->container->get(ApplicationAcceptedMail::class)->send($id);
        $this->presenter->flashMessage('E-mail odeslán.', 'success');
        $this->presenter->redirect('this');
    }
    public function handleMailRejected($id)
    {
        $this->container->get(ApplicationRejectedMail::class)->send($id);
        $this->presenter->flashMessage('E-mail odeslán.', 'success');
        $this->presenter->redirect('this');
    }
    public function handleMailPaid($id)
    {
        $this->container->get(ApplicationPaidMail::class)->send($id);
        $this->presenter->flashMessage('E-mail odeslán.', 'success');
        $this->presenter->redirect('this');
    }

    public function handleInvoices()
    {
        $created = $this->container->get(Invoices::class)->createInvoices();
        if ($created > 0) {
            $this->presenter->flashMessage(sprintf('Vygenerováno %s faktur.', $created), 'success');
        } else {
            $this->presenter->flashMessage('Žádné faktury k vygenerování.', 'info');
        }
        $this->presenter->redirect('this');
    }

    public function handleCreateInvoice($id)
    {
        $application = $this->orm->applications->get($id);
        $this->container->get(Invoices::class)->createInvoiceToBePaid($application);
        $this->presenter->flashMessage('Faktura byla vytvořena.', 'success');
        $this->presenter->redirect('this');
    }

    public function handleLoadSteps($event)
    {
        $eventEntity = $this->orm->events->get((int)$event);

        $data = [];
        if ($eventEntity !== null) {
            foreach ($eventEntity->steps as $step) {
                $options = [];
                foreach ($step->options as $option) {
                    $options[$option->id] = $option->option;
                }

                $data['step' . $step->id] = [
                    'prompt' => $step->prompt,
                    'options' => [null => '---'] + $options,
                ];
            }
        }

        $this->presenter->sendJson($data);
    }

    public function render()
    {
        $this->template->setFile(__DIR__ . '/default.latte');
        $this->template->id = $this->getUniqueId();
        $this->template->render();
    }
}
