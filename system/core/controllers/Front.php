<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers;

use core\models\CollectionItem as ModelsCollectionItem;
use core\controllers\Controller as FrontendController;

/**
 * Handles incoming requests and outputs data related to front page
 */
class Front extends FrontendController
{
    
    /**
     * Collection item model instance
     * @var \core\models\CollectionItem $collection_item
     */
    protected $collection_item;
    
    /**
     * Constructor
     * @param ModelsCollectionItem $collection_item
     */
    public function __construct(ModelsCollectionItem $collection_item)
    {
        parent::__construct();

        $this->collection_item = $collection_item;
    }

    /**
     * Displays the store front page
     */
    public function indexFront()
    {
        $this->setRegionFeaturedFront();
        
        $this->setTitleIndexFront();
        $this->outputIndexFront();
    }
    
    /**
     * Adds a block with featured products on the front page
     */
    protected function setRegionFeaturedFront()
    {
        $collection_id = $this->store->config('collection_featured');

        if (!empty($collection_id)) {
            $options = array('collection_id' => $collection_id);
            $html = $this->renderCollectionProduct($options);
            $this->setRegion('region_content', $html);
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
        $this->output('front/front');
    }

}
