<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\handlers\validator;

use core\Handler;
use core\models\File as FileModel;
use core\models\Page as PageModel;
use core\models\Product as ProductModel;
use core\models\Collection as CollectionModel;
use core\models\CollectionItem as CollectionItemModel;
use core\handlers\validator\Base as BaseValidator;

/**
 * Provides methods to validate collection item data
 */
class CollectionItem extends BaseValidator
{

    /**
     * Product model instance
     * @var \core\models\Product $product
     */
    protected $product;

    /**
     * Page model instance
     * @var \core\models\Page $page
     */
    protected $page;

    /**
     * File model instance
     * @var \core\models\File $file
     */
    protected $file;

    /**
     * Collection model instance
     * @var \core\models\Collection $collection
     */
    protected $collection;

    /**
     * Collection item model instance
     * @var \core\models\CollectionItem $collection_item
     */
    protected $collection_item;

    /**
     * Constructor
     * @param FileModel $file
     * @param PageModel $page
     * @param ProductModel $product
     * @param CollectionModel $collection
     * @param CollectionItemModel $collection_item
     */
    public function __construct(FileModel $file, PageModel $page,
            ProductModel $product, CollectionModel $collection,
            CollectionItemModel $collection_item)
    {

        parent::__construct();

        $this->file = $file;
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
        $this->submitted = &$submitted;
        
        $this->validateStatus($options);
        $this->validateWeight($options);
        $this->validateUrlCollectionItem($options);
        $this->validateCollectionCollectionItem($options);
        $this->validateValueCollectionItem($options);
        $this->validateEntityCollectionItem($options);

        return $this->getResult();
    }

    /**
     * Validates collection item URL
     * @param array $options
     * @return boolean
     */
    protected function validateUrlCollectionItem(array $options)
    {
        $url = $this->getSubmitted('data.url', $options);

        if (isset($url) && mb_strlen($url) > 255) {
            $vars = array('@max' => 255, '@field' => $this->language->text('Url'));
            $error = $this->language->text('@field must not be longer than @max characters', $vars);
            $this->setError('data.url', $error, $options);
            return false;
        }

        return true;
    }

    /**
     * Validates that collection data is provided
     * @param array $options
     * @return boolean
     */
    protected function validateCollectionCollectionItem(array $options)
    {
        $collection_id = $this->getSubmitted('collection_id', $options);

        if (empty($collection_id)) {
            $vars = array('@field' => $this->language->text('Collection ID'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('collection_id', $error, $options);
            return false;
        }

        if (!is_numeric($collection_id)) {
            $vars = array('@field' => $this->language->text('Collection ID'));
            $error = $this->language->text('@field must be numeric', $vars);
            $this->setError('collection_id', $error, $options);
            return false;
        }

        $collection = $this->collection->get($collection_id);

        if (empty($collection['collection_id'])) {
            $vars = array('@name' => $this->language->text('Collection ID'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('collection_id', $error, $options);
            return false;
        }

        $this->setSubmitted('collection', $collection);
        return true;
    }

    /**
     * Validates submitted value
     * @param array $options
     * @return boolean|null
     */
    protected function validateValueCollectionItem(array $options)
    {
        $collection = $this->getSubmitted('collection');

        if (empty($collection)) {
            return null;
        }

        $input = $this->getSubmitted('input', $options);
        $value = $this->getSubmitted('value', $options);

        if (isset($input) && is_numeric($input)) {
            $value = $input;
        }

        if (empty($value)) {
            $vars = array('@field' => $this->language->text('Value'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('value', $error, $options);
            return false;
        }

        $conditions = array(
            'value' => $value,
            'collection_id' => $collection['collection_id']
        );

        $collection_item = $this->collection_item->getList($conditions);

        if (!empty($collection_item)) {
            $vars = array('@object' => $this->language->text('Value'));
            $error = $this->language->text('@object already exists', $vars);
            $this->setError('value', $error, $options);
            return false;
        }

        $this->setSubmitted('value', $value, $options);
        return true;
    }

    /**
     * Validates collection item entities
     * @param array $options
     * @return boolean|null
     */
    protected function validateEntityCollectionItem(array $options)
    {
        $collection = $this->getSubmitted('collection');

        if (empty($collection)) {
            return null;
        }

        $value = $this->getSubmitted('value', $options);
        $handlers = $this->collection->getHandlers();
        $result = Handler::call($handlers, $collection['type'], 'validate', array($value));

        if ($result === true) {
            return true;
        }

        foreach ((array) $result as $key => $error) {
            $this->setError($key, $error, $options);
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
