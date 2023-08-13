<?php

namespace VCD2\Ebooks\Service;

use Hafo\DI\Container;
use Nette\Application\Responses\FileResponse;
use Nette\SmartObject;
use VCD2\Ebooks\Ebook;
use VCD2\Ebooks\EbookDownload;
use VCD2\Ebooks\EbookDownloadException;
use VCD2\Ebooks\EbookDownloadLink;
use VCD2\Orm;
use VCD2\Users\User;

/**
 * @method onCreateDownloadLink(EbookDownloadLink $link)
 * @method onDownload(EbookDownload $download)
 */
class Ebooks {

    use SmartObject;

    public $onCreateDownloadLink = [];

    public $onDownload = [];

    private $orm;

    private $www;

    function __construct(Orm $orm, Container $container) {
        $this->orm = $orm;
        $this->www = $container->get('www');
    }

    /**
     * @param Ebook $ebook
     * @param $email
     * @param User|NULL $user
     * @return EbookDownloadLink
     */
    function createDownloadLink(Ebook $ebook, $email, User $user = NULL) {
        $existing = $this->orm->ebookDownloadLinks->getBy([
            'ebook' => $ebook->id,
            'email' => $email,
            'user' => $user->id,
        ]);

        if($existing !== NULL) {
            $existing->createdAt = new \DateTimeImmutable;
            $this->orm->persistAndFlush($existing);
            return $existing;
        }

        $link = new EbookDownloadLink($ebook, $user, $email);
        $this->orm->persistAndFlush($link);

        $this->onCreateDownloadLink($link);

        return $link;
    }

    /**
     * @param $hash
     * @return FileResponse
     * @throws EbookDownloadException
     */
    function downloadEbook($hash) {
        $links = $this->orm->ebookDownloadLinks->findBy(['hash' => $hash]);
        foreach($links as $link) {
            if($link->expiresAt > new \DateTimeImmutable) {
                $download = $link->createEbookDownload();
                $download->ebook->countDownloads++;

                $this->orm->persist($download->ebook);
                $this->orm->persist($download);
                $this->orm->remove($link);
                $this->orm->flush();

                $this->onDownload($download);

                return $this->createDownloadResponse($download->ebook);
            }
        }

        throw new EbookDownloadException;
    }

    private function createDownloadResponse(Ebook $ebook) {
        $path = $this->www . '/' . $ebook->ebook;
        return new FileResponse($path, NULL, 'application/pdf', TRUE);
    }

}
