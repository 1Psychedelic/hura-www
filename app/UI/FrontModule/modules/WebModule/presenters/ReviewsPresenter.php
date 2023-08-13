<?php
declare(strict_types=1);

namespace VCD\UI\FrontModule\WebModule;

use Hafo\NetteBridge\Forms\FormFactory;
use Nette\Application\UI\Form;
use Nette\Database\Context;
use Nette\Utils\Html;
use Tomaj\Form\Renderer\BootstrapRenderer;
use VCD2\Credits\Credit;
use VCD2\Reviews\Service\Reviews;
use VCD2\Users\Consent;
use VCD2\Users\Service\Consents;
use VCD2\Users\Service\Credits;

class ReviewsPresenter extends BasePresenter
{
    const LINK_DEFAULT = ':Front:Web:Reviews:default';

    public function actionDefault()
    {
        $db = $this->container->get(Context::class);
        $reviews = $this->container->get(Reviews::class);
        $user = $this->userContext->getEntity();
        $this->template->reviews = $reviews->getReviews();
        $this->template->showForm = $showForm = $reviews->canPostReview($user);
        $this->template->content = $db->table('vcd_page')->where('slug = "recenze" AND special = 1')->fetch()['content'];

        if ($showForm) {
            /** @var Form $f */
            $f = $this->container->get(FormFactory::class)->create();
            $f->setRenderer(new BootstrapRenderer);
            $f->addStarRating('score', 'Hodnocení')
               ->setDefaultValue(5);
            $f->addTextArea('review', 'Recenze')->setRequired();

            $this->container->get(Consents::class)
               ->addConsentCheckbox($f, Consent::TYPE_REVIEW,
                   Html::el()->setHtml('Přečetl/a jsem si <a href="' . Consent::DOCUMENT_URL . '" target="_blank">Zásady ochrany osobních údajů</a> a souhlasím se zveřejněním svého jména a příjmení u příspěvku.'),
                   'review_consent',
                   'Pro vložení recenze musíte souhlasit se zveřejněním svého jména a příjmení.',
                   'save'
               );

            $f->addSubmit('save', 'Odeslat recenzi');
            $f->onSuccess[] = function (Form $f) use ($reviews, $user) {
                if ($f->isSubmitted() === $f['save']) {
                    $data = $f->getValues(true);
                    if ($reviews->postReview($user, (int)$data['score'], (string)$data['review'])) {
                        $credits = Credit::AMOUNT_REVIEW_REWARD;

                        $this->flashMessage('Vaše recenze byla úspěšně vložená, děkujeme.' . ($credits > 0 ? ' Jako poděkování jsme vám připsali ' . $credits . ' Kč ve formě kreditu.' : ''), 'success');
                        if ($credits > 0) {
                            $this->container->get(Credits::class)->add($this->user->id, $credits, new \DateTimeImmutable(Credit::EXPIRATION_REVIEW_REWARD), sprintf('%s Kč za recenzi', $credits));
                        }
                        $this->container->get(\VCD\Notifications\Notifications::class)->add(
                           'Uživatel ' . $this->user->getIdentity()->getData()['name'] . ' vložil recenzi.',
                           $this->user->id
                       );
                    } else {
                        $this->flashMessage('Nepodařilo se vložit recenzi.', 'danger');
                    }
                    $this->redirect('this');
                }
            };
            $this->addComponent($f, 'form');
        }
    }
}
