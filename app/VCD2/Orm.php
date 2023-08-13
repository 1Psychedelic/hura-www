<?php

namespace VCD2;

use Hafo\Fio;
use Hafo\GoPay;
use Hafo\Orm\Model\Model;
use VCD2\Applications;
use VCD2\Carousel;
use VCD2\Credits;
use VCD2\Discounts;
use VCD2\Ebooks;
use VCD2\Emails;
use VCD2\Events;
use VCD2\FacebookImages;
use VCD2\Firebase;
use VCD2\Gallery;
use VCD2\Users;
use VCD2\PostOffice;
use VCD2\Reviews;

/**
 * @property-read Applications\Repository\ApplicationRepository $applications
 * @property-read Applications\Repository\ChildRepository $applicationChildren
 * @property-read Applications\Repository\StepChoiceRepository $applicationStepChoices
 * @property-read Applications\Repository\InvoiceRepository $invoices
 * @property-read Applications\Repository\InvoiceItemRepository $invoiceItems
 * @property-read Applications\Repository\PaymentRepository $payments
 * @property-read Applications\Repository\ApplicationAddonRepository $applicationAddons
 * @property-read GoPay\Repository\PaymentRepository $gopayPayments
 * @property-read Fio\Repository\PaymentRepository $fioPayments
 * @property-read Applications\Repository\PaymentMethodRepository $paymentMethods
 * @property-read Credits\Repository\CreditMovementRepository $creditMovements
 * @property-read Credits\Repository\CreditRepository $credits
 * @property-read Discounts\Repository\DiscountCodeRepository $discountCodes
 * @property-read Discounts\Repository\DiscountRepository $discounts
 * @property-read Emails\Repository\EmailRepository $emails
 * @property-read Emails\Repository\AttachmentRepository $emailAttachments
 * @property-read Events\Repository\EventRepository $events
 * @property-read Events\Repository\EventTabRepository $eventTabs
 * @property-read Events\Repository\EventImageRepository $eventImages
 * @property-read Events\Repository\EventAddonRepository $eventAddons
 * @property-read Events\Repository\ApplicationStepRepository $eventSteps
 * @property-read Events\Repository\ApplicationStepOptionRepository $eventStepOptions
 * @property-read Users\Repository\UserRepository $users
 * @property-read Users\Repository\ChildRepository $children
 * @property-read Users\Repository\UserRoleRepository $userRoles
 * @property-read Users\Repository\UserSessionRepository $userSessions
 * @property-read Ebooks\Repository\EbookRepository $ebooks
 * @property-read Ebooks\Repository\EbookDownloadRepository $ebookDownloads
 * @property-read Ebooks\Repository\EbookDownloadLinkRepository $ebookDownloadLinks
 * @property-read Carousel\Repository\CarouselRepository $carousels
 * @property-read Carousel\Repository\CarouselPageRepository $carouselPages
 * @property-read Gallery\Repository\PhotoRepository $photos
 * @property-read Users\Repository\ConsentRepository $consents
 * @property-read PostOffice\Repository\LetterRepository $letters
 * @property-read Reviews\Repository\ReviewRepository $reviews
 * @property-read FacebookImages\Repository\FacebookImageRepository $facebookImages
 * @property-read Firebase\Repository\FirebasePushTokenRepository $firebasePushTokens
 */
class Orm extends Model {

}
