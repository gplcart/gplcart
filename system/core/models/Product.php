<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Model;
use gplcart\core\Cache;
use gplcart\core\helpers\Request as RequestHelper;
use gplcart\core\models\Sku as SkuModel;
use gplcart\core\models\File as FileModel;
use gplcart\core\models\Alias as AliasModel;
use gplcart\core\models\Price as PriceModel;
use gplcart\core\models\Search as SearchModel;
use gplcart\core\models\Language as LanguageModel;
use gplcart\core\models\PriceRule as PriceRuleModel;
use gplcart\core\models\ProductField as ProductFieldModel;

/**
 * Manages basic behaviors and data related to products
 */
class Product extends Model
{

    use \gplcart\core\traits\EntityImage,
        \gplcart\core\traits\EntityAlias,
        \gplcart\core\traits\EntityTranslation;

    /**
     * Cache instance
     * @var \gplcart\core\Cache $cache
     */
    protected $cache;

    /**
     * Product field model instance
     * @var \gplcart\core\models\ProductField $product_field
     */
    protected $product_field;

    /**
     * Price model instance
     * @var \gplcart\core\models\Price $price
     */
    protected $price;

    /**
     * Language model instance
     * @var \gplcart\core\models\Language $language
     */
    protected $language;

    /**
     * Price rule model instance
     * @var \gplcart\core\models\PriceRule $pricerule
     */
    protected $pricerule;

    /**
     * Sku model instance
     * @var \gplcart\core\models\Sku $sku
     */
    protected $sku;

    /**
     * Search module instance
     * @var \gplcart\core\models\Search $search
     */
    protected $search;

    /**
     * Alias model instance
     * @var \gplcart\core\models\Alias $alias
     */
    protected $alias;

    /**
     * File model instance
     * @var \gplcart\core\models\File $file
     */
    protected $file;

    /**
     * Request class instance
     * @var \gplcart\core\helpers\Request $request
     */
    protected $request;

    /**
     * Constructor
     * @param AliasModel $alias
     * @param FileModel $file
     * @param PriceModel $price
     * @param PriceRuleModel $pricerule
     * @param LanguageModel $language
     * @param SkuModel $sku
     * @param SearchModel $search
     * @param ProductFieldModel $product_field
     * @param Cache $cache
     * @param RequestHelper $request
     */
    public function __construct(AliasModel $alias, FileModel $file,
            PriceModel $price, PriceRuleModel $pricerule,
            LanguageModel $language, SkuModel $sku, SearchModel $search,
            ProductFieldModel $product_field, Cache $cache,
            RequestHelper $request)
    {
        parent::__construct();

        $this->sku = $sku;
        $this->cache = $cache;
        $this->file = $file;
        $this->alias = $alias;
        $this->price = $price;
        $this->search = $search;
        $this->request = $request;
        $this->language = $language;
        $this->pricerule = $pricerule;
        $this->product_field = $product_field;
    }

    /**
     * Adds a product to the database
     * @param array $data
     * @return boolean|integer
     */
    public function add(array $data)
    {
        $this->hook->fire('add.product.before', $data);

        if (empty($data)) {
            return false;
        }

        $data['created'] = GC_TIME;
        $data += array('currency' => $this->config->get('currency', 'USD'));

        $this->setPrice($data);

        $data['product_id'] = $this->db->insert('product', $data);

        $this->setTranslation($this->db, $data, 'product', false);
        $this->setImages($this->file, $data, 'product');

        $this->setSku($data, false);
        $this->setSkuCombinations($data, false);
        $this->setOptions($data, false);
        $this->setAttributes($data, false);
        $this->setAliasTrait($this->alias, $data, 'product', false);
        $this->setRelated($data, false);

        $this->search->index('product', $data);

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

        $this->setPrice($data);

        $conditions = array('product_id' => $product_id);
        $updated = $this->db->update('product', $data, $conditions);

        $updated += (int) $this->setSku($data);
        $updated += (int) $this->setTranslation($this->db, $data, 'product');
        $updated += (int) $this->setImages($this->file, $data, 'product');
        $updated += (int) $this->setAliasTrait($this->alias, $data, 'product');
        $updated += (int) $this->setSkuCombinations($data);
        $updated += (int) $this->setOptions($data);
        $updated += (int) $this->setAttributes($data);
        $updated += (int) $this->setRelated($data);

        $result = false;

        if ($updated > 0) {
            $result = true;
            $this->search->index('product', $product_id);
            $this->cache->clear("product.$product_id.", array('pattern' => '*'));
        }

        $this->hook->fire('update.product.after', $product_id, $data, $result);
        return (bool) $result;
    }

    /**
     * Converts a price to minor units
     * @param array $data
     */
    protected function setPrice(array &$data)
    {
        if (!empty($data['price']) && !empty($data['currency'])) {
            $data['price'] = $this->price->amount($data['price'], $data['currency']);
        }
    }

    /**
     * Deletes and/or adds related products
     * @param array $data
     * @param boolean $update
     * @return boolean
     */
    public function setRelated(array $data, $update = true)
    {
        if (empty($data['form']) && empty($data['related'])) {
            return false;
        }

        $product_id = $data['product_id'];

        if ($update) {
            $this->db->delete('product_related', array('product_id' => $product_id));
            $this->db->delete('product_related', array('related_product_id' => $product_id));
        }

        if (empty($data['related'])) {
            return false;
        }

        foreach ((array) $data['related'] as $id) {
            $this->db->insert('product_related', array('product_id' => $product_id, 'related_product_id' => $id));
            $this->db->insert('product_related', array('product_id' => $id, 'related_product_id' => $product_id));
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
     * Loads a product from the database
     * @param integer $product_id
     * @param array $options
     * @return array
     */
    public function get($product_id, array $options = array())
    {
        $this->hook->fire('get.product.before', $product_id, $options);

        if (empty($product_id) && empty($options)) {
            return array();
        }

        $conditions = array();

        $sql = 'SELECT p.*, u.role_id, ps.sku, ps.price, ps.stock, ps.file_id'
                . ' FROM product p'
                . ' LEFT JOIN product_sku ps ON(p.product_id=ps.product_id)'
                . ' LEFT JOIN user u ON(p.user_id=u.user_id)';

        if (isset($options['sku'])) {
            $sql .= ' WHERE ps.sku = ?';
            $conditions[] = $options['sku'];
        } else {
            $sql .= ' WHERE p.product_id = ?';
            $conditions[] = $product_id;
        }

        if (!empty($options['store_id'])) {
            $sql .= ' AND p.store_id = ?';
            $conditions[] = $options['store_id'];
        }

        if (!isset($options['language'])) {
            $options['language'] = null;
        }

        $product = $this->db->fetch($sql, $conditions);

        $this->attachFields($product);
        $this->attachSku($product);
        $this->attachImages($this->file, $product, 'product', $options['language']);
        $this->attachTranslation($this->db, $product, 'product', $options['language']);

        $this->hook->fire('get.product.after', $product_id, $options, $product);

        return $product;
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
        $conditions = array(
            'sku' => $sku,
            'language' => $language,
            'store_id' => $store_id
        );

        return $this->get(null, $conditions);
    }

    /**
     * Adds fields to the product
     * @param array $product
     * @return null
     */
    protected function attachFields(array &$product)
    {
        if (empty($product)) {
            return null;
        }

        $product['field'] = $this->product_field->getList($product['product_id']);

        if (empty($product['field']['option'])) {
            return null;
        }

        // Remove repeating field values
        foreach ($product['field']['option'] as &$field_values) {
            $field_values = array_unique($field_values);
        }
    }

    /**
     * Adds option combinations to the product
     * @param array $product
     * @return null
     */
    protected function attachSku(array &$product)
    {
        if (empty($product)) {
            return null;
        }

        $skus = (array) $this->sku->getList(array('product_id' => $product['product_id']));

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
                . ' FROM cart'
                . ' WHERE product_id=? AND order_id > 0';

        $result = $this->db->fetchColumn($sql, array($product_id));
        return empty($result);
    }

    /**
     * Calculates product price
     * @param array $product
     * @param integer $store_id
     * @return array
     * @todo check this method
     */
    public function calculate(array $product, $store_id = 1)
    {
        //@todo check method, options and data arrays
        $options = array('store_id' => $store_id, 'value_type' => 'product');
        $rules = $this->pricerule->getTriggered($options, $product);

        $components = array();
        $price = $product['price'];
        $currency_code = $product['currency'];

        foreach ($rules as $rule) {

            //$this->pricerule->calculate($total, $cart, $data, $components);
        }

        $result = array(
            'total' => $price,
            'currency' => $currency_code,
            'components' => $components
        );

        return $result;
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

        return (array) $list;
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

        if (isset($data['sort']) && isset($allowed_sort[$data['sort']])//
                && isset($data['order']) && in_array($data['order'], $allowed_order)) {
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

        $this->request->setCookie('viewed_products', implode('|', $saved), $lifespan);
        return $saved;
    }

    /**
     * Returns an array of recently viewed product IDs
     * @param integer|null $limit
     * @return array
     */
    public function getViewed($limit = null)
    {
        $cookie = $this->request->cookie('viewed_products', '');
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
        if (empty($data['form']) && empty($data['sku'])) {
            return false;
        }

        if ($update) {
            $this->sku->delete($data['product_id'], array('base' => true));
            return (bool) $this->sku->add($data);
        }

        if (empty($data['sku'])) {
            $data['sku'] = $this->createSku($data);
        }

        return (bool) $this->sku->add($data);
    }

    /**
     * Deletes and/or adds product combinations
     * @param array $data
     * @param boolean $update
     * @return boolean
     */
    protected function setSkuCombinations(array $data, $update = true)
    {
        if (empty($data['form']) && empty($data['combination'])) {
            return false;
        }

        if ($update) {
            $this->sku->delete($data['product_id'], array('combinations' => true));
        }

        if (empty($data['combination'])) {
            return false;
        }

        foreach ($data['combination'] as $combination) {

            if (empty($combination['fields'])) {
                continue;
            }

            if (!empty($combination['price'])) {
                $combination['price'] = $this->price->amount($combination['price'], $data['currency']);
            }

            $this->setCombinationFileId($combination, $data);

            $sku = array(
                'sku' => $combination['sku'],
                'store_id' => $data['store_id'],
                'price' => $combination['price'],
                'stock' => $combination['stock'],
                'product_id' => $data['product_id'],
                'file_id' => $combination['file_id'],
                'is_default' => !empty($combination['is_default']),
                'combination_id' => $this->sku->getCombinationId($combination['fields'], $data['product_id'])
            );

            if (empty($sku['sku'])) {
                $sku['sku'] = $this->sku->generate($data['sku'], array(), array('store_id' => $data['store_id']));
            }

            $this->sku->add($sku);
        }

        return true;
    }

    /**
     * Adds a file ID from uploaded images to the combination item
     * @param array $combination
     * @param array $data
     * @return array
     */
    protected function setCombinationFileId(array &$combination, array $data)
    {
        foreach ($data['images'] as $image) {
            if ($image['path'] === $combination['path']) {
                $combination['file_id'] = $image['file_id'];
            }
        }

        return $combination;
    }

    /**
     * Deletes and/or adds product option fields
     * @param array $data
     * @param boolean $update
     * @return boolean
     */
    protected function setOptions(array $data, $update = true)
    {
        if (empty($data['form']) && empty($data['combination'])) {
            return false;
        }

        if ($update) {
            $this->product_field->delete('option', $data['product_id']);
        }

        if (empty($data['combination'])) {
            return false;
        }

        $this->addOptions($data);
        return true;
    }

    /**
     * Adds multiple options
     * @param array $data
     */
    protected function addOptions(array $data)
    {
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
        if (empty($data['form']) && empty($data['field']['attribute'])) {
            return false;
        }

        if ($update) {
            $this->product_field->delete('attribute', $data['product_id']);
        }

        if (empty($data['field']['attribute'])) {
            return false;
        }

        $this->addAttributes($data);
        return true;
    }

    /**
     * Adds multiple attributes
     * @param array $data
     */
    protected function addAttributes(array $data)
    {
        foreach ($data['field']['attribute'] as $field_id => $field_value_ids) {
            foreach ((array) $field_value_ids as $field_value_id) {
                $options = array(
                    'type' => 'attribute',
                    'field_id' => $field_id,
                    'product_id' => $data['product_id'],
                    'field_value_id' => $field_value_id
                );

                $this->product_field->add($options);
            }
        }
    }

}
