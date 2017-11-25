<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Config,
    gplcart\core\Hook,
    gplcart\core\Database;
use gplcart\core\models\Sku as SkuModel,
    gplcart\core\models\File as FileModel,
    gplcart\core\models\Alias as AliasModel,
    gplcart\core\models\Price as PriceModel,
    gplcart\core\models\Search as SearchModel,
    gplcart\core\models\Language as LanguageModel,
    gplcart\core\models\PriceRule as PriceRuleModel,
    gplcart\core\models\Translation as TranslationModel,
    gplcart\core\models\ProductField as ProductFieldModel,
    gplcart\core\models\ProductRelation as ProductRelationModel;
use gplcart\core\helpers\Request as RequestHelper;
use gplcart\core\traits\Image as ImageTrait,
    gplcart\core\traits\Alias as AliasTrait;

/**
 * Manages basic behaviors and data related to products
 */
class Product
{

    use ImageTrait,
        AliasTrait;

    /**
     * Database class instance
     * @var \gplcart\core\Database $db
     */
    protected $db;

    /**
     * Hook class instance
     * @var \gplcart\core\Hook $hook
     */
    protected $hook;

    /**
     * Config class instance
     * @var \gplcart\core\Config $config
     */
    protected $config;

    /**
     * Product field model instance
     * @var \gplcart\core\models\ProductField $product_field
     */
    protected $product_field;

    /**
     * Product relation model instance
     * @var \gplcart\core\models\ProductRelation $product_relation
     */
    protected $product_relation;

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
     * SKU model instance
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
     * Translation model instance
     * @var \gplcart\core\models\Translation $translation
     */
    protected $translation;

    /**
     * Request class instance
     * @var \gplcart\core\helpers\Request $request
     */
    protected $request;

    /**
     * @param Hook $hook
     * @param Database $db
     * @param Config $config
     * @param LanguageModel $language
     * @param AliasModel $alias
     * @param FileModel $file
     * @param PriceModel $price
     * @param PriceRuleModel $pricerule
     * @param SkuModel $sku
     * @param SearchModel $search
     * @param ProductFieldModel $product_field
     * @param ProductRelationModel $product_relation
     * @param TranslationModel $translation
     * @param RequestHelper $request
     */
    public function __construct(Hook $hook, Database $db, Config $config, LanguageModel $language,
            AliasModel $alias, FileModel $file, PriceModel $price, PriceRuleModel $pricerule,
            SkuModel $sku, SearchModel $search, ProductFieldModel $product_field,
            ProductRelationModel $product_relation, TranslationModel $translation,
            RequestHelper $request)
    {
        $this->db = $db;
        $this->hook = $hook;
        $this->config = $config;

        $this->sku = $sku;
        $this->file = $file;
        $this->alias = $alias;
        $this->price = $price;
        $this->search = $search;
        $this->request = $request;
        $this->language = $language;
        $this->pricerule = $pricerule;
        $this->translation = $translation;
        $this->product_field = $product_field;
        $this->product_relation = $product_relation;
    }

    /**
     * Adds a product
     * @param array $data
     * @return integer
     */
    public function add(array $data)
    {
        $result = null;
        $this->hook->attach('product.add.before', $data, $result, $this);

        if (isset($result)) {
            return (int) $result;
        }

        $data['created'] = $data['modified'] = GC_TIME;
        $data += array('currency' => $this->config->get('currency', 'USD'));

        $this->setPrice($data);

        $result = $data['product_id'] = $this->db->insert('product', $data);

        $this->setTranslations($data, $this->translation, 'product', false);
        $this->setImages($data, $this->file, 'product');

        $this->setSku($data, false);
        $this->setSkuCombinations($data, false);
        $this->setOptions($data, false);
        $this->setAttributes($data, false);
        $this->setAlias($data, $this->alias, 'product', false);
        $this->setRelated($data, false);

        $this->search->index('product', $data);
        $this->hook->attach('product.add.after', $data, $result, $this);
        return (int) $result;
    }

    /**
     * Updates a product
     * @param integer $product_id
     * @param array $data
     * @return boolean
     */
    public function update($product_id, array $data)
    {
        $result = null;
        $this->hook->attach('product.update.before', $product_id, $data, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        $data['modified'] = GC_TIME;
        $data['product_id'] = $product_id;

        $this->setPrice($data);

        $conditions = array('product_id' => $product_id);
        $updated = $this->db->update('product', $data, $conditions);

        $updated += (int) $this->setSku($data);
        $updated += (int) $this->setTranslations($data, $this->translation, 'product');
        $updated += (int) $this->setImages($data, $this->file, 'product');
        $updated += (int) $this->setAlias($data, $this->alias, 'product');
        $updated += (int) $this->setSkuCombinations($data);
        $updated += (int) $this->setOptions($data);
        $updated += (int) $this->setAttributes($data);
        $updated += (int) $this->setRelated($data);

        $result = $updated > 0;

        if ($result) {
            $this->search->index('product', $product_id);
        }

        $this->hook->attach('product.update.after', $product_id, $data, $result, $this);
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

        if ($update) {
            $this->product_relation->delete($data['product_id']);
        }

        if (!empty($data['related'])) {
            foreach ((array) $data['related'] as $related_product_id) {
                $this->product_relation->add($related_product_id, $data['product_id']);
            }
            return true;
        }

        return false;
    }

    /**
     * Generate a product SKU
     * @param array $data
     * @return string
     */
    public function generateSku(array $data)
    {
        $data += array('placeholders' => $this->sku->getPatternPlaceholders());
        return $this->sku->generate($this->sku->getPattern(), $data);
    }

    /**
     * Loads a product from the database
     * @param integer $product_id
     * @param array $options
     * @return array
     */
    public function get($product_id, array $options = array())
    {
        $result = &gplcart_static(gplcart_array_hash(array("product.get.$product_id" => $options)));

        if (isset($result)) {
            return $result;
        }

        $this->hook->attach('product.get.before', $product_id, $options, $result, $this);

        if (isset($result)) {
            return $result;
        }

        $options += array('language' => null);

        $list = $this->getList(array('product_id' => (int) $product_id));

        if (count($list) != 1) {
            return $result = array();
        }

        $result = reset($list);

        $this->attachFields($result);
        $this->attachSku($result);
        $this->attachImages($result, $this->file, $this->translation, 'product', $options['language']);
        $this->attachTranslations($result, $this->translation, 'product', $options['language']);

        $this->hook->attach('product.get.after', $product_id, $options, $result, $this);

        return $result;
    }

    /**
     * Returns a product by SKU
     * @param string $sku
     * @param integer $store_id
     * @param string|null $language
     * @return array
     */
    public function getBySku($sku, $store_id, $language = null)
    {
        $options = array(
            'sku' => (string) $sku,
            'language' => $language,
            'store_id' => (int) $store_id
        );

        $list = $this->getList($options);

        if (count($list) != 1) {
            return array();
        }

        $result = reset($list);
        $this->attachImages($result, $this->file, $this->translation, 'product', $language);
        return $result;
    }

    /**
     * Adds fields to the product
     * @param array $product
     * @return array
     */
    protected function attachFields(array &$product)
    {
        if (empty($product)) {
            return $product;
        }

        $product['field'] = $this->product_field->getList($product['product_id']);

        if (empty($product['field']['option'])) {
            return $product;
        }

        foreach ($product['field']['option'] as &$field_values) {
            $field_values = array_unique($field_values);
        }

        return $product;
    }

    /**
     * Adds option combinations to the product
     * @param array $product
     * @return array
     */
    protected function attachSku(array &$product)
    {
        if (empty($product)) {
            return $product;
        }

        $product['default_field_values'] = array();
        $codes = (array) $this->sku->getList(array('product_id' => $product['product_id']));

        foreach ($codes as $code) {

            if ($code['combination_id'] === '') {
                $product['sku'] = $code['sku'];
                $product['price'] = $code['price'];
                $product['stock'] = $code['stock'];
                continue;
            }

            $product['combination'][$code['combination_id']] = $code;

            if (!empty($code['is_default'])) {
                $product['default_field_values'] = $code['fields'];
            }
        }

        return $product;
    }

    /**
     * Deletes a product
     * @param integer $product_id
     * @param bool $check
     * @return boolean
     */
    public function delete($product_id, $check = true)
    {
        $result = null;
        $this->hook->attach('product.delete.before', $product_id, $check, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        if ($check && !$this->canDelete($product_id)) {
            return false;
        }

        $conditions = array('product_id' => $product_id);
        $conditions2 = array('id_key' => 'product_id', 'id_value' => $product_id);
        $conditions3 = array('item_product_id' => $product_id);

        $result = (bool) $this->db->delete('product', $conditions);

        if ($result) {

            $this->db->delete('cart', $conditions);
            $this->db->delete('review', $conditions);
            $this->db->delete('rating', $conditions);
            $this->db->delete('wishlist', $conditions);
            $this->db->delete('rating_user', $conditions);

            $this->db->delete('product_sku', $conditions);
            $this->db->delete('product_field', $conditions);
            $this->db->delete('product_translation', $conditions);
            $this->db->delete('product_view', $conditions);

            $this->db->delete('product_related', $conditions);
            $this->db->delete('product_related', $conditions3);

            $this->db->delete('product_bundle', $conditions);
            $this->db->delete('product_bundle', $conditions3);

            $this->db->delete('file', $conditions2);
            $this->db->delete('alias', $conditions2);
            $this->db->delete('search_index', $conditions2);

            $sql = 'DELETE ci'
                    . ' FROM collection_item ci'
                    . ' INNER JOIN collection c ON(ci.collection_id = c.collection_id)'
                    . ' WHERE c.type = ? AND ci.value = ?';

            $this->db->run($sql, array('product', $product_id));
        }

        $this->hook->attach('product.delete.after', $product_id, $check, $result, $this);
        return (bool) $result;
    }

    /**
     * Whether the product can be deleted
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
     * Calculates the product price
     * @param array $product
     * @return array
     */
    public function calculate(array $product)
    {
        return $this->pricerule->calculate($product['price'], $product);
    }

    /**
     * Returns an array of weight measurement units
     * @return array
     */
    public function getWeightUnits()
    {
        return array(
            'g' => $this->language->text('Gram'),
            'kg' => $this->language->text('Kilogram'),
            'lb' => $this->language->text('Pound'),
            'oz' => $this->language->text('Ounce'),
        );
    }

    /**
     * Returns an array of size measurement units
     * @return array
     */
    public function getSizeUnits()
    {
        return array(
            'in' => $this->language->text('Inch'),
            'mm' => $this->language->text('Millimeter'),
            'cm' => $this->language->text('Centimetre')
        );
    }

    /**
     * Returns an array of related product IDs
     * @param array $options
     * @return array
     */
    public function getRelated(array $options)
    {
        return (array) $this->product_relation->getList($options);
    }

    /**
     * Returns an array of products or counts them
     * @param array $data
     * @return array|integer
     */
    public function getList(array $data = array())
    {
        $sql = 'SELECT p.*, a.alias, COALESCE(NULLIF(pt.title, ""), p.title) AS title,'
                . 'pt.language, ps.sku, ps.price, ps.stock, ps.file_id, u.role_id,'
                . 'GROUP_CONCAT(pb.item_sku) AS bundle';

        if (!empty($data['count'])) {
            $sql = 'SELECT COUNT(p.product_id)';
        }

        $sql .= ' FROM product p'
                . ' LEFT JOIN product_translation pt ON(p.product_id = pt.product_id AND pt.language=?)'
                . ' LEFT JOIN alias a ON(a.id_key=? AND a.id_value=p.product_id)'
                . ' LEFT JOIN user u ON(u.user_id=p.user_id)'
                . ' LEFT JOIN product_sku ps ON(p.product_id = ps.product_id';

        if (!isset($data['sku'])) {
            $sql .= ' AND LENGTH(ps.combination_id) = 0';
        }

        $sql .= ')';
        $sql .= ' LEFT JOIN product_bundle pb ON(p.product_id = pb.product_id)';


        $language = $this->language->getLangcode();
        $conditions = array($language, 'product_id');

        if (!empty($data['product_id'])) {
            settype($data['product_id'], 'array');
            $placeholders = rtrim(str_repeat('?,', count($data['product_id'])), ',');
            $sql .= " WHERE p.product_id IN($placeholders)";
            $conditions = array_merge($conditions, $data['product_id']);
        } else {
            $sql .= ' WHERE p.product_id IS NOT NULL';
        }

        if (isset($data['title'])) {
            $sql .= ' AND (p.title LIKE ? OR (pt.title LIKE ? AND pt.language=?))';
            $conditions[] = "%{$data['title']}%";
            $conditions[] = "%{$data['title']}%";
            $conditions[] = $language;
        }

        if (isset($data['language'])) {
            $sql .= ' AND pt.language = ?';
            $conditions[] = $data['language'];
        }

        if (isset($data['sku'])) {
            settype($data['sku'], 'array');
            $placeholders = rtrim(str_repeat('?,', count($data['sku'])), ',');
            $sql .= " AND ps.sku IN($placeholders)";
            $conditions = array_merge($conditions, $data['sku']);
        }

        if (isset($data['sku_like'])) {
            $sql .= ' AND ps.sku LIKE ?';
            $conditions[] = "%{$data['sku_like']}%";
        }

        if (isset($data['price']) && isset($data['currency'])) {
            $sql .= ' AND ps.price = ?';
            $conditions[] = $this->price->amount((int) $data['price'], $data['currency']);
        }

        if (isset($data['currency'])) {
            $sql .= ' AND p.currency = ?';
            $conditions[] = $data['currency'];
        }

        if (isset($data['stock'])) {
            $sql .= ' AND ps.stock = ?';
            $conditions[] = (int) $data['stock'];
        }

        if (isset($data['category_id'])) {
            $sql .= ' AND p.category_id = ?';
            $conditions[] = (int) $data['category_id'];
        }

        if (isset($data['status'])) {
            $sql .= ' AND p.status = ?';
            $conditions[] = (int) $data['status'];
        }

        if (isset($data['store_id'])) {
            $sql .= ' AND p.store_id = ?';
            $conditions[] = (int) $data['store_id'];
        }

        if (empty($data['count'])) {
            // Additional group by to prevent errors wnen sql_mode=only_full_group_by
            $sql .= ' GROUP BY p.product_id, a.alias, pt.title, ps.sku, ps.price, ps.stock, ps.file_id';
        }

        $allowed_order = array('asc', 'desc');

        $allowed_sort = array(
            'title' => 'p.title', 'sku' => 'ps.sku', 'sku_like' => 'ps.sku', 'price' => 'ps.price',
            'currency' => 'p.currency', 'stock' => 'ps.stock',
            'status' => 'p.status', 'store_id' => 'p.store_id',
            'product_id' => 'p.product_id'
        );

        if (isset($data['sort']) && isset($allowed_sort[$data['sort']])//
                && isset($data['order']) && in_array($data['order'], $allowed_order)) {
            $sql .= " ORDER BY {$allowed_sort[$data['sort']]} {$data['order']}";
        } else {
            $sql .= " ORDER BY p.modified DESC";
        }

        if (!empty($data['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $data['limit']));
        }

        if (!empty($data['count'])) {
            return (int) $this->db->fetchColumn($sql, $conditions);
        }

        $list = $this->db->fetchAll($sql, $conditions, array('index' => 'product_id'));
        $this->hook->attach('product.list', $list, $this);
        return $list;
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
            $data['sku'] = $this->generateSku($data);
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

        return $this->addSkuCombinations($data);
    }

    /**
     * Add SKUs for an array of product combinations
     * @param array $data
     * @return boolean
     */
    protected function addSkuCombinations(array $data)
    {
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

            $combination['store_id'] = $data['store_id'];
            $combination['product_id'] = $data['product_id'];
            $combination['combination_id'] = $this->sku->getCombinationId($combination['fields'], $data['product_id']);

            if (empty($combination['sku'])) {
                $combination['sku'] = $this->sku->generate($data['sku'], array('store_id' => $data['store_id']));
            }

            $this->sku->add($combination);
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

        return $this->addOptions($data);
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

        return $this->addAttributes($data);
    }

    /**
     * Adds multiple options
     * @param array $data
     * @return boolean
     */
    protected function addOptions(array $data)
    {
        if (empty($data['combination'])) {
            return false;
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

        return true;
    }

    /**
     * Adds multiple attributes
     * @param array $data
     * @return boolean
     */
    protected function addAttributes(array $data)
    {
        if (empty($data['field']['attribute'])) {
            return false;
        }

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

        return true;
    }

    /**
     * Returns a relative/absolute path for uploaded images
     * @param boolean $absolute
     * @return string
     */
    public function getImagePath($absolute = false)
    {
        $dirname = $this->config->get('product_image_dirname', 'product');

        if ($absolute) {
            return gplcart_path_absolute($dirname, GC_DIR_IMAGE);
        }

        return gplcart_path_relative(GC_DIR_IMAGE, GC_DIR_FILE) . "/$dirname";
    }

}
