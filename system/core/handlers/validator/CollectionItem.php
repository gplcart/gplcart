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
use core\models\Language as ModelsLanguage;
use core\models\Collection as ModelsCollection;

/**
 * Provides methods to validate collection item data
 */
class CollectionItem
{

    /**
     * Language model instance
     * @var \core\models\Language $language
     */
    protected $language;

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
     * Constructor
     * @param ModelsFile $file
     * @param ModelsPage $page
     * @param ModelsProduct $product
     * @param ModelsCollection $collection
     * @param ModelsLanguage $language
     */
    public function __construct(ModelsFile $file, ModelsPage $page,
            ModelsProduct $product, ModelsCollection $collection,
            ModelsLanguage $language)
    {
        $this->file = $file;
        $this->page = $page;
        $this->product = $product;
        $this->language = $language;
        $this->collection = $collection;
    }

    /**
     * Validates collection item value
     * @param string $value
     * @param array $options
     * @return boolean|string
     */
    public function value($value, array $options = array())
    {
        if (empty($value) || empty($options['data']['type'])) {
            return false;
        }

        $handler_id = $options['data']['type'];
        $handlers = $this->collection->getHandlers();
        $result = Handler::call($handlers, $handler_id, 'validate', array($value, $options));

        if ($result === true) {
            return true;
        }

        return $result;
    }

    /**
     * Validates page collection item
     * @param string $page_id
     * @param array $options
     * @return boolean|string
     */
    public function page($page_id, array $options = array())
    {
        if (empty($page_id)) {
            return false;
        }

        $page = $this->page->get($page_id);

        if (empty($page)) {
            return $this->language->text('Page does not exist');
        }

        if (empty($page['status'])) {
            return $this->language->text('Page is disabled');
        }

        return true;
    }

    /**
     * Validates product collection item
     * @param string $product_id
     * @param array $options
     * @return boolean|string
     */
    public function product($product_id, array $options = array())
    {
        if (empty($product_id)) {
            return false;
        }

        $product = $this->product->get($product_id);

        if (empty($product)) {
            return $this->language->text('Product does not exist');
        }

        if (empty($product['status'])) {
            return $this->language->text('Product is disabled');
        }

        return true;
    }

    /**
     * Validates file collection item
     * @param string $file_id
     * @param array $options
     * @return boolean|string
     */
    public function file($file_id, array $options = array())
    {
        if (empty($file_id)) {
            return false;
        }

        $file = $this->file->get($file_id);

        if (empty($file)) {
            return $this->language->text('File does not exist');
        }

        return true;
    }

}
