<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\search;

use gplcart\core\Config;
use gplcart\core\models\Product as ProductModel;
use gplcart\core\models\Search as SearchModel;

class Product
{

    /**
     * Config class instance
     * @var \gplcart\core\Config $config
     */
    protected $config;

    /**
     * Product model instance
     * @var \gplcart\core\models\Product $product
     */
    protected $product;

    /**
     * Search model instance
     * @var \gplcart\core\models\Search $search
     */
    protected $search;

    /**
     * @param Config $config
     * @param SearchModel $search
     * @param ProductModel $product
     */
    public function __construct(Config $config, SearchModel $search, ProductModel $product)
    {
        $this->search = $search;
        $this->config = $config;
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
            $product = $this->product->get($product);
        }

        if (empty($product)) {
            return false;
        }

        $indexed = 0;
        $indexed += (int) $this->indexProduct($product);
        $indexed += (int) $this->indexProductTranslations($product);

        return $indexed > 0;
    }

    /**
     * Returns an array of suggested products for a given query
     * @param string $query
     * @param array $data
     * @return array
     */
    public function search($query, array $data)
    {
        $sql = 'SELECT si.entity_id';

        if (!empty($data['count'])) {
            $sql = 'SELECT COUNT(si.entity_id)';
        }

        $conditions = array($query, $data['language'], 'und', 'product');

        $sql .= ' FROM search_index si
                  LEFT JOIN product p ON(p.product_id = si.entity_id)
                  WHERE MATCH(si.text) AGAINST (? IN BOOLEAN MODE)
                  AND (si.language=? OR si.language=?)
                  AND si.entity=? AND p.product_id IS NOT NULL';

        if (isset($data['status'])) {
            $sql .= ' AND p.status=?';
            $conditions[] = (int) $data['status'];
        }

        if (isset($data['store_id'])) {
            $sql .= ' AND p.store_id=?';
            $conditions[] = (int) $data['store_id'];
        }

        if (empty($data['count'])) {
            $sql .= ' GROUP BY si.entity_id';
        }

        if (!empty($data['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $data['limit']));
        }

        if (!empty($data['count'])) {
            return $this->config->getDb()->fetchColumn($sql, $conditions);
        }

        $data['product_id'] = $this->config->getDb()->fetchColumnAll($sql, $conditions);

        if (empty($data['product_id'])) {
            return array();
        }

        unset($data['language']);
        return $this->product->getList($data);
    }

    /**
     * Adds the main product data to the search index
     * @param array $product
     * @return boolean
     */
    protected function indexProduct(array $product)
    {
        $snippet = $this->search->getSnippet($product, 'und');
        return $this->search->setIndex($snippet, 'product', $product['product_id'], 'und');
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
            $snippet = $this->search->getSnippet($translation, $language);
            $indexed += (int) $this->search->setIndex($snippet, 'product', $product['product_id'], $language);
        }

        return $indexed > 0;
    }

}
