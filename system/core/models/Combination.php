<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\models;

use core\Model;
use core\models\Price as ModelsPrice;
use core\models\Language as ModelsLanguage;

/**
 * Manages basic behaviors and data related to product option combinations
 */
class Combination extends Model
{

    /**
     * Language model instance
     * @var \core\models\Language $language
     */
    protected $language;

    /**
     * Price model instance
     * @var \core\models\Price $price
     */
    protected $price;

    /**
     * Constructor
     * @param ModelsLanguage $language
     * @param ModelsPrice $price
     */
    public function __construct(ModelsLanguage $language, ModelsPrice $price)
    {
        parent::__construct();

        $this->price = $price;
        $this->language = $language;
    }

    /**
     * Deletes option combination(s) by product ID
     * @param integer $product_id
     * @return boolean
     */
    protected function delete($product_id)
    {
        $conditions = array('product_id' => $product_id);
        $this->db->delete('option_combination', $conditions);
        return true;
    }

    /**
     * Adds a field combination
     * @param array $data
     * @return boolean|string
     */
    public function add(array $data)
    {
        $this->hook->fire('add.option.combination.before', $data);

        if (empty($data)) {
            return false;
        }

        $fields = empty($data['fields']) ? array() : (array) $data['fields'];
        $data['combination_id'] = $this->id($fields, $data['product_id']);

        if (!empty($data['price'])) {
            $data['price'] = $this->price->amount($data['price'], $data['currency']);
        }

        $this->db->insert('option_combination', $data);
        $this->hook->fire('add.option.combination.after', $data);

        return $data['combination_id'];
    }

    /**
     * Returns an array of option combinations for a given product
     * @param integer $product_id
     * @return array
     */
    public function getList($product_id)
    {
        $sql = 'SELECT oc.*, ps.sku'
                . ' FROM option_combination oc'
                . ' LEFT JOIN product_sku ps'
                . ' ON(oc.product_id = ps.product_id AND ps.combination_id = oc.combination_id)'
                . ' WHERE oc.product_id=?';

        $results = $this->db->fetchAll($sql, array($product_id));

        $combinations = array();
        foreach ($results as $combination) {
            $combinations[$combination['combination_id']] = $combination;
            $fields = $this->getFieldValues($combination['combination_id']);
            $combinations[$combination['combination_id']]['fields'] = $fields;
        }

        return $combinations;
    }

    /**
     * Returns an array of field value Ids from a combination ID
     * @param string $combination_id
     * @return array
     */
    public function getFieldValues($combination_id)
    {
        $field_value_ids = explode('_', substr($combination_id, strpos($combination_id, '-') + 1));
        sort($field_value_ids);

        return $field_value_ids;
    }

    /**
     * Creates a field combination id from the field value ids
     * @param array $field_value_ids
     * @param null|integer $product_id
     * @return string
     */
    public function id(array $field_value_ids, $product_id = null)
    {
        sort($field_value_ids);

        if (!empty($product_id)) {
            return $product_id . '-' . implode('_', $field_value_ids);
        }

        return implode('_', $field_value_ids);
    }

}
