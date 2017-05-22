<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\frontend;

use gplcart\core\models\Page as PageModel;
use gplcart\core\controllers\frontend\Controller as FrontendController;

/**
 * Handles incoming requests and outputs data related to pages
 */
class Page extends FrontendController
{

    /**
     * Page model instance
     * @var \gplcart\core\models\Page $page
     */
    protected $page;

    /**
     * An array of page data
     * @var array
     */
    protected $data_page = array();

    /**
     * @param PageModel $page
     */
    public function __construct(PageModel $page)
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
        $this->setPage($page_id);

        $this->setTitleIndexPage();
        $this->setBreadcrumbIndexPage();

        $this->setHtmlFilterIndexPage();
        $this->setMetaIndexPage();

        $this->setRegionContentIndexPage();
        $this->outputIndexPage();
    }

    /**
     * Sets HTML filter on the page
     */
    protected function setHtmlFilterIndexPage()
    {
        $this->setHtmlFilter($this->data_page);
    }

    /**
     * Set meta tags on the page
     */
    protected function setMetaIndexPage()
    {
        $this->setMetaEntity($this->data_page);
    }

    /**
     * Sets the main content region
     */
    protected function setRegionContentIndexPage()
    {
        $data = array(
            'page' => $this->data_page,
            'images' => $this->renderImagesIndexPage()
        );

        $this->setRegion('content', $this->render('page/content', $data));
    }

    /**
     * Returns rendered page images
     */
    protected function renderImagesIndexPage()
    {
        $options = array('imagestyle' => $this->settings('image_style_page', 5));
        $this->attachItemThumb($this->data_page, $options);
        return $this->render('page/images', array('page' => $this->data_page));
    }

    /**
     * Render and output the page
     */
    protected function outputIndexPage()
    {
        $this->output();
    }

    /**
     * Sets breadcrumbs on the page
     */
    protected function setBreadcrumbIndexPage()
    {
        $breadcrumb = array('url' => $this->url('/'), 'text' => $this->text('Home'));
        $this->setBreadcrumb($breadcrumb);
    }

    /**
     * Sets titles on the page
     */
    protected function setTitleIndexPage()
    {
        $this->setTitle($this->data_page['title']);
    }

    /**
     * Sets a page data
     * @param integer $page_id
     */
    protected function setPage($page_id)
    {
        $page = $this->page->get($page_id, $this->langcode);

        if (empty($page)) {
            $this->outputHttpStatus(404);
        }

        if (empty($page['status']) && !$this->access('page')) {
            $this->outputHttpStatus(403);
        }

        if ($page['store_id'] != $this->store_id) {
            $this->outputHttpStatus(404);
        }

        $this->data_page = $page;
    }

}
