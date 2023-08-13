<?php
declare(strict_types=1);

namespace HuraTabory\API\V1\UserProfile\Transformer;


use HuraTabory\API\V1\Event\Transformer\EventTermTransformer;
use VCD2\Applications\Application;

class ReservationTransformer
{
    /** @var EventTermTransformer */
    private $eventTermTransformer;

    public function __construct(EventTermTransformer $eventTermTransformer)
    {
        $this->eventTermTransformer = $eventTermTransformer;
    }

    public function transform(Application $application): array
    {
        $data = [
            'event' => [
                'id' => $application->event->id,
                'name' => $application->event->name,
                'price' => $application->baseEventPrice,
                'date' => $this->eventTermTransformer->transform($application->event->starts, $application->event->ends, true),
            ],
            'reservation' => [
                'id' => $application->id,
                'notes' => $application->notes,
                'price' => $application->price,
                'priceText' => $application->price . ' Kč',
                'deposit' => $application->deposit,
                'isPaid' => $application->isPaid,
                'isDepositPaid' => $application->isDepositPaid,
                'status' => $application->status,
                'paid' => $application->paid,
                'canBePaidFor' => $application->canBePaidFor,
                'paymentDueDate' => $application->event->applicableUntil === null ? 'začátku akce' : $application->event->applicableUntil->format('j. n. Y'),
                'discounts' => [
                    'payingByCredit' => $application->paidByCredit,
                    'payingByDiscountCode' => $application->discountCode === null ? 0 : $application->discountCode->discount,
                    'discountCode' => $application->discountCode === null ? '' : $application->discountCode->code,
                ],
                'isPayingOnInvoice' => $application->isPayingOnInvoice,
                'invoiceData' => [
                    'isFilled' => $application->hasValidInvoiceInfo,
                    'name' => $application->invoiceName,
                    'ico' => $application->invoiceIco,
                    'dic' => $application->invoiceDic,
                    'city' => $application->invoiceCity,
                    'street' => $application->invoiceStreet,
                    'zip' => $application->invoiceZip,
                    'notes' => $application->invoiceNotes,
                ],
            ],
            'parent' => [
                'name' => $application->name,
                'phone' => $application->phone,
                'email' => $application->email,
                'street' => $application->street,
                'city' => $application->city,
                'zip' => $application->zip,
                'agreeGdpr' => $application->agreedPersonalData,
                'agreeTermsAndConditions' => $application->agreedTermsAndConditions,
            ],
            'children' => [],
            'addons' => [],
        ];

        foreach ($application->children as $child) {
            $data['children'][] = [
                'childId' => $child->child->id,
                'name' => $child->name,
                'gender' => $child->gender,
                'adhd' => $child->adhd,
                'dateBorn' => $child->dateBorn->format('Y-m-d'),
                'swimmer' => $child->swimmer,
                'firstTimer' => true,
                'health' => $child->health,
            ];
        }

        foreach ($application->addons as $addon) {
            $eventAddon = $addon->addon;
            $data['addons'][] = [
                'name' => $eventAddon->name,
                'price' => $addon->price,
                'amount' => $addon->amount,
            ];
        }

        return $data;
    }
}
