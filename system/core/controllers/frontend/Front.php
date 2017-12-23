<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\frontend;

use gplcart\core\traits\Collection as CollectionTrait;
use gplcart\core\models\CollectionItem as CollectionItemModel;
use gplcart\core\controllers\frontend\Controller as FrontendController;

/**
 * Handles incoming requests and outputs data related to front page
 */
class Front extends FrontendController
{

    use CollectionTrait;

    /**
     * Collection item model instance
     * @var \gplcart\core\models\CollectionItem $collection_item
     */
    protected $collection_item;

    /**
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

        $this->setDataCollectionFront('page');
        $this->setDataCollectionFront('file');
        $this->setDataCollectionFront('product');

        $this->outputIndexFront();
    }

    /**
     * Adds a collection block
     * @param string $type
     */
    protected function setDataCollectionFront($type)
    {
        $collection_id = $this->getStore("data.collection_$type");

        if (!empty($collection_id)) {
            $conditions = array('collection_id' => $collection_id);
            $options = array('imagestyle' => $this->configTheme("image_style_collection_$type"));
            $items = $this->getCollectionItems($conditions, array_filter($options), $this->collection_item);
            $this->setData("collection_$type", $this->getWidgetCollection($items));
        }
    }

    /**
     * Sets titles on the front page
     */
    protected function setTitleIndexFront()
    {
        $this->setTitle($this->getStore('data.title'), false);
    }

    /**
     * Renders the front page templates
     */
    protected function outputIndexFront()
    {
        $this->output('front/content');
    }

}
