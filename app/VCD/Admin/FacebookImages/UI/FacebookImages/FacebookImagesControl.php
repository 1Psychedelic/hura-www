<?php
declare(strict_types=1);

namespace VCD\Admin\FacebookImages\UI;

use Hafo\DI\Container;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use Nette\Http\FileUpload;
use Nette\Http\Url;
use Nette\Utils\FileSystem;
use Nextras\Orm\Collection\ICollection;
use VCD2\FacebookImages\FacebookImage;
use VCD2\FacebookImages\Service\FacebookImages;
use VCD2\Orm;
use VCD2\UI\Admin\Forms\AdminFormRenderer;

class FacebookImagesControl extends Control
{
    /** @var Orm */
    private $orm;

    /** @var Cache */
    private $cache;

    public function __construct(Container $container, string $url = '/')
    {
        $this->orm = $container->get(Orm::class);
        $this->cache = new Cache($container->get(IStorage::class), 'fb.images');

        $uploadDir = $container->get('facebook-images');

        $this->onAnchor[] = function () use ($url, $uploadDir, $container) {
            $this->template->list = $this->orm->facebookImages->findBy(['url' => $url])->orderBy('position');

            $f = new Form();
            $f->setRenderer(new AdminFormRenderer());
            $f->addText('url', 'URL');
            $f->addSubmit('submit', 'Vybrat URL');
            $f->onSuccess[] = function (Form $f) {
                $data = $f->getValues(true);
                if (empty($data['url'])) {
                    $this->presenter->redirect('this', ['url' => '/']);
                }

                $url = new Url($data['url']);
                $this->presenter->redirect('this', ['url' => $url->getPath()]);
            };
            $f->setValues(['url' => $url]);
            $this->addComponent($f, 'urlForm');

            $uf = new Form();
            $uf->setRenderer(new AdminFormRenderer());
            $uf->addUpload('image', 'Obrázek')
                ->setRequired();
                //->addRule(Form::IMAGE);
            $uf->addSubmit('upload', 'Nahrát obrázek');
            $uf->onSuccess[] = function (Form $f) use ($uploadDir, $url, $container) {
                if ($f->isSubmitted() === $f['upload']) {
                    $data = $f->getValues(true);
                    $file = $data['image'];
                    if ($file instanceof FileUpload) {
                        $image = $file->toImage();
                        $width = $image->getWidth();
                        $height = $image->getHeight();
                        $destination = $uploadDir . '/' . md5($url) . '/' . $file->getSanitizedName();
                        $file->move($destination);

                        $existing = $this->orm->facebookImages->findBy(['url' => $url])->orderBy('position', ICollection::DESC)->fetch();
                        $position = $existing instanceof FacebookImage ? $existing->position + 1 : 0;

                        $fbImage = new FacebookImage();
                        $fbImage->url = $url;
                        $fbImage->image = str_replace($container->get('www'), '', $destination);
                        $fbImage->position = $position;
                        $fbImage->width = $width;
                        $fbImage->height = $height;
                        $this->orm->persistAndFlush($fbImage);

                        $this->clearCache();

                        $this->presenter->redirect('this');
                    }
                }
            };
            $this->addComponent($uf, 'uploadForm');

            $this->template->url = $url;
        };
    }

    public function render() {
        $this->template->setFile(__DIR__ . '/default.latte');
        $this->template->render();
    }

    public function handleMoveUp($id) {
        $current = $this->orm->facebookImages->get($id);
        $previous = $this->orm->facebookImages->findBy(['position<' => $current->position])->orderBy('position', ICollection::DESC)->fetch();
        if ($previous instanceof FacebookImage) {
            $currentPosition = $current->position;
            $current->position = $previous->position;
            $previous->position = $currentPosition;
            $this->orm->persist($previous);
            $this->orm->persist($current);
            $this->orm->flush();
            $this->clearCache();
            $this->presenter->flashMessage('Pozice změněna.', 'success');
        }
        $this->presenter->redirect('this');
    }

    public function handleMoveDown($id) {
        $current = $this->orm->facebookImages->get($id);
        $next = $this->orm->facebookImages->findBy(['position>' => $current->position])->orderBy('position')->fetch();
        if ($next instanceof FacebookImage) {
            $currentPosition = $current->position;
            $current->position = $next->position;
            $next->position = $currentPosition;
            $this->orm->persist($next);
            $this->orm->persist($current);
            $this->orm->flush();
            $this->clearCache();
            $this->presenter->flashMessage('Pozice změněna.', 'success');
        }
        $this->presenter->redirect('this');
    }

    public function handleDelete($id) {
        $image = $this->orm->facebookImages->get($id);
        if ($image instanceof FacebookImage) {
            $this->orm->remove($image);
            $this->orm->flush();
            $this->clearCache();
            $this->presenter->flashMessage('Obrázek odstraněn.', 'success');
        }

        $this->presenter->redirect('this');
    }

    private function clearCache() {
        $this->cache->clean([Cache::TAGS => [FacebookImages::CACHE_TAG]]);
    }
}
