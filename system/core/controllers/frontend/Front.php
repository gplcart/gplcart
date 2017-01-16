<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\frontend;

use gplcart\core\models\CollectionItem as CollectionItemModel;
use gplcart\core\controllers\frontend\Controller as FrontendController;

/**
 * Handles incoming requests and outputs data related to front page
 */
class Front extends FrontendController
{

    /**
     * Collection item model instance
     * @var \gplcart\core\models\CollectionItem $collection_item
     */
    protected $collection_item;

    /**
     * Constructor
     * @param CollectionItemModel $collection_item
     */
    public function __construct(CollectionItemModel $collection_item)
    {
        parent::__construct();

        $this->collection_item = $collection_item;
    }

    /**
     * Displays the store front page
     */
    public function indexFront()
    {
        $this->setTitleIndexFront();

        $this->setDataCollectionBannersFront();
        $this->setDataCollectionProductsFront();
        $this->setDataCollectionPagesFront();
        $this->setRegionContentFront();

        $this->outputIndexFront();
    }

    /**
     * Adds main content to content region
     */
    protected function setRegionContentFront()
    {
        $html = $this->render('front/content', $this->data);
        $this->setRegion('region_content', $html);
    }

    /**
     * Adds a block with featured products on the front page
     */
    protected function setDataCollectionProductsFront()
    {
        $collection_id = $this->store->config('collection_featured');

        if (!empty($collection_id)) {
            $options = array('collection_id' => $collection_id);
            $html = $this->renderCollectionProduct($options);
            $this->setData('collection_products', $html);
        }
    }

    /**
     * Adds a block with banners on the front page
     */
    protected function setDataCollectionBannersFront()
    {
        $collection_id = $this->store->config('collection_banner');

        if (!empty($collection_id)) {
            $options = array('collection_id' => $collection_id);
            $html = $this->renderCollectionFile($options);
            $this->setData('collection_banners', $html);
        }
    }

    /**
     * Adds a block with pages on the front page
     */
    protected function setDataCollectionPagesFront()
    {
        $collection_id = $this->store->config('collection_page');

        if (!empty($collection_id)) {
            $options = array('collection_id' => $collection_id);
            $html = $this->renderCollectionPage($options);
            $this->setData('collection_pages', $html);
        }
    }

    /**
     * Sets titles on the front page
     */
    protected function setTitleIndexFront()
    {
        $title = $this->store->config('title');
        $this->setTitle($title, false);
    }

    /**
     * Renders the fron page templates
     */
    protected function outputIndexFront()
    {
        $this->output();
    }

}
