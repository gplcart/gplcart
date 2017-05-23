<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\frontend;

use gplcart\core\controllers\frontend\Controller as FrontendController;

/**
 * Handles incoming requests and outputs data related to front page
 */
class Front extends FrontendController
{

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
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
     */
    protected function setDataCollectionFront($type)
    {
        $collection_id = $this->store("data.collection_$type");

        if (!empty($collection_id)) {
            $options = array('imagestyle' => $this->settings("image_style_collection_$type"));
            $conditions = array('collection_id' => $collection_id);
            $html = $this->renderCollection($conditions, array_filter($options));
            $this->setData("collection_$type", $html);
        }
    }

    /**
     * Sets titles on the front page
     */
    protected function setTitleIndexFront()
    {
        $title = $this->store('data.title');
        $this->setTitle($title, false);
    }

    /**
     * Renders the fron page templates
     */
    protected function outputIndexFront()
    {
        $this->output('front/content');
    }

}
