<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\traits;

/**
 * Contains methods related to collections
 */
trait Collection
{

    /**
     * @see \gplcart\core\Controller::getStoreId()
     */
    abstract public function getStoreId();

    /**
     * @see \gplcart\core\controllers\frontend\Controller::prepareEntityItems()
     * @param $items
     * @param array $options
     * @return
     */
    abstract protected function prepareEntityItems($items, $options = array());

    /**
     * Returns an array of collection items
     * @param array $conditions
     * @param array $options
     * @param \gplcart\core\models\CollectionItem $model
     * @return array
     */
    public function getCollectionItems(array $conditions, array $options, $model)
    {
        $conditions += array(
            'status' => 1,
            'store_id' => $this->getStoreId()
        );

        $items = $model->getItems($conditions);

        if (empty($items)) {
            return array();
        }

        $item = reset($items);

        $options += array(
            'entity' => $item['collection_item']['type'],
            'template_item' => $item['collection_handler']['template']['item']
        );

        return $this->prepareEntityItems($items, $options);
    }

}
