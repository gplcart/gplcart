<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\handlers\validator;

use core\Handler;
use core\models\File as ModelsFile;
use core\models\Page as ModelsPage;
use core\models\Product as ModelsProduct;
use core\models\Collection as ModelsCollection;
use core\models\CollectionItem as ModelsCollectionItem;
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
     * @param ModelsFile $file
     * @param ModelsPage $page
     * @param ModelsProduct $product
     * @param ModelsCollection $collection
     * @param ModelsCollectionItem $collection_item
     */
    public function __construct(ModelsFile $file, ModelsPage $page,
            ModelsProduct $product, ModelsCollection $collection,
            ModelsCollectionItem $collection_item)
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
     * @return null
     */
    public function collectionItem(array &$submitted, array $options = array())
    {
        $this->validateStatus($submitted);
        $this->validateWeight($submitted);
        $this->validateUrlCollectionItem($submitted);
        $this->validateCollectionCollectionItem($submitted);
        $this->validateValueCollectionItem($submitted);
        $this->validateEntityCollectionItem($submitted);

        return $this->getResult();
    }

    /**
     * Validates collection item URL
     * @param array $submitted
     */
    protected function validateUrlCollectionItem(array $submitted)
    {
        if (isset($submitted['data']['url']) && mb_strlen($submitted['data']['url']) > 255) {
            $options = array('@max' => 255, '@field' => $this->language->text('Url'));
            $this->errors['data']['url'] = $this->language->text('@field must not be longer than @max characters', $options);
        }
    }

    /**
     * Validates that collection data is provided
     * @param array $submitted
     */
    protected function validateCollectionCollectionItem(array &$submitted)
    {
        if (empty($submitted['collection_id'])) {
            $this->errors['collection_id'] = $this->language->text('@field is required', array(
                '@field' => $this->language->text('Collection ID')
            ));
            return false;
        }

        if (!is_numeric($submitted['collection_id'])) {
            $options = array('@field' => $this->language->text('Collection ID'));
            $this->errors['collection_id'] = $this->language->text('@field must be numeric', $options);
            return false;
        }

        $collection = $this->collection->get($submitted['collection_id']);

        if (empty($collection)) {
            $this->errors['collection_id'] = $this->language->text('Object @name does not exist', array(
                '@name' => $this->language->text('Collection ID')));
            return false;
        }

        $submitted['collection'] = $collection;
        return true;
    }

    /**
     * Validates submitted value
     * @param array $submitted
     * @return boolean
     */
    protected function validateValueCollectionItem(array &$submitted)
    {
        if (empty($submitted['collection'])) {
            return null;
        }

        if (isset($submitted['input']) && is_numeric($submitted['input'])) {
            $submitted['value'] = $submitted['input'];
        }

        if (empty($submitted['value'])) {
            $this->errors['value'] = $this->language->text('@field is required', array(
                '@field' => $this->language->text('Value')
            ));
            return false;
        }

        $conditions = array(
            'value' => $submitted['value'],
            'collection_id' => $submitted['collection']['collection_id']
        );

        $item = $this->collection_item->getList($conditions);

        if (!empty($item)) {
            $this->errors['value'] = $this->language->text('@object already exists', array(
                '@object' => $this->language->text('Value')));
            return false;
        }

        return true;
    }

    /**
     * Validates collection item entities
     * @param array $submitted
     * @return boolean
     */
    protected function validateEntityCollectionItem(array &$submitted)
    {
        if (empty($submitted['collection'])) {
            return null;
        }

        $handler_id = $submitted['collection']['type'];
        $handlers = $this->collection->getHandlers();
        $result = Handler::call($handlers, $handler_id, 'validate', array($submitted['value']));

        if ($result === true) {
            return true;
        }

        $errors = (array) $result;
        $this->errors = array_merge($this->errors, $errors);
        return false;
    }

    /**
     * Validates page collection item
     * @param string $page_id
     * @return boolean
     */
    public function page($page_id)
    {
        $page = $this->page->get($page_id);

        if (empty($page)) {
            return $this->language->text('Object @name does not exist', array(
                        '@name' => $this->language->text('Page')));
        }

        if (empty($page['status'])) {
            return $this->language->text('Object @name exists but disabled', array(
                        '@name' => $this->language->text('Page')));
        }

        return true;
    }

    /**
     * Validates product collection item
     * @param string $product_id
     * @return boolean|string
     */
    public function product($product_id)
    {
        $product = $this->product->get($product_id);

        if (empty($product)) {
            return $this->language->text('Object @name does not exist', array(
                        '@name' => $this->language->text('Product')));
        }

        if (empty($product['status'])) {
            return $this->language->text('Object @name exists but disabled', array(
                        '@name' => $this->language->text('Product')));
        }

        return true;
    }

    /**
     * Validates file collection item
     * @param string $file_id
     * @return boolean|string
     */
    public function file($file_id)
    {
        $file = $this->file->get($file_id);

        if (empty($file)) {
            return $this->language->text('Object @name does not exist', array(
                        '@name' => $this->language->text('File')));
        }

        return true;
    }

}
