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
     * The current page
     * @var array
     */
    protected $data_page = array();

    /**
     * Constructor
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

        $this->setTitlePage();
        $this->setBreadcrumbPage();
        $this->setMetaPage();

        $this->setHtmlFilter($this->data_page);
        $this->setData('page', $this->data_page);

        $this->setDataImagesPage();
        $this->setRegionContentPage();

        $this->outputPage();
    }

    /**
     * Set meta tags
     */
    protected function setMetaPage()
    {
        $this->setMetaEntity($this->data_page);
    }

    /**
     * Sets main content region
     */
    protected function setRegionContentPage()
    {
        $html = $this->render('page/content', $this->data);
        $this->setRegion('region_content', $html);
    }

    /**
     * Renders the page tempaltes
     */
    protected function outputPage()
    {
        $this->output();
    }

    /**
     * Sets breadcrumbs on the page
     */
    protected function setBreadcrumbPage()
    {
        $breadcrumb = array(
            'url' => $this->url('/'),
            'text' => $this->text('Home')
        );

        $this->setBreadcrumb($breadcrumb);
    }

    /**
     * Sets titles on the page
     */
    protected function setTitlePage()
    {
        $this->setTitle($this->data_page['title']);
    }

    /**
     * Loads a page from the database
     * @param integer $page_id
     * @return array
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

        return $this->data_page = $page;
    }

    /**
     * Sets rendered page images
     */
    protected function setDataImagesPage()
    {
        $imagestyle = $this->settings('image_style_page', 5);
        $this->setItemThumb($this->data_page, array('imagestyle' => $imagestyle));

        $html = $this->render('page/blocks/images', array('page' => $this->data_page));
        $this->setData('images', $html);
    }

}
