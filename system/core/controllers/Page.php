<?php
namespace core\controllers;

use core\Controller;
use core\models\Page as P;

class Page extends Controller
{
    protected $page;

    public function __construct(P $page)
    {
        parent::__construct();

        $this->page  = $page;
    }

    public function page($page_id)
    {
        $page = $this->page->getCached($page_id);

        $langcode = $this->data['lang'];

        if (isset($page['description'][$langcode])) {
            $page['title'] = $page['description'][$langcode]['title'];
            $page['description'] = $page['description'][$langcode]['description'];

            $this->setMetaTitle($page['title']);
            $this->setMetaDescription($page['title']);
        }

        $this->data['page'] = $page;

        $this->hook->fire('page.view', $page);
    }
}
