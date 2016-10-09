<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers;

use core\models\Page as ModelsPage;
use core\controllers\Controller as FrontendController;

/**
 * Handles incoming requests and outputs data related to pages
 */
class Page extends FrontendController
{

    /**
     * Page model instance
     * @var \core\models\Page $page
     */
    protected $page;

    /**
     * Constructor
     * @param ModelsPage $page
     */
    public function __construct(ModelsPage $page)
    {
        parent::__construct();

        $this->page = $page;
    }

    /**
     * Displays a page
     * @param integer $page_id
     */
    public function indexPage($page_id)
    {
        $page = $this->getPage($page_id);

        $this->setImagesPage($page);
        $this->setData('page', $page);

        $this->setTitlePage($page);
        $this->setBreadcrumbPage($page);
        $this->setMetaPage($page);
        $this->outputPage();
    }

    /**
     * Renders the page
     */
    protected function outputPage()
    {
        $this->output('page/page');
    }

    /**
     * Sets breadcrumbs on the page
     * @param array $page
     */
    protected function setBreadcrumbPage(array $page)
    {
        $breadcrumb = array(
            'url' => $this->url('/'),
            'text' => $this->text('Home')
        );

        $this->setBreadcrumb($breadcrumb);
    }
    
    /**
     * Sets meta tags on the page
     * @param array $page
     */
    protected function setMetaPage(array $page)
    {
        if ($page['meta_title'] !== '') {
            $this->setTitle($page['meta_title'], false);
        }
        
        if ($page['meta_description'] !== '') {
            $this->setMeta(array('name' => 'description', 'content' => $page['meta_description']));
        }
    }

    /**
     * Sets titles on the page
     * @param array $page
     */
    protected function setTitlePage(array $page)
    {
        $this->setTitle($page['title']);
    }

    /**
     * Loads a page from the database
     * @param integer $page_id
     * @return array
     */
    protected function getPage($page_id)
    {
        $page = $this->page->get($page_id, $this->langcode);

        if (empty($page)) {
            $this->outputError(404);
        }

        if (empty($page['status']) && !$this->access('page')) {
            $this->outputError(403);
        }

        if ($page['store_id'] != $this->store_id) {
            $this->outputError(404);
        }

        return $page;
    }

    /**
     * Sets rendered page images
     * @param array $page
     */
    protected function setImagesPage(array $page)
    {
        $imagestyle = $this->setting('image_style_page', 5);
        $this->setItemThumb($page, array('imagestyle' => $imagestyle));

        $html = $this->render('page/images', array('page' => $page));
        $this->setData('images', $html);
    }

}
