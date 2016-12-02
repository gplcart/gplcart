<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\handlers\search;

use core\models\Product as ModelsProduct;
use core\handlers\search\Base as BaseHandler;

class Product extends BaseHandler
{

    /**
     * Product model instance
     * @var \core\models\Product $product
     */
    protected $product;

    /**
     * Constructor
     * @param ModelsProduct $product
     */
    public function __construct(ModelsProduct $product)
    {
        parent::__construct();

        $this->product = $product;
    }

    /**
     * Indexes a product
     * @param integer|array $product
     * @return boolean
     */
    public function index($product)
    {
        if (is_numeric($product)) {
            // Product can be numeric ID when updating
            $product = $this->product->get($product);
        }

        if (empty($product)) {
            return false;
        }

        $indexed = 0;
        $indexed += (int) $this->indexProduct($product);
        $indexed += (int) $this->indexProductTranslations($product);

        return ($indexed > 0);
    }

    /**
     * Returns an array of suggested products for a given query
     * @param string $query
     * @param array $data
     * @return array
     */
    public function search($query, array $data)
    {
        $sql = 'SELECT si.id_value';

        if (!empty($data['count'])) {
            $sql = 'SELECT COUNT(si.id_value)';
        }

        $where = array($query, $data['language'], 'und', 'product_id');

        $sql .= ' FROM search_index si'
                . ' LEFT JOIN product p ON(p.product_id = si.id_value)'
                . ' WHERE MATCH(si.text) AGAINST (? IN BOOLEAN MODE)'
                . ' AND (si.language=? OR si.language=?)'
                . ' AND si.id_key=? AND p.product_id > 0';

        if (isset($data['status'])) {
            $sql .= ' AND p.status=?';
            $where[] = (int) $data['status'];
        }

        if (isset($data['store_id'])) {
            $sql .= ' AND p.store_id=?';
            $where[] = (int) $data['store_id'];
        }

        if (empty($data['count'])) {
            $sql .= ' GROUP BY si.id_value';
        }

        if (!empty($data['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $data['limit']));
        }

        if (!empty($data['count'])) {
            return $this->db->fetchColumn($sql, $where);
        }

        $options['product_id'] = $this->db->fetchColumnAll($sql, $where);
        return $this->product->getList($options);
    }

    /**
     * Adds main product data to the search index
     * @param array $product
     * @return boolean
     */
    protected function indexProduct(array $product)
    {
        $snippet = $this->getSnippet($product, 'und');
        return $this->search->setIndex($snippet, 'product_id', $product['product_id'], 'und');
    }

    /**
     * Returns a text string to be saved in the index table
     * @param array $product
     * @param string $language
     * @return string
     */
    protected function getSnippet(array $product, $language)
    {
        $snippet = "{$product['title']} {$product['title']} {$product['sku']} {$product['description']}";
        return $this->search->filterStopwords($snippet, $language);
    }

    /**
     * Adds product translations to the search index
     * @param array $product
     * @return boolean
     */
    protected function indexProductTranslations(array $product)
    {
        if (empty($product['translation'])) {
            return false;
        }

        $indexed = 0;
        foreach ($product['translation'] as $language => $translation) {
            $translation += $product;
            $snippet = $this->getSnippet($translation, $language);
            $indexed += (int) $this->search->setIndex($snippet, 'product_id', $product['product_id'], $language);
        }

        return ($indexed > 0);
    }

}
