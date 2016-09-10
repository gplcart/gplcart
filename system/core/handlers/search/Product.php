<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\handlers\search;

use PDO;
use core\Config;
use core\classes\Request;
use core\models\Image as ModelsImage;
use core\models\Price as ModelsPrice;
use core\models\Search as ModelsSearch;
use core\models\Product as ModelsProduct;

class Product
{

    /**
     * Search model instance
     * @var \core\models\Search $search
     */
    protected $search;

    /**
     * Product model instance
     * @var \core\models\Product $product
     */
    protected $product;

    /**
     * Price model instance
     * @var \core\models\Price $price
     */
    protected $price;

    /**
     * Image model instance
     * @var \core\models\Image $image
     */
    protected $image;

    /**
     * Request class instance
     * @var \core\classes\Request $request
     */
    protected $request;

    /**
     * PDO class instance
     * @var \core\classes\Database $db
     */
    protected $db;

    /**
     * Constructor
     * @param ModelsSearch $search
     * @param ModelsProduct $product
     * @param \core\handlers\search\ModelsPrice $price
     * @param ModelsImage $image
     * @param Request $request
     * @param Config $config
     */
    public function __construct(ModelsSearch $search, ModelsProduct $product,
            ModelsPrice $price, ModelsImage $image, Request $request,
            Config $config)
    {
        $this->price = $price;
        $this->image = $image;
        $this->search = $search;
        $this->product = $product;
        $this->request = $request;
        $this->db = $config->getDb();
    }

    /**
     * Indexes a product
     * @param integer $product_id
     * @param array $options
     * @return boolean
     */
    public function index($product_id, $options)
    {
        $product = $this->product->get($product_id);

        $result = $this->indexProduct($product);
        $this->indexProductTranslations($product);
        return $result;
    }

    /**
     * Returns product total to be indexed
     * @param array $options
     * @return integer
     */
    public function total($options)
    {
        $options['count'] = true;
        return $this->product->getList($options);
    }

    /**
     * Returns an array of suggested products for a given query
     * @param string $query
     * @param array $options
     * @return array
     */
    public function search($query, $options)
    {
        $sql = 'SELECT p.*, a.alias, s.domain, s.basepath, s.name AS store_name, COALESCE(NULLIF(pt.title, ""), p.title) AS title ';

        if (!empty($options['count'])) {
            $sql = 'SELECT COUNT(p.product_id) ';
        }

        $where = array(
            ':query' => $query,
            ':id_key' => 'product_id',
            ':language' => $options['language'],
            ':default_language' => 'und');

        $sql .= '
            FROM product p
            LEFT JOIN search_index si ON(p.product_id = si.id_value AND si.id_key=:id_key)
            LEFT JOIN product_translation pt ON(p.product_id=pt.product_id AND pt.language=si.language)
            LEFT JOIN store s ON(p.store_id=s.store_id)
            LEFT JOIN alias a ON(a.id_key=:id_key AND a.id_value=p.product_id)
            WHERE MATCH(si.text) AGAINST (:query IN BOOLEAN MODE) AND (si.language=:language OR si.language=:default_language)
            ';

        if (isset($options['status'])) {
            $sql .= ' AND p.status=:status';
            $where[':status'] = (int) $options['status'];
        }

        if (isset($options['store_id'])) {
            $sql .= ' AND p.store_id=:store_id';
            $where[':store_id'] = (int) $options['store_id'];
        }

        if (empty($options['count'])) {
            $sql .= ' GROUP BY p.product_id';
        }

        if (isset($options['sort']) && (isset($options['order']) && in_array($options['order'], array('asc', 'desc')))) {
            $allowed_sort = array('title', 'price', 'created');

            if (in_array($options['sort'], $allowed_sort, true)) {
                $sql .= " ORDER BY p.{$options['sort']} {$options['order']}";
            }
        }

        if (!empty($options['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $options['limit']));
        }

        $sth = $this->db->prepare($sql);
        $sth->execute($where);

        if (!empty($options['count'])) {
            return $sth->fetchColumn();
        }

        $products = array();
        foreach ($sth->fetchAll(PDO::FETCH_ASSOC) as $result) {
            $products[$result['product_id']] = $result;
        }

        if (!empty($options['prepare'])) {
            $this->prepareResults($products, $options);
        }

        return $products;
    }

    /**
     * Adds main product data to the search index
     * @param array $product
     * @return boolean
     */
    protected function indexProduct($product)
    {
        $text = "{$product['title']} {$product['title']} {$product['sku']} {$product['description']}";
        $filtered_text = $this->search->filterStopwords(strip_tags($text), 'und');

        if ($filtered_text) {
            return $this->search->setIndex($filtered_text, 'product_id', $product['product_id'], 'und');
        }

        return false;
    }

    /**
     * Adds product translations to the search index
     * @param array $product
     * @return boolean
     */
    protected function indexProductTranslations($product)
    {
        if (empty($product['translation'])) {
            return false;
        }

        foreach ($product['translation'] as $language => $translation) {
            $text = "{$translation['title']}{$translation['title']}{$translation['description']}";
            $filtered_text = $this->search->filterStopwords(strip_tags($text), $language);

            if ($filtered_text) {
                $this->search->setIndex($filtered_text, 'product_id', $product['product_id'], $language);
            }
        }

        return true;
    }

    /**
     * Modifies an array of search results (images, price etc)
     * @param array $results
     * @param array $options
     * @return array
     */
    protected function prepareResults(&$results, $options)
    {
        $product_ids = array_keys($results);

        $scheme = $this->$this->request->getScheme();

        foreach ($results as $product_id => &$result) {
            $result['price_formatted'] = $this->price->format($result['price'], $result['currency']);
            $result['url'] = "$scheme{$result['domain']}";

            if ($result['basepath']) {
                $result['url'] .= "/{$result['basepath']}";
            }

            $result['url'] .= "/product/{$result['product_id']}";
            $result['thumb'] = $this->image->getThumb($product_id, $options['imagestyle'], 'product_id', $product_ids);
        }

        return $results;
    }

}
