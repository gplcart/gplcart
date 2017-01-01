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
        $page = $this->getPage($page_id);

        $this->setHtmlFilter($page);
        $this->setDataImagesPage($page);
        $this->setData('page', $page);
        $this->setRegionContentPage();

        $this->setTitlePage($page);
        $this->setMetaEntity($page);
        $this->setBreadcrumbPage($page);
        $this->outputPage();
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
    protected function setDataImagesPage(array $page)
    {
        $imagestyle = $this->setting('image_style_page', 5);
        $this->setItemThumb($page, array('imagestyle' => $imagestyle));

        $html = $this->render('page/blocks/images', array('page' => $page));
        $this->setData('images', $html);
    }

}
