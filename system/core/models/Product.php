<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Config;
use gplcart\core\Hook;
use gplcart\core\interfaces\Crud as CrudInterface;
use gplcart\core\models\Alias as AliasModel;
use gplcart\core\models\File as FileModel;
use gplcart\core\models\Price as PriceModel;
use gplcart\core\models\PriceRule as PriceRuleModel;
use gplcart\core\models\ProductField as ProductFieldModel;
use gplcart\core\models\ProductRelation as ProductRelationModel;
use gplcart\core\models\Search as SearchModel;
use gplcart\core\models\Sku as SkuModel;
use gplcart\core\models\Translation as TranslationModel;
use gplcart\core\models\TranslationEntity as TranslationEntityModel;
use gplcart\core\traits\Alias as AliasTrait;
use gplcart\core\traits\Image as ImageTrait;
use gplcart\core\traits\Translation as TranslationTrait;

/**
 * Manages basic behaviors and data related to products
 */
class Product implements CrudInterface
{

    use ImageTrait,
        AliasTrait,
        TranslationTrait;

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
     * Price rule model instance
     * @var \gplcart\core\models\PriceRule $pricerule
     */
    protected $price_rule;

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
     * Translation UI model instance
     * @var \gplcart\core\models\Translation $translation
     */
    protected $translation;

    /**
     * Translation entity model instance
     * @var \gplcart\core\models\TranslationEntity $translation_entity
     */
    protected $translation_entity;

    /**
     * @param Hook $hook
     * @param Config $config
     * @param SkuModel $sku
     * @param FileModel $file
     * @param AliasModel $alias
     * @param PriceModel $price
     * @param SearchModel $search
     * @param PriceRuleModel $pricerule
     * @param ProductFieldModel $product_field
     * @param TranslationModel $translation
     * @param TranslationEntityModel $translation_entity
     * @param ProductRelationModel $product_relation
     */
    public function __construct(Hook $hook, Config $config, SkuModel $sku, FileModel $file,
                                AliasModel $alias, PriceModel $price, SearchModel $search, PriceRuleModel $pricerule,
                                ProductFieldModel $product_field, TranslationModel $translation,
                                TranslationEntityModel $translation_entity, ProductRelationModel $product_relation)
    {
        $this->hook = $hook;
        $this->config = $config;
        $this->db = $this->config->getDb();

        $this->sku = $sku;
        $this->file = $file;
        $this->alias = $alias;
        $this->price = $price;
        $this->search = $search;
        $this->price_rule = $pricerule;
        $this->translation = $translation;
        $this->product_field = $product_field;
        $this->product_relation = $product_relation;
        $this->translation_entity = $translation_entity;
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

        $this->setTranslations($data, $this->translation_entity, 'product', false);
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

        $updated = $this->db->update('product', $data, array('product_id' => $product_id));

        $updated += (int) $this->setSku($data);
        $updated += (int) $this->setTranslations($data, $this->translation_entity, 'product');
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
     * @param array|int
     * @return array
     */
    public function get($condition)
    {
        if (!is_array($condition)) {
            $condition = array('product_id' => $condition);
        }

        $result = &gplcart_static(gplcart_array_hash(array('product.get' => $condition)));

        if (isset($result)) {
            return $result;
        }

        $this->hook->attach('product.get.before', $condition, $result, $this);

        if (isset($result)) {
            return $result;
        }

        $condition['limit'] = array(0, 1);
        $list = (array) $this->getList($condition);
        $result = empty($list) ? array() : reset($list);

        $this->attachFields($result);
        $this->attachSku($result);

        $this->hook->attach('product.get.after', $condition, $result, $this);
        return $result;
    }

    /**
     * Returns a product by SKU
     * @param string $sku
     * @param integer $store_id
     * @return array
     */
    public function getBySku($sku, $store_id)
    {
        $options = array(
            'sku' => $sku,
            'store_id' => $store_id
        );

        return $this->get($options);
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

        $result = (bool) $this->db->delete('product', array('product_id' => $product_id));

        if ($result) {
            $this->deleteLinked($product_id);
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
        $sql = 'SELECT NOT EXISTS (SELECT cart_id FROM cart WHERE product_id=:id AND order_id > 0)
                AND NOT EXISTS (SELECT product_bundle_id FROM product_bundle WHERE item_product_id=:id)';

        return (bool) $this->db->fetchColumn($sql, array('id' => $product_id));
    }

    /**
     * Calculates the product price
     * @param array $product
     * @param array $options
     * @return int
     */
    public function calculate(array $product, array $options = array())
    {
        $options += array(
            'status' => 1,
            'store_id' => $product['store_id']
        );

        $components = array();
        $total = $product['price'];

        foreach ($this->price_rule->getTriggered($product, $options) as $price_rule) {
            $this->price_rule->calculate($total, $product, $components, $price_rule);
        }

        return $total;
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
     * @param array $options
     * @return array|integer
     */
    public function getList(array $options = array())
    {
        $options += array('language' => $this->translation->getLangcode());

        $result = null;
        $this->hook->attach('product.list.before', $options, $result, $this);

        if (isset($result)) {
            return $result;
        }

        $sql = 'SELECT p.*,
                a.alias,
                COALESCE(NULLIF(pt.title, ""), p.title) AS title,
                COALESCE(NULLIF(pt.description, ""), p.description) AS description,
                COALESCE(NULLIF(pt.meta_title, ""), p.meta_title) AS meta_title,
                COALESCE(NULLIF(pt.meta_description, ""), p.meta_description) AS meta_description,
                pt.language,
                ps.sku, ps.price, ps.stock, ps.file_id,
                u.role_id,
                GROUP_CONCAT(pb.item_product_id) AS bundle';

        if (!empty($options['count'])) {
            $sql = 'SELECT COUNT(p.product_id)';
        }

        $sql .= ' FROM product p
                  LEFT JOIN product_translation pt ON(p.product_id = pt.product_id AND pt.language=?)
                  LEFT JOIN alias a ON(a.entity=? AND a.entity_id=p.product_id)
                  LEFT JOIN user u ON(u.user_id=p.user_id)
                  LEFT JOIN product_sku ps ON(p.product_id = ps.product_id';

        if (!isset($options['sku'])) {
            $sql .= ' AND LENGTH(ps.combination_id) = 0';
        }

        $sql .= ')';
        $sql .= ' LEFT JOIN product_bundle pb ON(p.product_id = pb.product_id)';

        $conditions = array($options['language'], 'product');

        if (!empty($options['product_id'])) {
            settype($options['product_id'], 'array');
            $placeholders = rtrim(str_repeat('?,', count($options['product_id'])), ',');
            $sql .= " WHERE p.product_id IN($placeholders)";
            $conditions = array_merge($conditions, $options['product_id']);
        } else {
            $sql .= ' WHERE p.product_id IS NOT NULL';
        }

        if (isset($options['title'])) {
            $sql .= ' AND (p.title LIKE ? OR (pt.title LIKE ? AND pt.language=?))';
            $conditions[] = "%{$options['title']}%";
            $conditions[] = "%{$options['title']}%";
            $conditions[] = $options['language'];
        }

        if (isset($options['sku'])) {
            settype($options['sku'], 'array');
            $placeholders = rtrim(str_repeat('?,', count($options['sku'])), ',');
            $sql .= " AND ps.sku IN($placeholders)";
            $conditions = array_merge($conditions, $options['sku']);
        }

        if (isset($options['sku_like'])) {
            $sql .= ' AND ps.sku LIKE ?';
            $conditions[] = "%{$options['sku_like']}%";
        }

        if (isset($options['price']) && isset($options['currency'])) {
            $sql .= ' AND ps.price = ?';
            $conditions[] = $this->price->amount($options['price'], $options['currency']);
        }

        if (isset($options['currency'])) {
            $sql .= ' AND p.currency = ?';
            $conditions[] = $options['currency'];
        }

        if (isset($options['stock'])) {
            $sql .= ' AND ps.stock = ?';
            $conditions[] = $options['stock'];
        }

        if (isset($options['category_id'])) {
            $sql .= ' AND p.category_id = ?';
            $conditions[] = $options['category_id'];
        }

        if (isset($options['status'])) {
            $sql .= ' AND p.status = ?';
            $conditions[] = (int) $options['status'];
        }

        if (isset($options['store_id'])) {
            $sql .= ' AND p.store_id = ?';
            $conditions[] = $options['store_id'];
        }

        if (empty($options['count'])) {
            // Prevent errors wnen sql_mode=only_full_group_by (PHP 7)
            $sql .= ' GROUP BY p.product_id, a.alias, pt.title, ps.sku, ps.price, ps.stock, ps.file_id';
        }

        $allowed_order = array('asc', 'desc');

        $allowed_sort = array(
            'title' => 'p.title', 'sku' => 'ps.sku', 'sku_like' => 'ps.sku', 'price' => 'ps.price',
            'currency' => 'p.currency', 'stock' => 'ps.stock', 'status' => 'p.status', 'store_id' => 'p.store_id',
            'product_id' => 'p.product_id'
        );

        if (isset($options['sort'])
            && isset($allowed_sort[$options['sort']])
            && isset($options['order'])
            && in_array($options['order'], $allowed_order)) {
            $sql .= " ORDER BY {$allowed_sort[$options['sort']]} {$options['order']}";
        } else {
            $sql .= " ORDER BY p.modified DESC";
        }

        if (!empty($options['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $options['limit']));
        }

        if (empty($options['count'])) {
            $result = $this->db->fetchAll($sql, $conditions, array('index' => 'product_id'));
        } else {
            $result = (int) $this->db->fetchColumn($sql, $conditions);
        }

        $this->hook->attach('product.list.after', $options, $result, $this);
        return $result;
    }

    /**
     * Returns an array of weight measurement units
     * @return array
     */
    public function getWeightUnits()
    {
        return array(
            'g' => $this->translation->text('Gram'),
            'kg' => $this->translation->text('Kilogram'),
            'lb' => $this->translation->text('Pound'),
            'oz' => $this->translation->text('Ounce'),
        );
    }

    /**
     * Returns an array of size measurement units
     * @return array
     */
    public function getSizeUnits()
    {
        return array(
            'in' => $this->translation->text('Inch'),
            'mm' => $this->translation->text('Millimeter'),
            'cm' => $this->translation->text('Centimetre')
        );
    }

    /**
     * Delete all database records associated with the product ID
     * @param int $product_id
     */
    protected function deleteLinked($product_id)
    {
        $conditions = array('product_id' => $product_id);
        $conditions2 = array('entity' => 'product', 'entity_id' => $product_id);
        $conditions3 = array('item_product_id' => $product_id);

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

        $this->db->delete('file', $conditions2);
        $this->db->delete('alias', $conditions2);
        $this->db->delete('search_index', $conditions2);

        $sql = 'DELETE ci
                FROM collection_item ci
                INNER JOIN collection c ON(ci.collection_id = c.collection_id)
                WHERE c.type = ? AND ci.value = ?';

        $this->db->run($sql, array('product', $product_id));
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
            $this->product_field->delete(array('type' => 'option', 'product_id' => $data['product_id']));
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
            $this->product_field->delete(array('type' => 'attribute', 'product_id' => $data['product_id']));
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

}
