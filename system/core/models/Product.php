<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\models;

use core\Model;
use core\helpers\Tool;
use core\helpers\Cache;
use core\helpers\Request;
use core\models\Sku as ModelsSku;
use core\models\Price as ModelsPrice;
use core\models\Image as ModelsImage;
use core\models\Alias as ModelsAlias;
use core\models\Search as ModelsSearch;
use core\models\Language as ModelsLanguage;
use core\models\PriceRule as ModelsPriceRule;
use core\models\Combination as ModelsCombination;
use core\models\ProductField as ModelsProductField;

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
     * Product field model instance
     * @var \core\models\ProductField $product_field
     */
    protected $product_field;

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
     * @var \core\helpers\Request $request
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
     * @param ModelsProductField $product_field
     * @param Request $request
     */
    public function __construct(ModelsPrice $price, ModelsPriceRule $pricerule,
            ModelsImage $image, ModelsAlias $alias, ModelsLanguage $language,
            ModelsSku $sku, ModelsSearch $search,
            ModelsProductField $product_field, Request $request)
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
        $this->product_field = $product_field;
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
        
        $data['created'] = GC_TIME;
        $data += array('currency' => $this->config->get('currency', 'USD'));
        $data['product_id'] = $this->db->insert('product', $data);

        $this->setTranslation($data, false);
        $this->setImages($data);
        $this->setSku($data, false);
        $this->setSkuCombinations($data, false);
        $this->setOptions($data, false);
        $this->setAttributes($data, false);

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

        $data['modified'] = GC_TIME;
        $data['product_id'] = $product_id;

        if (isset($data['price'])) {
            $data['price'] = $this->price->amount($data['price'], $data['currency']);
        }

        $conditions = array('product_id' => $product_id);

        $updated = (int) $this->db->update('product', $data, $conditions);
        $updated += (int) $this->setSku($data);
        $updated += (int) $this->setTranslation($data);
        $updated += (int) $this->setImages($data);
        $updated += (int) $this->setAlias($data);
        $updated += (int) $this->setSkuCombinations($data);
        $updated += (int) $this->setOptions($data);
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

        return (bool) $this->db->insert('product_translation', $translation);
    }

    /**
     * Deletes and/or adds product translations
     * @param array $data
     * @param boolean $update
     * @return boolean
     */
    protected function setTranslation(array $data, $update = true)
    {
        if (empty($data['translation'])) {
            return false;
        }

        if ($update) {
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

        $sql = 'SELECT * FROM product WHERE product_id=?';
        $product = $this->db->fetch($sql, array($product_id));

        $this->attachFields($product);
        $this->attachSku($product);
        $this->attachImage($product, $language);
        $this->attachTranslation($product, $language);

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
        if (empty($product)) {
            return;
        }

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
        $sql = 'SELECT * FROM product_translation WHERE product_id=?';
        return $this->db->fetchAll($sql, array($product_id));
    }

    /**
     * Adds images to the product
     * @param array $product
     * @param null|string $language
     */
    protected function attachImage(array &$product, $language = null)
    {
        if (empty($product)) {
            return;
        }

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
        if (empty($product)) {
            return;
        }

        $product['field'] = $this->product_field->getList($product['product_id']);

        if (empty($product['field']['option'])) {
            return;
        }

        // Remove repeating field values
        foreach ($product['field']['option'] as &$field_values) {
            $field_values = array_unique($field_values);
        }
    }

    /**
     * Adds option combinations to the product
     * @param array $product
     */
    protected function attachSku(array &$product)
    {
        if (empty($product)) {
            return;
        }

        $skus = $this->sku->getList(array('product_id' => $product['product_id']));

        foreach ($skus as $sku) {

            if ($sku['combination_id'] !== '') {
                $product['combination'][$sku['combination_id']] = $sku;
                continue;
            }

            $product['sku'] = $sku['sku'];
            $product['price'] = $sku['price'];
            $product['stock'] = $sku['stock'];
        }
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
                . ' ps.sku, ps.price, ps.stock, ps.file_id'
                . ' FROM product p'
                . ' LEFT JOIN product_sku ps ON(p.product_id=ps.product_id)'
                . ' LEFT JOIN product_translation pt ON(p.product_id=pt.product_id AND pt.language=:language)'
                . ' WHERE ps.sku=:sku AND ps.store_id=:store_id';

        $conditions = array(
            'sku' => $sku,
            'language' => $language,
            'store_id' => $store_id
        );

        $product = $this->db->fetch($sql, $conditions);

        $this->attachImage($product);
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

        $deleted = (bool) $this->db->delete('product', $conditions);

        if ($deleted) {

            $this->db->delete('cart', $conditions);
            $this->db->delete('review', $conditions);
            $this->db->delete('rating', $conditions);
            $this->db->delete('wishlist', $conditions);
            $this->db->delete('product_sku', $conditions);
            $this->db->delete('rating_user', $conditions);
            $this->db->delete('product_field', $conditions);
            $this->db->delete('product_translation', $conditions);
            $this->db->delete('file', $conditions2);
            $this->db->delete('alias', $conditions2);
            $this->db->delete('search_index', $conditions2);

            $sql = 'DELETE ci'
                    . ' FROM collection_item ci'
                    . ' INNER JOIN collection c ON(ci.collection_id = c.collection_id)'
                    . ' WHERE c.type = ? AND ci.value = ?';

            $this->db->run($sql, array('product', $product_id));
        }

        $this->hook->fire('delete.product.after', $product_id, $deleted);
        return (bool) $deleted;
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

        $result = $this->db->fetchColumn($sql, array($product_id));
        return empty($result);
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

        foreach ($rules as $rule) {
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

        $list = $this->db->fetchColumnAll($sql, array($product_id));

        if (!empty($list) && $load) {
            $data['product_id'] = $list;
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

        $sql = 'SELECT p.*, a.alias, COALESCE(NULLIF(pt.title, ""), p.title) AS title,'
                . 'pt.language, ps.sku, ps.price, ps.stock, ps.file_id';

        if (!empty($data['count'])) {
            $sql = 'SELECT COUNT(p.product_id)';
        }

        $sql .= ' FROM product p'
                . ' LEFT JOIN product_translation pt'
                . ' ON(p.product_id = pt.product_id AND pt.language=?)'
                . ' LEFT JOIN alias a'
                . ' ON(a.id_key=? AND a.id_value=p.product_id)'
                . ' LEFT JOIN product_sku ps'
                . ' ON(p.product_id = ps.product_id AND LENGTH(ps.combination_id) = 0)';

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
            $sql .= ' AND ps.price = ?';
            $where[] = $this->price->amount((int) $data['price'], $data['currency']);
        }

        if (isset($data['currency'])) {
            $sql .= ' AND p.currency = ?';
            $where[] = $data['currency'];
        }

        if (isset($data['stock'])) {
            $sql .= ' AND ps.stock = ?';
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

        $allowed_sort = array(
            'title' => 'p.title', 'sku' => 'ps.sku', 'price' => 'ps.price',
            'currency' => 'p.currency', 'stock' => 'ps.stock',
            'status' => 'p.status', 'store_id' => 'p.store_id',
            'product_id' => 'p.product_id'
        );

        if (isset($data['sort']) && isset($allowed_sort[$data['sort']]) && isset($data['order']) && in_array($data['order'], $allowed_order)) {
            $sql .= " ORDER BY {$allowed_sort[$data['sort']]} {$data['order']}";
        } else {
            $sql .= " ORDER BY p.created DESC";
        }

        if (!empty($data['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $data['limit']));
        }

        if (!empty($data['count'])) {
            return (int) $this->db->fetchColumn($sql, $where);
        }

        $list = $this->db->fetchAll($sql, $where, array('index' => 'product_id'));

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

        array_unshift($existing, $product_id);
        $saved = array_unique($existing);

        $this->controlViewedLimit($saved, $limit);

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
        $this->controlViewedLimit($products, $limit);
        return $products;
    }

    /**
     * Reduces an array of recently viewed products
     * If the limit is set to X and > 0,
     * it removes all but first X items in the array
     * @param array $items
     * @param integer $limit
     * @return array
     */
    protected function controlViewedLimit(array &$items, $limit)
    {

        if (empty($limit)) {
            return $items;
        }

        $items = array_slice($items, 0, $limit + 1);
        return $items;
    }

    /**
     * Deletes and/or adds a new base SKU
     * @param array $data
     * @param boolean $update
     * @return bool
     */
    protected function setSku(array &$data, $update = true)
    {
        if (empty($data['sku']) || !isset($data['store_id'])) {
            return false;
        }

        if ($update) {
            $this->sku->delete($data['product_id'], array('base' => true));
        }

        if (!$update && !empty($data['price'])) {
            $data['price'] = $this->price->amount($data['price'], $data['currency']);
        }

        if (!$update && empty($data['sku'])) {
            $data['sku'] = $this->createSku($data);
        }

        return (bool) $this->sku->add($data);
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
     * @param boolean $update
     * @return boolean
     */
    protected function setAlias(array $data, $update = true)
    {
        if (empty($data['alias'])) {
            return false;
        }

        if ($update) {
            $this->alias->delete('product_id', $data['product_id']);
        }

        return (bool) $this->alias->add('product_id', $data['product_id'], $data['alias']);
    }

    /**
     * Deletes and/or adds product combinations
     * @param array $data
     * @param boolean $update
     * @return boolean
     */
    protected function setSkuCombinations(array $data, $update = true)
    {
        if (empty($data['combination'])) {
            return false;
        }

        if (isset($update)) {
            $this->sku->delete($data['product_id'], array('combinations' => true));
        }

        foreach ($data['combination'] as $combination) {

            if (empty($combination['fields'])) {
                continue;
            }

            if (!$update && !empty($combination['price'])) {
                $combination['price'] = $this->price->amount($combination['price'], $data['currency']);
            }

            $sku = array(
                'price' => $combination['price'],
                'stock' => $combination['stock'],
                'product_id' => $data['product_id'],
                'file_id' => $combination['file_id'],
                'combination_id' => $this->sku->getCombinationId($combination['fields'], $data['product_id'])
            );

            if (isset($combination['path']) && isset($data['images'][$combination['path']])) {
                $sku['file_id'] = $data['images'][$combination['path']];
            }

            if (empty($combination['sku'])) {
                $pattern = $data['sku'] . '-' . crc32(uniqid('', true));
                $sku['sku'] = $this->sku->generate($pattern, array(), array('store_id' => $data['store_id']));
            } else {
                $sku['sku'] = $combination['sku'];
            }

            if (isset($data['store_id'])) {
                $sku['store_id'] = $data['store_id'];
                $this->sku->add($sku);
            }
        }

        return true;
    }

    /**
     * Deletes and/or adds product option fields
     * @param array $data
     * @param boolean $update
     * @return boolean
     */
    protected function setOptions(array $data, $update = true)
    {
        if (empty($data['combination'])) {
            return false;
        }

        if ($update) {
            $this->product_field->delete('option', $data['product_id']);
        }

        foreach ($data['combination'] as $combination) {

            if (empty($combination['fields'])) {
                continue;
            }

            foreach ($combination['fields'] as $field_id => $field_value_id) {

                $options = array(
                    'type' => 'option',
                    'field_id' => $field_id,
                    'product_id' => $data['product_id'],
                    'field_value_id' => $field_value_id
                );

                $this->product_field->add($options);
            }
        }
    }

    /**
     * Deletes and/or adds product attribute fields
     * @param array $data
     * @param boolean $update
     * @return boolean
     */
    protected function setAttributes(array $data, $update = true)
    {
        if (empty($data['field']['attribute'])) {
            return false;
        }

        $product_id = $data['product_id'];

        if ($update) {
            $this->product_field->delete('attribute', $product_id);
        }

        foreach ($data['field']['attribute'] as $field_id => $field_value_ids) {
            foreach ((array) $field_value_ids as $field_value_id) {

                $options = array(
                    'type' => 'attribute',
                    'field_id' => $field_id,
                    'product_id' => $product_id,
                    'field_value_id' => $field_value_id
                );

                $this->product_field->add($options);
            }
        }

        return true;
    }

    /**
     * Deletes product translation(s)
     * @param integer $product_id
     * @return boolean
     */
    protected function deleteTranslation($product_id)
    {
        $conditions = array('product_id' => $product_id);
        return (bool) $this->db->delete('product_translation', $conditions);
    }

}
