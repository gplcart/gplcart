<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator\components;

use Exception;
use gplcart\core\handlers\validator\Component;
use gplcart\core\models\Collection as CollectionModel;
use gplcart\core\models\CollectionItem as CollectionItemModel;
use gplcart\core\models\Page as PageModel;
use gplcart\core\models\Product as ProductModel;

/**
 * Provides methods to validate collection item data
 */
class CollectionItem extends Component
{

    /**
     * Page model instance
     * @var \gplcart\core\models\Page $page
     */
    protected $page;

    /**
     * Product model instance
     * @var \gplcart\core\models\Product $product
     */
    protected $product;

    /**
     * Collection model instance
     * @var \gplcart\core\models\Collection $collection
     */
    protected $collection;

    /**
     * Collection item model instance
     * @var \gplcart\core\models\CollectionItem $collection_item
     */
    protected $collection_item;

    /**
     * @param PageModel $page
     * @param ProductModel $product
     * @param CollectionModel $collection
     * @param CollectionItemModel $collection_item
     */
    public function __construct(PageModel $page, ProductModel $product, CollectionModel $collection,
                                CollectionItemModel $collection_item)
    {
        parent::__construct();

        $this->page = $page;
        $this->product = $product;
        $this->collection = $collection;
        $this->collection_item = $collection_item;
    }

    /**
     * Performs full collection item entity validation
     * @param array $submitted
     * @param array $options
     * @return boolean|array
     */
    public function collectionItem(array &$submitted, array $options = array())
    {
        $this->options = $options;
        $this->submitted = &$submitted;

        $this->validateCollectionItem();
        $this->validateBool('status');
        $this->validateWeight();
        $this->validateUrlCollectionItem();
        $this->validateCollectionCollectionItem();
        $this->validateEntityIdCollectionItem();
        $this->validateEntityCollectionItem();
        $this->validateData();

        $this->unsetSubmitted('update');
        $this->unsetSubmitted('collection');

        return $this->getResult();
    }

    /**
     * Validates a collection item to be updated
     * @return boolean|null
     */
    protected function validateCollectionItem()
    {
        $id = $this->getUpdatingId();

        if ($id === false) {
            return null;
        }

        $data = $this->collection_item->get($id);

        if (empty($data)) {
            $this->setErrorUnavailable('update', $this->translation->text('Collection item'));
            return false;
        }

        $this->setSubmitted('update', $data);
        return true;
    }

    /**
     * Validates collection item URL
     * @return boolean
     */
    protected function validateUrlCollectionItem()
    {
        $url = $this->getSubmitted('data.url');

        if (isset($url) && mb_strlen($url) > 255) {
            $this->setErrorLengthRange('data.url', $this->translation->text('URL'), 0, 255);
            return false;
        }

        return true;
    }

    /**
     * Validates the collection data
     * @return boolean
     */
    protected function validateCollectionCollectionItem()
    {
        $field = 'collection_id';

        if ($this->isExcluded($field)) {
            return null;
        }

        $collection_id = $this->getSubmitted($field);

        if ($this->isUpdating() && !isset($collection_id)) {
            return null;
        }

        $label = $this->translation->text('Collection');

        if (empty($collection_id)) {
            $this->setErrorRequired($field, $label);
            return false;
        }

        if (!is_numeric($collection_id)) {
            $this->setErrorNumeric($field, $label);
            return false;
        }

        $collection = $this->collection->get($collection_id);

        if (empty($collection)) {
            $this->setErrorUnavailable($field, $label);
            return false;
        }

        $this->setSubmitted('collection', $collection);
        return true;
    }

    /**
     * Validates submitted collection item entity ID
     * @return boolean|null
     */
    protected function validateEntityIdCollectionItem()
    {
        $field = 'entity_id';

        if ($this->isExcluded($field)) {
            return null;
        }

        $value = $this->getSubmitted($field);

        if ($this->isUpdating() && !isset($value)) {
            return null;
        }

        $title = $this->getSubmitted('title');

        if (is_numeric($title)) {
            $value = $title;
        }

        $label = $this->translation->text('Entity');

        if (empty($value) || !is_numeric($value)) {
            $this->setErrorInvalid($field, $label);
            return false;
        }

        $update = $this->getUpdating();

        if (isset($update['collection_item_id']) && $update[$field] == $value) {
            return null;
        }

        $conditions = array(
            'type' => $this->getSubmitted('collection.type'),
            'collection_id' => $this->getSubmitted('collection.collection_id')
        );

        $existing = $this->collection_item->getItems($conditions);

        if (isset($existing[$value])) {
            $this->setErrorExists($field, $label);
            return false;
        }

        $this->setSubmitted($field, $value);
        return true;
    }

    /**
     * Validates collection item entities
     * @return boolean|null
     */
    protected function validateEntityCollectionItem()
    {
        if ($this->isError()) {
            return null;
        }

        $collection = $this->getSubmitted('collection');
        $collection_id = $this->getSubmitted('collection_id');

        if (empty($collection)) {

            if (!isset($collection_id)) {
                $updating = $this->getUpdating();
                if (isset($updating['collection_id'])) {
                    $collection_id = $updating['collection_id'];
                }
            }

            $collection = $this->collection->get($collection_id);
        }

        if (empty($collection['type'])) {
            $this->setErrorUnavailable('collection_id', $this->translation->text('Collection'));
            return false;
        }

        try {
            $entity_id = $this->getSubmitted('entity_id');
            $handlers = $this->collection->getHandlers();
            $result = static::call($handlers, $collection['type'], 'validate', array($entity_id));
        } catch (Exception $ex) {
            $result = $ex->getMessage();
        }

        if ($result === true) {
            return true;
        }

        $this->setError('entity_id', implode('<br>', (array) $result));
        return false;
    }

    /**
     * Validates page collection item. Hook callback
     * @param integer $page_id
     * @return boolean|string
     */
    public function validatePageCollectionItem($page_id)
    {
        $page = $this->page->get($page_id);

        if (empty($page['status'])) {
            return $this->translation->text('@name is unavailable', array('@name' => $this->translation->text('Page')));
        }

        return true;
    }

    /**
     * Validates product collection item. Hook callback
     * @param integer $product_id
     * @return boolean|string
     */
    public function validateProductCollectionItem($product_id)
    {
        $product = $this->product->get($product_id);

        if (empty($product['status'])) {
            return $this->translation->text('@name is unavailable', array(
                '@name' => $this->translation->text('Product')));
        }

        return true;
    }

    /**
     * Validates file collection item. Hook callback
     * @param integer $file_id
     * @return boolean|string
     */
    public function validateFileCollectionItem($file_id)
    {
        $file = $this->file->get($file_id);

        if (empty($file)) {
            return $this->translation->text('@name is unavailable', array(
                '@name' => $this->translation->text('File')));
        }

        return true;
    }

}
