<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers\admin;

use core\Controller;
use core\models\Product as ModelsProduct;
use core\models\Bookmark as ModelsBookmark;

/**
 * Handles incoming requests and outputs data related to bookmarks
 */
class Bookmark extends Controller
{

    /**
     * Bookmark model instance
     * @var \core\models\Bookmark $bookmark
     */
    protected $bookmark;

    /**
     * Product model instance
     * @var \core\models\Product $product
     */
    protected $product;

    /**
     * Constructor
     * @param ModelsBookmark $bookmark
     * @param ModelsProduct $product
     */
    public function __construct(ModelsBookmark $bookmark, ModelsProduct $product)
    {
        parent::__construct();

        $this->bookmark = $bookmark;
        $this->product = $product;
    }

    /**
     * Displays the bookmarks overview page
     */
    public function bookmarks()
    {
        $query = $this->getFilterQuery();
        $limit = $this->setPager($this->getTotalBookmarks($query), $query);
        $this->data['bookmark_list'] = $this->getBookmarks($limit, $query);

        $filters = array('id_value', 'user_id', 'created', 'title', 'type');
        $this->setFilter($filters, $query);
        $this->prepareFilter();

        $action = $this->request->post('action');
        $selected = $this->request->post('selected', array());

        if (!empty($action)) {
            $this->action($selected, $action);
        }

        $this->setTitleBookmarks();
        $this->setBreadcrumbBookmarks();
        $this->outputBookmarks();
    }

    /**
     * Returns total number of bookmarks for pager
     * @param array $query
     * @return integer
     */
    protected function getTotalBookmarks(array $query)
    {
        return $this->bookmark->getList(array('count' => true) + $query);
    }

    /**
     * Sets titles on the bookmark overview page
     */
    protected function setTitleBookmarks()
    {
        $this->setTitle($this->text('Bookmarks'));
    }

    /**
     * Sets breadcrumbs on the bookmark overview page
     */
    protected function setBreadcrumbBookmarks()
    {
        $this->setBreadcrumb(array('text' => $this->text('Dashboard'), 'url' => $this->url('admin')));
    }

    /**
     * Renders the bookmark overview page
     */
    protected function outputBookmarks()
    {
        $this->output('content/bookmark/list');
    }

    /**
     * Returns an array of bookmarks
     * @param integer $limit
     * @param array $query
     * @return array
     */
    protected function getBookmarks($limit, array $query)
    {
        $bookmarks = $this->bookmark->getList(array('limit' => $limit) + $query);

        foreach ($bookmarks as &$bookmark) {
            if ($bookmark['id_key'] !== 'product_id' || empty($bookmark['id_value'])) {
                continue;
            }

            $bookmark['title'] = $this->text('Missing');
            $product_data = $this->product->get($bookmark['id_value'], $this->language->current());

            if (empty($product_data)) {
                continue;
            }

            $bookmark['title'] = $product_data['title'];
            $bookmark['url'] = '';

            $store = $this->store->get($product_data['store_id']);

            if (empty($product_data['store_id']) || empty($store)) {
                $bookmark['url'] = $this->url("product/{$bookmark['id_value']}");
                continue;
            }

            $bookmark['url'] = "{$store['scheme']}{$store['domain']}";

            if ($store['basepath'] !== "") {
                $bookmark['url'] .= "/{$store['basepath']}";
            }

            $bookmark['url'] .= "/product/{$bookmark['id_value']}";
        }

        return $bookmarks;
    }

    /**
     * Prepares filter values
     * @return null
     */
    protected function prepareFilter()
    {
        $product_id = $this->request->get('id_value');

        if (!empty($product_id)) {
            $product_data = $this->product->get($product_id);
            $this->data['filter_title'] = $this->product->getTitle($product_data);
        }

        $this->data['user'] = '';
        $user_id = $this->request->get('user_id');

        if (empty($user_id)) {
            return;
        }

        $user = $this->user->get($user_id);
        if (isset($user['email'])) {
            $this->data['user'] = $user['email'];
        }
    }

    /**
     * Applies an action to the selected bookmarks
     * @param array $selected
     * @param string $action
     * @return boolean
     */
    protected function action(array $selected, $action)
    {
        $deleted = 0;
        foreach ($selected as $id) {
            if ($action === 'delete' && $this->access('bookmark_delete')) {
                $deleted += (int) $this->bookmark->delete($id);
            }
        }

        if ($deleted > 0) {
            $this->session->setMessage($this->text('Deleted %num bookmarks', array('%num' => $deleted)), 'success');
            return true;
        }

        return false;
    }

}
