<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\search;

use gplcart\core\Handler;
use gplcart\core\models\Search as SearchModel,
    gplcart\core\models\Product as ProductModel;

class Product extends Handler
{

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
     * @param SearchModel $search
     * @param ProductModel $product
     */
    public function __construct(SearchModel $search, ProductModel $product)
    {
        parent::__construct();

        $this->search = $search;
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
            return $this->config->getDb()->fetchColumn($sql, $where);
        }

        $data['product_id'] = $this->config->getDb()->fetchColumnAll($sql, $where);

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
        return $this->search->setIndex($snippet, 'product_id', $product['product_id'], 'und');
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
            $indexed += (int) $this->search->setIndex($snippet, 'product_id', $product['product_id'], $language);
        }

        return ($indexed > 0);
    }

}
