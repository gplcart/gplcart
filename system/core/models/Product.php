<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\models;

use PDO;
use core\Hook;
use core\Config;
use core\classes\Tool;
use core\classes\Cache;
use core\classes\Request as ClassesRequest;
use core\models\Sku as ModelsSku;
use core\models\Price as ModelsPrice;
use core\models\Image as ModelsImage;
use core\models\Alias as ModelsAlias;
use core\models\Search as ModelsSearch;
use core\models\Language as ModelsLanguage;
use core\models\PriceRule as ModelsPriceRule;

/**
 * Manages basic behaviors and data related to products
 */
class Product
{

    /**
     * Image model instance
     * @var \core\models\Image $image
     */
    protected $image;

    /**
     * Url model instance
     * @var \core\models\Alias $alias
     */
    protected $alias;

    /**
     * Price model instance
     * @var \core\models\Price $price
     */
    protected $price;

    /**
     * Language model instance
     * @var \core\models\Language $language
     */
    protected $language;

    /**
     * Price rule model instance
     * @var \core\models\PriceRule $pricerule
     */
    protected $pricerule;

    /**
     * Sku model instance
     * @var \core\models\Sku $sku
     */
    protected $sku;

    /**
     * Search module instance
     * @var \core\models\Search $search
     */
    protected $search;

    /**
     * Hook class instance
     * @var \core\Hook $hook
     */
    protected $hook;

    /**
     * Request class instance
     * @var \core\classes\Request $request
     */
    protected $request;

    /**
     * Config class instance
     * @var \core\Config $config
     */
    protected $config;

    /**
     * PDO instance
     * @var \core\classes\Database $db
     */
    protected $db;

    /**
     * Constructor
     * @param ModelsPrice $price
     * @param ModelsPriceRule $pricerule
     * @param ModelsImage $image
     * @param ModelsAlias $alias
     * @param ModelsLanguage $language
     * @param ModelsSku $sku
     * @param ModelsSearch $search
     * @param ClassesRequest $request
     * @param Hook $hook
     * @param Config $config
     */
    public function __construct(ModelsPrice $price, ModelsPriceRule $pricerule,
            ModelsImage $image, ModelsAlias $alias, ModelsLanguage $language,
            ModelsSku $sku, ModelsSearch $search, ClassesRequest $request,
            Hook $hook, Config $config)
    {
        $this->sku = $sku;
        $this->hook = $hook;
        $this->price = $price;
        $this->image = $image;
        $this->alias = $alias;
        $this->search = $search;
        $this->config = $config;
        $this->request = $request;
        $this->language = $language;
        $this->pricerule = $pricerule;
        $this->db = $this->config->getDb();
    }

    /**
     * Adds a product to the database
     * @param array $data
     * @param array $options
     * @return boolean|integer
     */
    public function add(array $data, array $options = array())
    {
        $this->hook->fire('add.product.before', $data);

        if (empty($data)) {
            return false;
        }

        if (!isset($data['store_id'])) {
            $data['store_id'] = $this->config->get('store', 1);
        }

        $values = array(
            'modified' => 0,
            'title' => $data['title'],
            'user_id' => (int) $data['user_id'],
            'meta_title' => !empty($data['meta_title']) ? $data['meta_title'] : '',
            'description' => !empty($data['description']) ? $data['description'] : '',
            'meta_description' => !empty($data['meta_description']) ? $data['meta_description'] : '',
            'created' => !empty($data['created']) ? (int) $data['created'] : GC_TIME,
            'data' => !empty($data['data']) ? serialize((array) $data['data']) : serialize(array()),
            'currency' => !empty($data['currency']) ? $data['currency'] : $this->config->get('currency', 'USD'),
            'price' => !empty($data['price']) ? $this->price->amount($data['price'], $data['currency']) : 0,
            'status' => !empty($data['status']),
            'front' => !empty($data['front']),
            'product_class_id' => !empty($data['product_class_id']) ? (int) $data['product_class_id'] : 0,
            'subtract' => !empty($data['subtract']),
            'stock' => !empty($data['stock']) ? (int) $data['stock'] : 0,
            'brand_category_id' => !empty($data['brand_category_id']) ? (int) $data['brand_category_id'] : 0,
            'category_id' => !empty($data['category_id']) ? (int) $data['category_id'] : 0,
            'weight_unit' => !empty($data['weight_unit']) ? $data['weight_unit'] : 'g',
            'volume_unit' => !empty($data['volume_unit']) ? $data['volume_unit'] : 'mm',
            'store_id' => (int) $data['store_id'],
            'width' => isset($data['width']) ? (int) $data['width'] : 0,
            'height' => isset($data['height']) ? (int) $data['height'] : 0,
            'length' => isset($data['length']) ? (int) $data['length'] : 0,
            'weight' => isset($data['weight']) ? (int) $data['weight'] : 0
        );

        $data['product_id'] = $product_id = $this->db->insert('product', $values);

        if (!empty($data['translation'])) {
            $this->setTranslations($product_id, $data, false);
        }

        if (!empty($data['images'])) {
            $data['images'] = $this->setImages($product_id, $data);
        }

        if (empty($data['sku'])) {
            $data['sku'] = $this->createSku($data);
        }

        if (!empty($data['combination'])) {
            $this->setCombinations($product_id, $data, false);
        }

        if (!empty($data['field']['attribute'])) {
            $this->setAttributes($product_id, $data, false);
        }

        $this->setSku($product_id, $data, false);

        $translit_alias = $generate_alias = true;
        if (isset($options['translit_alias']) && !$options['translit_alias']) {
            $translit_alias = false;
        }

        if (isset($options['generate_alias']) && !$options['generate_alias']) {
            $generate_alias = false;
        }

        if (empty($data['alias']) && $generate_alias) {
            $data['alias'] = $this->createAlias($data, $translit_alias);
        }

        if (!empty($data['alias'])) {
            $this->setAlias($product_id, $data, false);
        }

        if (!empty($data['related'])) {
            $this->setRelated($product_id, $data, false);
        }

        $this->search->index('product_id', $product_id);

        $this->hook->fire('add.product.after', $data);
        return $product_id;
    }

    /**
     * Updates a product
     * @param integer $product_id
     * @param array $data
     * @return boolean
     */
    public function update($product_id, array $data)
    {
        $this->hook->fire('update.product.before', $product_id, $data);

        if (empty($product_id)) {
            return false;
        }

        $values = array();

        if (isset($data['status'])) {
            $values['status'] = (int) $data['status'];
        }

        if (isset($data['front'])) {
            $values['front'] = (int) $data['front'];
        }

        if (!empty($data['sku']) && isset($data['store_id'])) {
            $this->setSku($product_id, $data);
        }

        if (isset($data['product_class_id'])) {
            $values['product_class_id'] = (int) $data['product_class_id'];
        }

        if (!empty($data['title'])) {
            $values['title'] = $data['title'];
        }

        if (!empty($data['description'])) {
            $values['description'] = $data['description'];
        }

        if (!empty($data['currency'])) {
            $values['currency'] = $data['currency'];
        }

        if (isset($data['price'])) {
            $values['price'] = $this->price->amount($data['price'], $data['currency']);
        }

        if (isset($data['subtract'])) {
            $values['subtract'] = (int) $data['subtract'];
        }

        if (!empty($data['created'])) {
            $values['created'] = (int) $data['created'];
        }

        if (isset($data['stock'])) {
            $values['stock'] = (int) $data['stock'];
        }

        if (isset($data['store_id'])) {
            $values['store_id'] = (int) $data['store_id'];
        }

        if (isset($data['brand_category_id'])) {
            $values['brand_category_id'] = (int) $data['brand_category_id'];
        }

        if (isset($data['category_id'])) {
            $values['category_id'] = (int) $data['category_id'];
        }

        if (isset($data['user_id'])) {
            $values['user_id'] = (int) $data['user_id'];
        }

        if (!empty($data['data'])) {
            $values['data'] = serialize((array) $data['data']);
        }

        if (isset($data['width'])) {
            $values['width'] = (int) $data['width'];
        }

        if (isset($data['height'])) {
            $values['height'] = (int) $data['height'];
        }

        if (isset($data['length'])) {
            $values['length'] = (int) $data['length'];
        }

        if (isset($data['weight'])) {
            $values['weight'] = (int) $data['weight'];
        }

        if (isset($data['weight_unit'])) {
            $values['weight_unit'] = $data['weight_unit'];
        }

        if (isset($data['volume_unit'])) {
            $values['volume_unit'] = $data['volume_unit'];
        }

        $result = false;

        if (!empty($values)) {
            $values['modified'] = isset($data['modified']) ? (int) $data['modified'] : GC_TIME;
            $result = (boolean) $this->db->update('product', $values, array('product_id' => (int) $product_id));
        }

        if (!empty($data['translation'])) {
            $this->setTranslations($product_id, $data);
            $result = true;
        }

        if (!empty($data['images'])) {
            $data['images'] = $this->setImages($product_id, $data);
            $result = true;
        }

        if (!empty($data['alias'])) {
            $this->setAlias($product_id, $data);
            $result = true;
        }

        if (!empty($data['combination'])) {
            $this->setCombinations($product_id, $data);
            $result = true;
        }

        if (!empty($data['field']['attribute'])) {
            $this->setAttributes($product_id, $data);
            $result = true;
        }

        if (isset($data['related'])) {
            $this->setRelated($product_id, $data);
            $result = true;
        }

        if ($result) {
            $this->search->index('product_id', $product_id);
            Cache::clear("product.$product_id.", "*");
            //Cache::clear('cart', '*');
        }

        $this->hook->fire('update.product.after', $product_id, $data, $result);
        return (bool) $result;
    }

    /**
     * Deletes and/or adds related products
     * @param integer $product_id
     * @param array $data
     * @param boolean $delete
     */
    public function setRelated($product_id, array $data, $delete = true)
    {
        if ($delete) {
            $this->db->delete('product_related', array('product_id' => $product_id));
            $this->db->delete('product_related', array('related_product_id' => $product_id));
        }

        foreach ((array) $data['related'] as $id) {
            $this->db->insert('product_related', array('product_id' => $product_id, 'related_product_id' => $id));
            $this->db->insert('product_related', array('product_id' => $id, 'related_product_id' => $product_id));
        }
    }

    /**
     * Adds a translation
     * @param integer $product_id
     * @param string $language
     * @param string $translation
     * @return boolean
     */
    public function addTranslation($product_id, $language, array $translation)
    {
        $values = array(
            'product_id' => (int) $product_id,
            'language' => $language,
            'title' => !empty($translation['title']) ? $translation['title'] : '',
            'description' => !empty($translation['description']) ? $translation['description'] : '',
            'meta_description' => !empty($translation['meta_description']) ? $translation['meta_description'] : '',
            'meta_title' => !empty($translation['meta_title']) ? $translation['meta_title'] : '',
        );

        return (bool) $this->db->insert('product_translation', $values);
    }

    /**
     * Creates a SKU
     * @param array $data
     * @return string
     */
    public function createSku(array $data)
    {
        $pattern = $this->config->get('product_sku_pattern', 'PRODUCT-%i');
        $placeholders = $this->config->get('product_sku_placeholder', array('%i' => 'product_id'));
        return $this->sku->generate($pattern, $placeholders, $data);
    }

    /**
     * Adds a field to a product
     * @param integer $product_id
     * @param integer $field_id
     * @param integer $field_value_id
     * @param string $field_type
     * @return boolean|integer
     */
    public function addField($product_id, $field_id, $field_value_id,
            $field_type)
    {
        $arguments = func_get_args();

        $this->hook->fire('add.product.field.before', $arguments);

        if (empty($arguments)) {
            return false;
        }

        $values = array(
            'field_value_id' => (int) $field_value_id,
            'field_id' => (int) $field_id,
            'product_id' => (int) $product_id,
            'type' => $field_type,
        );

        $id = $this->db->insert('product_field', $values);

        $this->hook->fire('add.product.field.after', $arguments, $id);
        return $id;
    }

    /**
     * Adds a field combination
     * @param array $data
     * @return boolean|string
     */
    public function addCombination(array $data)
    {
        $this->hook->fire('add.option.combination.before', $data);

        if (empty($data)) {
            return false;
        }

        $fields = isset($data['fields']) ? (array) $data['fields'] : array();
        $data['combination_id'] = $this->getCombinationId($fields, $data['product_id']);

        if (!empty($data['price'])) {
            $data['price'] = $this->price->amount($data['price'], $data['currency']);
        } else {
            $data['price'] = 0;
        }

        $values = array(
            'combination_id' => $data['combination_id'],
            'product_id' => (int) $data['product_id'],
            'stock' => isset($data['stock']) ? (int) $data['stock'] : 0,
            'file_id' => isset($data['file_id']) ? (int) $data['file_id'] : 0,
            'price' => $data['price'],
        );

        $id = $this->db->insert('option_combination', $values);
        $this->hook->fire('add.option.combination.after', $data, $data['combination_id'], $id);
        return $data['combination_id'];
    }

    /**
     * Creates a field combination id from the field value ids
     * @param array $field_value_ids
     * @param null|integer $product_id
     * @return string
     */
    public function getCombinationId(array $field_value_ids, $product_id = null)
    {
        sort($field_value_ids);

        if (!empty($product_id)) {
            return $product_id . '-' . implode('_', $field_value_ids);
        }

        return implode('_', $field_value_ids);
    }

    /**
     * Creates a URL alis
     * @param array $data
     * @param boolean $translit_alias
     * @return string
     */
    public function createAlias(array $data, $translit_alias = true)
    {
        $pattern = $this->config->get('product_alias_pattern', '%t.html');
        $placeholders = $this->config->get('product_alias_placeholder', array('%t' => 'title'));
        return $this->alias->generate($pattern, $placeholders, $data, $translit_alias);
    }

    /**
     * Loads a product from the database
     * @param integer $product_id
     * @param string|null $language
     * @return array
     */
    public function get($product_id, $language = null)
    {
        $this->hook->fire('get.product.before', $product_id);

        if (empty($product_id)) {
            return array();
        }

        $sth = $this->db->prepare('SELECT * FROM product WHERE product_id=:product_id');
        $sth->execute(array(':product_id' => (int) $product_id));

        $product = $sth->fetch(PDO::FETCH_ASSOC);

        if (!empty($product)) {
            $product['data'] = unserialize($product['data']);
            $product['language'] = 'und';

            foreach ($this->getTranslations($product_id) as $translation) {
                $product['translation'][$translation['language']] = $translation;
            }

            if (isset($language) && isset($product['translation'][$language])) {
                $product = $product['translation'][$language] + $product;
            }

            $product['images'] = $this->image->getList('product_id', $product_id);
            $product['field'] = $this->getFields($product_id);
            $product['combination'] = $this->getCombinations($product_id);

            $sku_codes = $this->sku->getByProduct($product_id);
            $product['sku'] = $sku_codes['base'];

            foreach ($product['combination'] as $combination_id => &$combination) {
                if (isset($sku_codes['combinations'][$combination_id])) {
                    $combination['sku'] = $sku_codes['combinations'][$combination_id];
                }
            }
        }

        $this->hook->fire('get.product.after', $product_id, $product);
        return $product;
    }

    /**
     * Returns an array of product translations
     * @param integer $product_id
     * @return array
     */
    public function getTranslations($product_id)
    {
        $sth = $this->db->prepare('SELECT * FROM product_translation WHERE product_id=:product_id');
        $sth->execute(array(':product_id' => (int) $product_id));

        return $sth->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Returns an array of fields for a given product
     * @param integer $prodict_id
     * @return array
     */
    public function getFields($prodict_id)
    {
        $sql = 'SELECT * FROM product_field WHERE product_id=:product_id';
        $sth = $this->db->prepare($sql);
        $sth->execute(array(':product_id' => (int) $prodict_id));

        $fields = array();
        foreach ($sth->fetchAll(PDO::FETCH_ASSOC) as $field) {
            $fields[$field['type']][$field['field_id']][] = $field['field_value_id'];
        }

        return $fields;
    }

    /**
     * Returns an array of option combinations for a given product
     * @param integer $product_id
     * @return array
     */
    public function getCombinations($product_id)
    {
        $sql = '
        SELECT oc.*, ps.sku
        FROM option_combination oc
        LEFT JOIN product_sku ps ON(oc.product_id = ps.product_id AND ps.combination_id = oc.combination_id)
        WHERE oc.product_id=:product_id';

        $sth = $this->db->prepare($sql);
        $sth->execute(array(':product_id' => (int) $product_id));

        $combinations = array();
        foreach ($sth->fetchAll(PDO::FETCH_ASSOC) as $combination) {
            $combinations[$combination['combination_id']] = $combination;
            $combinations[$combination['combination_id']]['fields'] = $this->getCombinationFieldValue($combination['combination_id']);
        }

        return $combinations;
    }

    /**
     *
     * @param string $combination_id
     * @return array
     */
    public function getCombinationFieldValue($combination_id)
    {
        $field_value_ids = explode('_', substr($combination_id, strpos($combination_id, '-') + 1));
        sort($field_value_ids);
        return $field_value_ids;
    }

    /**
     * Returns a product by the SKU
     * @param string $sku
     * @param integer $store_id
     * @param string|null $language
     * @return array
     */
    public function getBySku($sku, $store_id, $language = null)
    {
        if (!isset($language)) {
            $language = $this->language->current();
        }

        $sql = '
        SELECT p.*,
            COALESCE(NULLIF(pt.title, ""), p.title) AS title, oc.combination_id,
            oc.price AS option_price, oc.file_id AS option_file_id,
            oc.stock AS option_stock
        FROM product p
        LEFT JOIN product_sku ps ON(p.product_id=ps.product_id)
        LEFT JOIN option_combination oc ON(ps.combination_id=oc.combination_id)
        LEFT JOIN product_translation pt ON(p.product_id=pt.product_id AND pt.language=:language)
        WHERE ps.sku=:sku AND ps.store_id=:store_id';

        $sth = $this->db->prepare($sql);

        $sth->execute(array(
            ':sku' => $sku,
            ':store_id' => (int) $store_id,
            ':language' => $language
        ));

        $product = $sth->fetch(PDO::FETCH_ASSOC);

        if (!empty($product)) {
            $product['data'] = unserialize($product['data']);
            $product['images'] = $this->image->getList('product_id', $product['product_id']);
        }

        return $product;
    }

    /**
     * Deletes a product
     * @param integer $product_id
     * @return boolean
     */
    public function delete($product_id)
    {
        $this->hook->fire('delete.product.before', $product_id);

        if (empty($product_id)) {
            return false;
        }

        if (!$this->canDelete($product_id)) {
            return false;
        }

        $this->db->delete('product', array('product_id' => $product_id));
        $this->db->delete('option_combination', array('product_id' => $product_id));
        $this->db->delete('product_translation', array('product_id' => $product_id));
        $this->db->delete('product_field', array('product_id' => $product_id));
        $this->db->delete('review', array('product_id' => $product_id));
        $this->db->delete('bookmark', array('id_key' => 'product_id', 'id_value' => $product_id));
        $this->db->delete('alias', array('id_key' => 'product_id', 'id_value' => $product_id));
        $this->db->delete('file', array('id_key' => 'product_id', 'id_value' => $product_id));
        $this->db->delete('product_sku', array('product_id' => $product_id));
        $this->db->delete('cart', array('product_id' => $product_id));
        $this->db->delete('rating', array('product_id' => $product_id));
        $this->db->delete('rating_user', array('product_id' => $product_id));
        $this->db->delete('search_index', array('id_key' => 'product_id', 'id_value' => $product_id));

        //Cache::clear('cart', '*');

        $this->hook->fire('delete.product.after', $product_id);
        return true;
    }

    /**
     * Returns true if a product can be deleted
     * @param integer $product_id
     * @return boolean
     */
    public function canDelete($product_id)
    {
        $sth = $this->db->prepare('SELECT cart_id FROM cart WHERE product_id=:product_id AND order_id > 0');
        $sth->execute(array(':product_id' => (int) $product_id));
        return !$sth->fetchColumn();
    }

    /**
     * Updates a field combination
     * @param string $combination_id
     * @param array $data
     * @return boolean
     */
    public function updateCombination($combination_id, array $data)
    {
        $this->hook->fire('update.option.combination.before', $combination_id, $data);

        if (empty($combination_id)) {
            return false;
        }

        $values = array();
        if (isset($data['price']) && !empty($data['currency'])) {
            $values['price'] = $this->price->amount($data['price'], $data['currency']);
        }

        if (isset($data['stock'])) {
            $values['stock'] = (int) $data['stock'];
        }

        if (empty($values)) {
            return false;
        }

        $result = $this->db->update('option_combination', $values, array('combination_id' => $combination_id));
        $this->hook->fire('update.option.combination.after', $combination_id, $data, $result);
        return (bool) $result;
    }

    /**
     * Adds a product to comparison
     * @param integer $product_id
     * @return boolean
     */
    public function addToCompare($product_id)
    {
        $this->hook->fire('add.product.compare.before', $product_id);

        if (empty($product_id)) {
            return false;
        }

        $product_ids = $this->getCompared();

        if (in_array($product_id, $product_ids)) {
            return false;
        }

        $product_ids[] = $product_id;

        $limit = (int) $this->config->get('product_comparison_limit', 10);

        if (!empty($limit)) {
            $product_ids = array_slice($product_ids, -$limit);
        }

        $result = $this->setCompared($product_ids);
        $this->hook->fire('add.product.compare.after', $product_id, $result);
        return $result;
    }

    /**
     * Returns an array of products to be compared
     * @return array
     */
    public function getCompared()
    {
        $product_ids = &Cache::memory(__FUNCTION__);

        if (isset($product_ids)) {
            return $product_ids;
        }

        $product_ids = array();
        $saved = $this->request->cookie('comparison');

        if (!empty($saved)) {
            $product_ids = explode(',', $saved);
        }

        return $product_ids;
    }

    /**
     * Whether a product is added to comparison
     * @param integer $product_id
     * @return boolean
     */
    public function isCompared($product_id)
    {
        $compared = $this->getCompared();
        return in_array($product_id, $compared);
    }

    /**
     * Sets compared products to the cookie
     * @param array $product_ids
     * @return boolean
     */
    public function setCompared(array $product_ids)
    {
        $lifespan = $this->config->get('product_comparison_cookie_lifespan', 604800);
        return Tool::setCookie('comparison', implode(',', (array) $product_ids), $lifespan);
    }

    /**
     * Removes a products from comparison
     * @param integer $product_id
     * @return array
     */
    public function removeCompared($product_id)
    {
        if (empty($product_id)) {
            return false;
        }

        $compared = $this->getCompared();

        if (empty($compared)) {
            return false;
        }

        $product_ids = array_flip($compared);
        unset($product_ids[$product_id]);
        $rest = array_keys($product_ids);
        $this->setCompared($rest);
        return $rest;
    }

    /**
     * Calculates product price
     * @param array $product
     * @param integer $store_id
     * @return array
     */
    public function calculate(array $product, $store_id = 1)
    {
        $rules = $this->pricerule->getSuited('catalog', $product, $store_id);

        $components = array();
        $price = $product['price'];
        $currency_code = $product['currency'];

        foreach ($rules as $id => $rule) {
            $this->pricerule->calculateComponents($price, $components, $currency_code, $rule);
        }

        return array('total' => $price, 'currency' => $currency_code, 'components' => $components);
    }

    /**
     * Calculates and returns product physical volume
     * @param array $product
     * @param integer $round
     * @param string $convert_to
     * @return mixed
     */
    public function getVolume(array $product, $round = 2, $convert_to = '')
    {
        $volume_value = (int) $product['width'] * (int) $product['height'] * (int) $product['length'];

        if (empty($convert_to)) {
            return round($volume_value, $round);
        }

        // TODO: complete
    }

    /**
     * Returns an array of related products
     * @param integer $product_id
     * @param boolean $load
     * @param array $data
     * @return array
     */
    public function getRelated($product_id, $load = false, array $data = array())
    {
        $sql = 'SELECT related_product_id FROM product_related WHERE product_id=:product_id';

        if (!empty($data['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $data['limit']));
        }

        $sth = $this->db->prepare($sql);
        $sth->execute(array(':product_id' => $product_id));
        $list = $sth->fetchAll(PDO::FETCH_COLUMN, 0);

        if (!empty($list) && $load) {
            $data += array('product_id' => $list);
            $list = $this->getList($data);
        }

        return $list;
    }

    /**
     * Returns an array of products or number of total products
     * @param array $data
     * @return array|integer
     */
    public function getList(array $data = array())
    {
        $this->hook->fire('get.product.list.before', $data);

        $sql = 'SELECT p.*, a.alias, COALESCE(NULLIF(pt.title, ""), p.title) AS title, pt.language, ps.sku ';

        if (!empty($data['count'])) {
            $sql = 'SELECT COUNT(p.product_id) ';
        }

        $sql .= '
            FROM product p
            LEFT JOIN product_translation pt ON(p.product_id = pt.product_id AND pt.language=?)
            LEFT JOIN alias a ON(a.id_key=? AND a.id_value=p.product_id)
            LEFT JOIN product_sku ps ON(p.product_id = ps.product_id AND LENGTH(ps.combination_id) = 0)';

        $language = $this->language->current();
        $where = array($language, 'product_id');

        if (!empty($data['product_id'])) {
            $product_ids = (array) $data['product_id'];
            $placeholders = rtrim(str_repeat('?, ', count($product_ids)), ', ');
            $sql .= 'WHERE p.product_id IN(' . $placeholders . ')';
            $where = array_merge($where, $product_ids);
        } else {
            $sql .= 'WHERE p.product_id > 0';
        }

        if (isset($data['title'])) {
            $sql .= ' AND (p.title LIKE ? OR (pt.title LIKE ? AND pt.language=?))';
            $where[] = "%{$data['title']}%";
            $where[] = "%{$data['title']}%";
            $where[] = $language;
        }

        if (isset($data['language'])) {
            $sql .= ' AND pt.language = ?';
            $where[] = $data['language'];
        }

        if (isset($data['sku'])) {
            $sql .= ' AND ps.sku LIKE ?';
            $where[] = "%{$data['sku']}%";
        }

        if (isset($data['price']) && isset($data['currency'])) {
            $sql .= ' AND p.price = ?';
            $where[] = $this->price->amount((int) $data['price'], $data['currency']);
        }

        if (isset($data['currency'])) {
            $sql .= ' AND p.currency = ?';
            $where[] = $data['currency'];
        }

        if (isset($data['stock'])) {
            $sql .= ' AND p.stock = ?';
            $where[] = (int) $data['stock'];
        }

        if (isset($data['category_id'])) {
            $sql .= ' AND p.category_id = ?';
            $where[] = (int) $data['category_id'];
        }

        if (isset($data['status'])) {
            $sql .= ' AND p.status = ?';
            $where[] = (int) $data['status'];
        }

        if (isset($data['front'])) {
            $sql .= ' AND p.front = ?';
            $where[] = (int) $data['front'];
        }

        if (isset($data['store_id'])) {
            $sql .= ' AND p.store_id = ?';
            $where[] = (int) $data['store_id'];
        }

        if (empty($data['count'])) {
            $sql .= ' GROUP BY p.product_id';
        }

        if (isset($data['sort']) && (isset($data['order']) && in_array($data['order'], array('asc', 'desc'), true))) {
            $order = $data['order'];

            switch ($data['sort']) {
                case 'title':
                    $sql .= " ORDER BY p.title $order";
                    break;
                case 'sku':
                    $sql .= " ORDER BY ps.sku $order";
                    break;
                case 'price':
                    $sql .= " ORDER BY p.price $order";
                    break;
                case 'currency':
                    $sql .= " ORDER BY p.currency $order";
                    break;
                case 'stock':
                    $sql .= " ORDER BY p.stock $order";
                    break;
                case 'status':
                    $sql .= " ORDER BY p.status $order";
                    break;
                case 'front':
                    $sql .= " ORDER BY p.front $order";
                    break;
                case 'store_id':
                    $sql .= " ORDER BY p.store_id $order";
            }
        } else {
            $sql .= " ORDER BY p.created DESC";
        }

        if (!empty($data['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $data['limit']));
        }

        $sth = $this->db->prepare($sql);
        $sth->execute($where);

        if (!empty($data['count'])) {
            return $sth->fetchColumn();
        }

        $list = array();
        foreach ($sth->fetchAll(PDO::FETCH_ASSOC) as $product) {
            $list[$product['product_id']] = $product;
        }

        $this->hook->fire('get.product.list.after', $list);
        return $list;
    }

    /**
     * Saves a product to the cookie
     * @param integer $product_id
     * @param integer $limit
     * @param integer $lifespan
     * @return array
     */
    public function setViewed($product_id, $limit, $lifespan)
    {
        $product_ids = $this->getViewed($limit);

        if (in_array($product_id, $product_ids)) {
            return $product_ids;
        }

        $product_ids[] = (int) $product_id;
        Tool::setCookie('viewed_products', implode('|', array_unique($product_ids)), $lifespan);
        return $product_ids;
    }

    /**
     * Returns an array of recently viewed product IDs
     * @param integer|null $limit
     * @return array
     */
    public function getViewed($limit = null)
    {
        $cookie = Tool::getCookie('viewed_products', '');
        $product_ids = array_filter(explode('|', $cookie), 'is_numeric');

        if (isset($limit)) {
            $product_ids = array_slice($product_ids, -$limit);
        }

        return $product_ids;
    }

    /**
     * Deletes and/or adds a new base SKU
     * @param integer $product_id
     * @param array $data
     * @param boolean $delete
     * @return integer
     */
    protected function setSku($product_id, array $data, $delete = true)
    {
        if ($delete) {
            $this->sku->delete($product_id, array('base' => true));
        }

        return $this->sku->add($data['sku'], $product_id, $data['store_id']);
    }

    /**
     * Deletes and/or adds product translations
     * @param integer $product_id
     * @param array $data
     * @param boolean $delete
     * @return boolean
     */
    protected function setTranslations($product_id, array $data, $delete = true)
    {
        if ($delete) {
            $this->deleteTranslation($product_id);
        }

        foreach ($data['translation'] as $language => $translation) {
            $this->addTranslation($product_id, $language, $translation);
        }

        return true;
    }

    /**
     * Adds product images
     * @param integer $product_id
     * @param array $data
     * @return array
     */
    protected function setImages($product_id, array $data)
    {
        return $this->image->setMultiple('product_id', $product_id, $data['images']);
    }

    /**
     * Deletes and/or adds an alias
     * @param integer $product_id
     * @param array $data
     * @param boolean $delete
     * @return integer|boolean
     */
    protected function setAlias($product_id, array $data, $delete = true)
    {
        if ($delete) {
            $this->alias->delete('product_id', (int) $product_id);
        }

        return $this->alias->add('product_id', $product_id, $data['alias']);
    }

    /**
     * Deletes and/or adds product combinations
     * @param integer $product_id
     * @param array $data
     * @param boolean $delete
     * @return boolean
     */
    protected function setCombinations($product_id, array $data, $delete = true)
    {
        if ($delete) {
            $this->deleteField('option', $product_id);
            $this->deleteCombination(false, $product_id);
        }

        if (isset($data['store_id']) && $delete) {
            $this->sku->delete($product_id, array('combinations' => true));
        }

        $i = 1;
        foreach ($data['combination'] as $combination) {
            $combination['product_id'] = $product_id;
            $combination['currency'] = $data['currency'];

            if (isset($combination['path']) && isset($data['images'][$combination['path']])) {
                $combination['file_id'] = $data['images'][$combination['path']];
            }

            if (empty($combination['sku'])) {
                $sku_pattern = $data['sku'] . '-' . $i;
                $combination['sku'] = $this->sku->generate($sku_pattern, false, array('store_id' => $data['store_id']));
            }

            $combination_id = $this->addCombination($combination);

            if (isset($data['store_id'])) {
                $this->sku->add($combination['sku'], $product_id, $data['store_id'], $combination_id);
            }

            if (empty($combination['fields'])) {
                continue;
            }

            foreach ($combination['fields'] as $field_id => $field_value_id) {
                $this->addField($product_id, $field_id, $field_value_id, 'option');
            }

            $i++;
        }

        return true;
    }

    /**
     * Deletes and/or adds product attribute fields
     * @param integer $product_id
     * @param array $data
     * @param boolean $delete
     * @return boolean
     */
    protected function setAttributes($product_id, array $data, $delete = true)
    {
        if ($delete) {
            $this->deleteField('attribute', $product_id);
        }

        foreach ($data['field']['attribute'] as $field_id => $field_value_ids) {
            foreach ((array) $field_value_ids as $field_value_id) {
                $this->addField($product_id, $field_id, $field_value_id, 'attribute');
            }
        }

        return true;
    }

    /**
     * Deletes product translation(s)
     * @param integer $product_id
     * @param null|string $language
     * @return boolean
     */
    protected function deleteTranslation($product_id, $language = null)
    {
        $where = array('product_id' => (int) $product_id);

        if (isset($language)) {
            $where['language'] = $language;
        }

        return (bool) $this->db->delete('product_translation', $where);
    }

    /**
     * Deletes product field either by an ID or type
     * @param string|integer $field_id
     * @param integer $product_id
     * @return boolean
     */
    protected function deleteField($field_id, $product_id)
    {
        if (is_numeric($field_id)) {
            $where['field_id'] = (int) $field_id;
        } elseif (is_string($field_id)) {
            $where = array('type' => $field_id, 'product_id' => (int) $product_id);
        } else {
            $where = array('product_id' => (int) $product_id);
        }

        return (bool) $this->db->delete('product_field', $where);
    }

    /**
     * Deletes option combination(s) either by combination or product ID
     * @param string $combination_id
     * @param integer|null $product_id
     * @return boolean
     */
    protected function deleteCombination($combination_id, $product_id = null)
    {
        if (isset($product_id)) {
            $where = array('product_id' => (int) $product_id);
        } else {
            $where = array('combination_id' => $combination_id);
        }

        $this->db->delete('option_combination', $where);
        return true;
    }

}
