<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\backend;

use gplcart\core\controllers\backend\Controller as BackendController;

/**
 * Handles incoming requests and outputs data related to user shopping carts
 */
class Cart extends BackendController
{

    /**
     * The current filter query
     * @var array
     */
    protected $data_filter = array();

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Displays the shopping cart overview page
     */
    public function listCart()
    {
        $this->actionCart();

        $this->setTitleListCart();
        $this->setBreadcrumbListCart();

        $this->setFilterCart();
        $this->setData('carts', $this->getListCart());
        $this->setData('stores', $this->store->getNames());
        $this->outputListCart();
    }

    /**
     * Set the current filter query
     * @return array
     */
    protected function setFilterCart()
    {
        $this->data_filter = $this->getFilterQuery();
        $allowed = array('user_email', 'user_id', 'store_id', 'sku', 'order_id', 'created', 'quantity');
        $this->setFilter($allowed, $this->data_filter);
        return $this->data_filter;
    }

    /**
     * Applies an action to the selected shopping cart items
     * @return null
     */
    protected function actionCart()
    {
        $action = (string) $this->request->post('action');

        if (empty($action)) {
            return null;
        }

        $deleted = 0;
        foreach ((array) $this->request->post('selected', array()) as $id) {
            if ($action === 'delete' && $this->access('cart_delete')) {
                $deleted += (int) $this->cart->delete($id);
            }
        }

        if ($deleted > 0) {
            $message = $this->text('Shopping cart items have been deleted');
            $this->setMessage($message, 'success', true);
        }
    }

    /**
     * Returns total number of shopping cart items
     * @return integer
     */
    protected function getTotalCart()
    {
        $options = array('count' => true) + $this->data_filter;
        return (int) $this->cart->getList($options, 'cart_id');
    }

    /**
     * Returns an array of shopping cart items
     * @return array
     */
    protected function getListCart()
    {
        $total = $this->getTotalCart();
        $limit = $this->setPager($total, $this->data_filter);

        $options = array('limit' => $limit) + $this->data_filter;
        $list = $this->cart->getList($options, 'cart_id');

        $this->attachEntityUrl($list, 'product');
        return $list;
    }

    /**
     * Sets title on the shopping cart overview page
     */
    protected function setTitleListCart()
    {
        $this->setTitle($this->text('Shopping carts'));
    }

    /**
     * Sets breadcrumbs on the shopping cart overview page
     */
    protected function setBreadcrumbListCart()
    {
        $breadcrumb = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')
        );

        $this->setBreadcrumb($breadcrumb);
    }

    /**
     * Renders the shopping cart overview page
     */
    protected function outputListCart()
    {
        $this->output('sale/cart/list');
    }

}
