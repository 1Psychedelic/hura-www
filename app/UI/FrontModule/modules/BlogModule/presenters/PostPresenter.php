<?php

namespace VCD\UI\FrontModule\BlogModule;

use Nette\Application\BadRequestException;
use Nette\Database\Context;

class PostPresenter extends BasePresenter {

    const LINK_DEFAULT = ':Front:Blog:Post:default';

    function actionDefault($id, $browse = NULL, $page = NULL) {
        if($id === 'roztomili-tucnacek-z-papiru') {
            $this->redirect('this', ['id' => 'roztomily-tucnacek-z-papiru']);
        }
        
        if(strlen($id) === 0) {
            throw new BadRequestException;
        }

        $db = $this->container->get(Context::class);
        if($this->user->isInRole('admin')) {
            $post = $db->table('vcd_blog_article')->where('slug', $id)->fetch();
        } else {
            $post = $db->table('vcd_blog_article')->where('slug = ? AND published_at IS NOT NULL AND published_at < NOW()', $id)->fetch();
        }
        if(!$post) {
            throw new BadRequestException;
        }
        $category = $browse === NULL ? NULL : $db->table('vcd_blog_category')->where('visible = 1')->where('slug', $browse)->fetch();
        if(($category && $category['id'] !== $post['category']) || $category === FALSE) {
            $this->redirect('this', ['id' => $id, 'browse' => $post['category'] === NULL ? NULL : $post->ref('vcd_blog_category', 'category')['slug']]);
        }
        $content = $post['content'];
        if($page !== NULL) {
            $pageRow = $db->table('vcd_blog_article_page')->where('article = ? AND position = ?', [$post['id'], $page])->fetch();
            if($pageRow) {
                $content = $pageRow['content'];
            }
        }
        $this->template->content = $content;
        $this->template->page = $page;
        $this->template->pages = $db->table('vcd_blog_article_page')->select('position')->where('article', $post['id'])->fetchAll();
        $this->template->currentCategory = $category;
        $this->template->categories = $db->table('vcd_blog_category')->where('visible = 1')->order('position ASC')->fetchAll();
        $this->template->post = $post;
        $this->template->countPosts = count($db->table('vcd_blog_article')->where('published_at IS NOT NULL AND published_at < NOW()'));

        $this->template->linkCanonical = '//' . self::LINK_DEFAULT;

        /**
         * @return \Nette\Database\Table\Selection
         */
        $selectArticles = function() use ($db, $category) {
            $table = $db->table('vcd_blog_article')->where('published_at IS NOT NULL AND published_at < NOW()');
            if($category) {
                $table->where('category', $category['id']);
            }
            return $table;
        };

        $this->template->previous = $post['published_at'] === NULL ? NULL : $selectArticles()->where('published_at < ? OR (published_at = ? AND id < ?)', [$post['published_at'], $post['published_at'], $post['id']])->order('published_at DESC, id DESC')->limit(1)->fetch();
        $this->template->next = $post['published_at'] === NULL ? NULL : $selectArticles()->where('published_at > ? OR (published_at = ? AND id > ?)', [$post['published_at'], $post['published_at'], $post['id']])->order('published_at ASC, id ASC')->limit(1)->fetch();

        $this->template->author = '';
        if ($post['author'] !== null) {
            $author = $this->orm->users->get($post['author']);
            if ($author !== null) {
                if (strpos($author->name, ' ') > 0) {
                    $this->template->author = explode(' ', $author->name)[0];
                } else {
                    $this->template->author = $author->name;
                }
            }
        }
    }

}
