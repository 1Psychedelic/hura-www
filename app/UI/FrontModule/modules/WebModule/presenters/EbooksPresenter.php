<?php

namespace VCD\UI\FrontModule\WebModule;

use Hafo\Google\ConversionTracking\Tracker;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Application\UI\Multiplier;
use Nette\Database\Context;
use Nette\Database\SqlLiteral;
use Nextras\Orm\Collection\ICollection;
use VCD\Users\Newsletter;
use VCD2\Ebooks\Ebook;
use VCD2\Ebooks\EbookDownload;
use VCD2\Ebooks\EbookDownloadException;
use VCD2\Ebooks\EbookDownloadLink;
use VCD2\Ebooks\Service\Ebooks;

class EbooksPresenter extends BasePresenter {

    const LINK_DEFAULT = ':Front:Web:Ebooks:default';
    const LINK_DOWNLOAD = ':Front:Web:Ebooks:download';

    function actionDefault($ref = NULL) {
        $cond = [];
        if(!$this->user->isInRole('admin')) {
            $cond['visible'] = TRUE;
        }

        $newsletter = $this->container->get(Newsletter::class);

        /** @var Ebook[] $ebooks */
        $ebooks = $this->orm->ebooks->findBy($cond)->orderBy('position', ICollection::ASC);
        foreach($ebooks as $ebook) {
            $c = new EbookControl($this->container, $ebook);
            $c->onDownload[] = function(EbookDownloadLink $download) use ($newsletter) {
                $newsletter->add($download->email);
                $this->container->get(Tracker::class)->addConversion('Stažení ebooku', '4qVJCJ-N1IMBEMiF9YkD');
            };
            $this->addComponent($c, $ebook->id);
        }
        $this->template->ebooks = $ebooks;

        if($ref !== NULL && $this->isAjax() && $this->getComponent($ref, FALSE) !== NULL) {
            $this[$ref]->redrawControl();
        }

        // todo: pages orm
        $db = $this->container->get(Context::class);
        $this->template->content = $db->table('vcd_page')->where('slug = ? AND special = 1', 'ebooks')->fetchField('content');
        return;


        $db = $this->container->get(Context::class);
        $selection = $db->table('vcd_ebook');
        if(!$this->user->isInRole('admin')) {
            $selection->where('visible', 1);
        }
        $this->template->ebooks = $selection->order('position ASC');
        $this->template->content = $db->table('vcd_page')->where('slug = ? AND special = 1', 'ebooks')->fetchField('content');

        $m = new Multiplier(function ($id) {
            $f = new Form();
            $f->addSubmit('download', 'Stáhnout');
            $f->addProtection();
            $f->onSuccess[] = function(Form $f) use ($id) {
                $db = $this->container->get(Context::class);
                $selection = $db->table('vcd_ebook')->wherePrimary($id);
                if(!$this->user->isInRole('admin')) {
                    $selection->where('visible', 1);
                }
                $row = $selection->fetch();
                if(!$row) {
                    throw new BadRequestException;
                }
                $file = $this->container->get('ebooks') . '/' . $id . '/' . pathinfo($row['ebook'], PATHINFO_BASENAME);
                if(!$this->isBot() && file_exists($file)) {
                    $db->table('vcd_ebook')->wherePrimary($id)->update(['downloaded' => new SqlLiteral('downloaded + 1')]);
                }
                //$this->sendResponse(new FileResponse($file));
                $this->redirectUrl($this->template->baseUri . '/www/' . $row['ebook']);
            };
            return $f;
        });
        $this->addComponent($m, 'download');
    }

    function actionDownload($hash) {
        try {
            $response = $this->container->get(Ebooks::class)->downloadEbook($hash);
            $this->sendResponse($response);
        } catch (EbookDownloadException $e) {
            $this->presenter->flashMessage('Odkaz pro stažení e-booku je neplatný nebo vypršel.', 'danger');
            $this->presenter->redirect('default');
        }
    }

}
