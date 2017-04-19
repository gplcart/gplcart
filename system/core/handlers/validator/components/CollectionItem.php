<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator\components;

use gplcart\core\Handler;
use gplcart\core\models\Page as PageModel,
    gplcart\core\models\Product as ProductModel,
    gplcart\core\models\Collection as CollectionModel,
    gplcart\core\models\CollectionItem as CollectionItemModel;
use gplcart\core\handlers\validator\Component as ComponentValidator;

/**
 * Provides methods to validate collection item data
 */
class CollectionItem extends ComponentValidator
{

    /**
     * Product model instance
     * @var \gplcart\core\models\Product $product
     */
    protected $product;

    /**
     * Page model instance
     * @var \gplcart\core\models\Page $page
     */
    protected $page;

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
    public function __construct(PageModel $page, ProductModel $product,
            CollectionModel $collection, CollectionItemModel $collection_item)
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

        $this->validateStatusComponent();
        $this->validateWeightComponent();
        $this->validateUrlCollectionItem();
        $this->validateCollectionCollectionItem();
        $this->validateValueCollectionItem();
        $this->validateEntityCollectionItem();

        return $this->getResult();
    }

    /**
     * Validates collection item URL
     * @return boolean
     */
    protected function validateUrlCollectionItem()
    {
        $url = $this->getSubmitted('data.url');

        if (isset($url) && mb_strlen($url) > 255) {
            $this->setErrorLengthRange('data.url', $this->language->text('URL'), 0, 255);
            return false;
        }
        return true;
    }

    /**
     * Validates that collection data is provided
     * @return boolean
     */
    protected function validateCollectionCollectionItem()
    {
        $field = 'collection_id';
        $label = $this->language->text('Collection ID');
        $collection_id = $this->getSubmitted($field);

        if (empty($collection_id)) {
            $this->setErrorRequired($field, $label);
            return false;
        }

        if (!is_numeric($collection_id)) {
            $this->setErrorNumeric($field, $label);
            return false;
        }

        $collection = $this->collection->get($collection_id);

        if (empty($collection['collection_id'])) {
            $this->setErrorUnavailable($field, $label);
            return false;
        }

        $this->setSubmitted('collection', $collection);
        return true;
    }

    /**
     * Validates submitted value
     * @return boolean|null
     */
    protected function validateValueCollectionItem()
    {
        $field = 'value';
        $label = $this->language->text('Value');

        $input = $this->getSubmitted('input');
        $value = $this->getSubmitted($field);
        $collection = $this->getSubmitted('collection');

        if (empty($collection)) {
            return null;
        }

        if (isset($input) && is_numeric($input)) {
            $value = $input;
        }

        if (empty($value)) {
            $this->setErrorRequired($field, $label);
            return false;
        }

        $conditions = array(
            'value' => $value,
            'collection_id' => $collection['collection_id']
        );

        $collection_item = $this->collection_item->getList($conditions);

        if (!empty($collection_item)) {
            $this->setErrorExists($field, $label);
            return false;
        }

        $this->setSubmitted('value', $value);
        return true;
    }

    /**
     * Validates collection item entities
     * @return boolean|null
     */
    protected function validateEntityCollectionItem()
    {
        $collection = $this->getSubmitted('collection');

        if (empty($collection)) {
            return null;
        }

        $value = $this->getSubmitted('value');
        $handlers = $this->collection->getHandlers();
        $result = Handler::call($handlers, $collection['type'], 'validate', array($value));

        if ($result === true) {
            return true;
        }

        foreach ((array) $result as $key => $error) {
            $this->setError($key, $error);
        }

        return false;
    }

    /**
     * Validates page collection item
     * @param integer $page_id
     * @return boolean|string
     */
    public function page($page_id)
    {
        $page = $this->page->get($page_id);

        if (empty($page['status'])) {
            $vars = array('@name' => $this->language->text('Page'));
            return $this->language->text('@name is unavailable', $vars);
        }

        return true;
    }

    /**
     * Validates product collection item
     * @param integer $product_id
     * @return boolean|string
     */
    public function product($product_id)
    {
        $product = $this->product->get($product_id);

        if (empty($product['status'])) {
            $vars = array('@name' => $this->language->text('Product'));
            return $this->language->text('@name is unavailable', $vars);
        }

        return true;
    }

    /**
     * Validates file collection item
     * @param integer $file_id
     * @return boolean|string
     */
    public function file($file_id)
    {
        $file = $this->file->get($file_id);

        if (empty($file)) {
            $vars = array('@name' => $this->language->text('File'));
            return $this->language->text('@name is unavailable', $vars);
        }

        return true;
    }

}
