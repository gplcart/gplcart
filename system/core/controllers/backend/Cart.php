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
     * A number of results found for the filter conditions
     * @var integer
     */
    protected $data_total;

    /**
     * Pager limits
     * @var array
     */
    protected $data_limit;

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
        $this->actionListCart();

        $this->setTitleListCart();
        $this->setBreadcrumbListCart();

        $this->setFilterListCart();
        $this->setTotalListCart();
        $this->setPagerListCart();

        $this->setData('carts', $this->getListCart());
        $this->setData('stores', $this->store->getNames());

        $this->outputListCart();
    }

    /**
     * Set the current filter query on the cart overview page
     */
    protected function setFilterListCart()
    {
        $this->data_filter = $this->getFilterQuery();
        $allowed = array('user_email', 'user_id', 'store_id', 'sku', 'order_id', 'created', 'quantity');
        $this->setFilter($allowed, $this->data_filter);
    }

    /**
     * Applies an action to the selected shopping cart items
     */
    protected function actionListCart()
    {
        $action = (string) $this->getPosted('action');

        if (empty($action)) {
            return null;
        }

        $deleted = 0;
        foreach ((array) $this->getPosted('selected', array()) as $id) {
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
     * Sets a total number of shopping cart items
     */
    protected function setTotalListCart()
    {
        $options = array('count' => true) + $this->data_filter;
        $this->data_total = (int) $this->cart->getList($options, 'cart_id');
    }

    /**
     * Sets pager limits on the cart overview page
     */
    protected function setPagerListCart()
    {
        $this->data_limit = $this->setPager($this->data_total, $this->data_filter);
    }

    /**
     * Returns an array of shopping cart items
     * @return array
     */
    protected function getListCart()
    {
        $options = array('limit' => $this->data_limit) + $this->data_filter;
        $list = (array) $this->cart->getList($options, 'cart_id');

        $this->attachEntityUrl($list, 'product');
        return $list;
    }

    /**
     * Sets title on the cart overview page
     */
    protected function setTitleListCart()
    {
        $this->setTitle($this->text('Shopping carts'));
    }

    /**
     * Sets breadcrumbs on the cart overview page
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
     * Render and output the cart overview page
     */
    protected function outputListCart()
    {
        $this->output('sale/cart/list');
    }

}
