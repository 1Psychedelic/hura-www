<?php

namespace VCD\UI\FrontModule\BlogModule;

use Nette\Database\Context;
use Nette\Utils\Paginator;

class BlogPresenter extends BasePresenter {

    const LINK_DEFAULT = ':Front:Blog:Blog:default';

    function actionDefault($id = NULL, $page = 1) {
        $db = $this->container->get(Context::class);
        $category = $id === NULL ? NULL : $db->table('vcd_blog_category')->where('slug = ? AND visible = 1', $id)->fetch();
        $posts = $db->table('vcd_blog_article')->where('published_at IS NOT NULL AND published_at < NOW()');
        if($category) {
            $posts->where('category', $category['id']);
        }
        $posts->order('published_at DESC, id DESC');
        $this->template->currentCategory = $category;
        $this->template->categories = $db->table('vcd_blog_category')->where('visible = 1')->order('position ASC')->fetchAll();
        $this->template->countPosts = count($db->table('vcd_blog_article')->where('published_at IS NOT NULL AND published_at < NOW()'));
        $paginator = (new Paginator)
            ->setItemCount(count($posts))
            ->setItemsPerPage(10)
            ->setPage($page);
        $this->template->isFirst = $paginator->isFirst();
        $this->template->isLast = $paginator->isLast();
        $this->template->page = $page;
        $this->template->posts = $posts->limit($paginator->getLength(), $paginator->getOffset());
    }

}
