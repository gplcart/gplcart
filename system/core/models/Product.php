<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\models;

use PDO;
use core\Model;
use core\classes\Tool;
use core\classes\Cache;
use core\classes\Request;
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
class Product extends Model
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
     * Request class instance
     * @var \core\classes\Request $request
     */
    protected $request;

    /**
     * Constructor
     * @param ModelsPrice $price
     * @param ModelsPriceRule $pricerule
     * @param ModelsImage $image
     * @param ModelsAlias $alias
     * @param ModelsLanguage $language
     * @param ModelsSku $sku
     * @param ModelsSearch $search
     * @param Request $request
     */
    public function __construct(ModelsPrice $price, ModelsPriceRule $pricerule,
            ModelsImage $image, ModelsAlias $alias, ModelsLanguage $language,
            ModelsSku $sku, ModelsSearch $search, Request $request)
    {
        parent::__construct();

        $this->sku = $sku;
        $this->price = $price;
        $this->image = $image;
        $this->alias = $alias;
        $this->search = $search;
        $this->request = $request;
        $this->language = $language;
        $this->pricerule = $pricerule;
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

        $data += array(
            'created' => GC_TIME,
            'currency' => $this->config->get('currency', 'USD'),
        );

        if (!empty($data['price'])) {
            $data['price'] = $this->price->amount($data['price'], $data['currency']);
        }

        $values = $this->prepareDbInsert('product', $data);

        $data['product_id'] = $this->db->insert('product', $values);

        $this->setTranslation($data, false);
        $this->setImages($data);

        if (empty($data['sku'])) {
            $data['sku'] = $this->createSku($data);
        }

        $this->setCombinations($data, false);
        $this->setAttributes($data, false);
        $this->setSku($data, false);

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

        $this->setAlias($data, false);
        $this->setRelated($data, false);

        $this->search->index('product_id', $data['product_id']);

        $this->hook->fire('add.product.after', $data);
        return $data['product_id'];
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

        $data += array('product_id' => $product_id, 'modified' => GC_TIME);

        if (isset($data['price'])) {
            $data['price'] = $this->price->amount($data['price'], $data['currency']);
        }

        $values = $this->filterDbValues('product', $data);

        $updated = 0;

        if (!empty($values)) {
            $conditions = array('product_id' => (int) $product_id);
            $updated += (int) $this->db->update('product', $values, $conditions);
        }

        $updated += (int) $this->setSku($data);
        $updated += (int) $this->setTranslation($data);
        $updated += (int) $this->setImages($data);
        $updated += (int) $this->setAlias($data);
        $updated += (int) $this->setCombinations($data);
        $updated += (int) $this->setAttributes($data);
        $updated += (int) $this->setRelated($data);

        $result = false;

        if ($updated > 0) {
            $result = true;
            $this->search->index('product_id', $product_id);
            Cache::clear("product.$product_id.", "*");
        }

        $this->hook->fire('update.product.after', $product_id, $data, $result);
        return (bool) $result;
    }

    /**
     * Deletes and/or adds related products
     * @param array $data
     * @param boolean $delete
     * @return boolean
     */
    public function setRelated(array $data, $delete = true)
    {
        if (empty($data['related'])) {
            return false;
        }

        $product_id = $data['product_id'];

        if ($delete) {
            $this->db->delete('product_related', array('product_id' => $product_id));
            $this->db->delete('product_related', array('related_product_id' => $product_id));
        }

        foreach ((array) $data['related'] as $id) {
            $this->db->insert('product_related', array('product_id' => $product_id, 'related_product_id' => $id));
            $this->db->insert('product_related', array('product_id' => $id, 'related_product_id' => $product_id));
        }

        return true;
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
        $translation += array(
            'language' => $language,
            'product_id' => $product_id
        );

        $values = $this->prepareDbInsert('product_translation', $translation);
        return (bool) $this->db->insert('product_translation', $values);
    }

    /**
     * Deletes and/or adds product translations
     * @param array $data
     * @param boolean $delete
     * @return boolean
     */
    protected function setTranslation(array $data, $delete = true)
    {
        if (empty($data['translation'])) {
            return false;
        }

        if ($delete) {
            $this->deleteTranslation($data['product_id']);
        }

        foreach ($data['translation'] as $language => $translation) {
            $this->addTranslation($data['product_id'], $language, $translation);
        }

        return true;
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
     * @param array $data
     * @return boolean|integer
     */
    public function addField(array $data)
    {
        $this->hook->fire('add.product.field.before', $data);

        if (empty($data)) {
            return false;
        }

        $values = $this->prepareDbInsert('product_field', $data);
        $id = $this->db->insert('product_field', $values);

        $this->hook->fire('add.product.field.after', $data, $id);
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

        $fields = empty($data['fields']) ? array() : (array) $data['fields'];
        $data['combination_id'] = $this->getCombinationId($fields, $data['product_id']);

        if (!empty($data['price'])) {
            $data['price'] = $this->price->amount($data['price'], $data['currency']);
        }

        $values = $this->prepareDbInsert('option_combination', $data);

        $id = $this->db->insert('option_combination', $values);
        $this->hook->fire('add.option.combination.after', $data, $id);

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

        $sth = $this->db->prepare('SELECT * FROM product WHERE product_id=?');
        $sth->execute(array($product_id));

        $product = $sth->fetch(PDO::FETCH_ASSOC);

        if (!empty($product)) {

            $product['data'] = unserialize($product['data']);

            $this->attachFields($product);
            $this->attachCombinations($product);
            $this->attachImage($product, $language);
            $this->attachTranslation($product, $language);
        }

        $this->hook->fire('get.product.after', $product_id, $product);
        return $product;
    }

    /**
     * Adds translations to the product
     * @param array $product
     * @param null|string $language
     */
    protected function attachTranslation(array &$product, $language)
    {
        $product['language'] = 'und';

        $translations = $this->getTranslation($product['product_id']);

        foreach ($translations as $translation) {
            $product['translation'][$translation['language']] = $translation;
        }

        if (isset($language) && isset($product['translation'][$language])) {
            $product = $product['translation'][$language] + $product;
        }
    }

    /**
     * Returns an array of product translations
     * @param integer $product_id
     * @return array
     */
    public function getTranslation($product_id)
    {
        $sth = $this->db->prepare('SELECT * FROM product_translation WHERE product_id=?');
        $sth->execute(array($product_id));

        return $sth->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Adds images to the product
     * @param array $product
     * @param null|string $language
     */
    protected function attachImage(array &$product, $language)
    {
        $images = $this->image->getList('product_id', $product['product_id']);

        foreach ($images as &$image) {
            $translations = $this->image->getTranslation($image['file_id']);
            foreach ($translations as $translation) {
                $image['translation'][$translation['language']] = $translation;
            }

            if (isset($language) && isset($image['translation'][$language])) {
                $image = $image['translation'][$language] + $image;
            }
        }

        $product['images'] = $images;
    }

    /**
     * Adds fields to the product
     * @param array $product
     */
    protected function attachFields(array &$product)
    {
        $product['field'] = $this->getFields($product['product_id']);
    }

    /**
     * Adds option combinations to the product
     * @param array $product
     */
    protected function attachCombinations(array &$product)
    {
        $product['combination'] = $this->getCombinations($product['product_id']);

        $sku_codes = $this->sku->getByProduct($product['product_id']);
        $product['sku'] = $sku_codes['base'];

        foreach ($product['combination'] as $combination_id => &$combination) {
            if (isset($sku_codes['combinations'][$combination_id])) {
                $combination['sku'] = $sku_codes['combinations'][$combination_id];
            }
        }
    }

    /**
     * Returns an array of fields for a given product
     * @param integer $prodict_id
     * @return array
     */
    public function getFields($prodict_id)
    {
        $sql = 'SELECT * FROM product_field WHERE product_id=?';
        $sth = $this->db->prepare($sql);
        $sth->execute(array($prodict_id));

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
        $sql = 'SELECT oc.*, ps.sku'
                . ' FROM option_combination oc'
                . ' LEFT JOIN product_sku ps'
                . ' ON(oc.product_id = ps.product_id AND ps.combination_id = oc.combination_id)'
                . ' WHERE oc.product_id=:product_id';

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

        $sql = 'SELECT p.*, COALESCE(NULLIF(pt.title, ""), p.title) AS title,'
                . ' oc.combination_id, oc.price AS option_price,'
                . ' oc.file_id AS option_file_id, oc.stock AS option_stock'
                . ' FROM product p'
                . ' LEFT JOIN product_sku ps ON(p.product_id=ps.product_id)'
                . ' LEFT JOIN option_combination oc ON(ps.combination_id=oc.combination_id)'
                . ' LEFT JOIN product_translation pt ON(p.product_id=pt.product_id AND pt.language=:language)'
                . ' WHERE ps.sku=:sku AND ps.store_id=:store_id';

        $sth = $this->db->prepare($sql);

        $conditions = array(
            ':sku' => $sku,
            ':language' => $language,
            ':store_id' => $store_id
        );

        $sth->execute($conditions);

        $product = $sth->fetch(PDO::FETCH_ASSOC);

        if (empty($product)) {
            return array();
        }

        $product['data'] = unserialize($product['data']);
        $product['images'] = $this->image->getList('product_id', $product['product_id']);
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

        $conditions = array('product_id' => $product_id);
        $conditions2 = array('id_key' => 'product_id', 'id_value' => $product_id);

        $delete = array(
            'cart' => $conditions,
            'review' => $conditions,
            'rating' => $conditions,
            'product' => $conditions,
            'wishlist' => $conditions,
            'product_sku' => $conditions,
            'rating_user' => $conditions,
            'product_field' => $conditions,
            'option_combination' => $conditions,
            'product_translation' => $conditions,
            'file' => $conditions2,
            'alias' => $conditions2,
            'search_index' => $conditions2,
            'collection_item' => $conditions2
        );

        foreach ($delete as $table => $where) {
            $this->db->delete($table, $where);
        }

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
        $sql = 'SELECT cart_id'
                . ' FROM cart WHERE product_id=? AND order_id > 0';

        $sth = $this->db->prepare($sql);
        $sth->execute(array($product_id));
        $result = $sth->fetchColumn();

        return empty($result);
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
        $items = &Cache::memory(__FUNCTION__);

        if (isset($items)) {
            return (array) $items;
        }

        $items = array();
        $saved = $this->request->cookie('comparison');

        if (!empty($saved)) {
            $items = array_filter(array_map('trim', explode('|', urldecode($saved))), 'is_numeric');
        }

        return $items;
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
        return Tool::setCookie('comparison', implode('|', (array) $product_ids), $lifespan);
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
        $sql = 'SELECT related_product_id FROM product_related WHERE product_id=?';

        if (!empty($data['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $data['limit']));
        }

        $sth = $this->db->prepare($sql);
        $sth->execute(array($product_id));
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

        $sql = 'SELECT p.*, a.alias, COALESCE(NULLIF(pt.title, ""), p.title) AS title, pt.language, ps.sku';

        if (!empty($data['count'])) {
            $sql = 'SELECT COUNT(p.product_id)';
        }

        $sql .= ' FROM product p'
                . ' LEFT JOIN product_translation pt ON(p.product_id = pt.product_id AND pt.language=?)'
                . ' LEFT JOIN alias a ON(a.id_key=? AND a.id_value=p.product_id)'
                . ' LEFT JOIN product_sku ps ON(p.product_id = ps.product_id AND LENGTH(ps.combination_id) = 0)';

        $language = $this->language->current();
        $where = array($language, 'product_id');

        if (!empty($data['product_id'])) {
            $product_ids = (array) $data['product_id'];
            $placeholders = rtrim(str_repeat('?, ', count($product_ids)), ', ');
            $sql .= ' WHERE p.product_id IN(' . $placeholders . ')';
            $where = array_merge($where, $product_ids);
        } else {
            $sql .= ' WHERE p.product_id > 0';
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

        if (isset($data['store_id'])) {
            $sql .= ' AND p.store_id = ?';
            $where[] = (int) $data['store_id'];
        }

        if (empty($data['count'])) {
            $sql .= ' GROUP BY p.product_id';
        }

        $allowed_order = array('asc', 'desc');
        $allowed_sort = array('title', 'sku', 'price', 'currency', 'stock', 'status', 'store_id');

        if (isset($data['sort']) && in_array($data['sort'], $allowed_sort) && isset($data['order']) && in_array($data['order'], $allowed_order)) {
            $sql .= " ORDER BY p.{$data['sort']} {$data['order']}";
        } else {
            $sql .= " ORDER BY p.created DESC";
        }

        if (!empty($data['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $data['limit']));
        }

        $sth = $this->db->prepare($sql);
        $sth->execute($where);

        if (!empty($data['count'])) {
            return (int) $sth->fetchColumn();
        }

        $results = $sth->fetchAll(PDO::FETCH_ASSOC);

        $list = array();
        foreach ($results as $product) {
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
        $existing = $this->getViewed($limit);

        if (in_array($product_id, $existing)) {
            return $existing;
        }

        $existing[] = (int) $product_id;
        $saved = array_unique($existing);

        Tool::setCookie('viewed_products', implode('|', $saved), $lifespan);
        return $saved;
    }

    /**
     * Returns an array of recently viewed product IDs
     * @param integer|null $limit
     * @return array
     */
    public function getViewed($limit = null)
    {
        $cookie = Tool::getCookie('viewed_products', '');
        $products = array_filter(explode('|', $cookie), 'is_numeric');

        if (isset($limit)) {
            $products = array_slice($products, -$limit);
        }

        return $products;
    }

    /**
     * Deletes and/or adds a new base SKU
     * @param array $data
     * @param boolean $delete
     * @return bool
     */
    protected function setSku(array &$data, $delete = true)
    {
        if (empty($data['sku']) || !isset($data['store_id'])) {
            return false;
        }

        if ($delete) {
            $this->sku->delete($data['product_id'], array('base' => true));
        }

        return (bool) $this->sku->add($data['sku'], $data['product_id'], $data['store_id']);
    }

    /**
     * Adds product images
     * @param array $data
     * @return boolean
     */
    protected function setImages(array &$data)
    {
        if (empty($data['images'])) {
            return false;
        }

        $data['images'] = $this->image->setMultiple('product_id', $data['product_id'], $data['images']);
        return !empty($data['images']);
    }

    /**
     * Deletes and/or adds an alias
     * @param array $data
     * @param boolean $delete
     * @return boolean
     */
    protected function setAlias(array $data, $delete = true)
    {
        if (empty($data['alias'])) {
            return false;
        }

        if ($delete) {
            $this->alias->delete('product_id', $data['product_id']);
        }

        return (bool) $this->alias->add('product_id', $data['product_id'], $data['alias']);
    }

    /**
     * Deletes and/or adds product combinations
     * @param array $data
     * @param boolean $delete
     * @return boolean
     */
    protected function setCombinations(array $data, $delete = true)
    {
        if (empty($data['combination'])) {
            return false;
        }

        $product_id = $data['product_id'];

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

                $options = array(
                    'type' => 'option',
                    'field_id' => $field_id,
                    'product_id' => $product_id,
                    'field_value_id' => $field_value_id
                );

                $this->addField($options);
            }

            $i++;
        }

        return true;
    }

    /**
     * Deletes and/or adds product attribute fields
     * @param array $data
     * @param boolean $delete
     * @return boolean
     */
    protected function setAttributes(array $data, $delete = true)
    {
        if (empty($data['field']['attribute'])) {
            return false;
        }

        $product_id = $data['product_id'];

        if ($delete) {
            $this->deleteField('attribute', $product_id);
        }

        foreach ($data['field']['attribute'] as $field_id => $field_value_ids) {
            foreach ((array) $field_value_ids as $field_value_id) {

                $options = array(
                    'type' => 'attribute',
                    'field_id' => $field_id,
                    'product_id' => $product_id,
                    'field_value_id' => $field_value_id
                );

                $this->addField($options);
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
