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
        $this->setPagerLimit();

        $this->setData('carts', $this->getListCart());

        $this->outputListCart();
    }

    /**
     * Set the current filter query on the cart overview page
     */
    protected function setFilterListCart()
    {
        $allowed = array('user_email', 'user_id', 'store_id', 'sku',
            'order_id', 'created', 'quantity');

        $this->setFilter($allowed);
    }

    /**
     * Applies an action to the selected shopping cart items
     */
    protected function actionListCart()
    {
        list($selected, $action) = $this->getPostedAction();

        $deleted = 0;
        foreach ($selected as $id) {
            if ($action === 'delete' && $this->access('cart_delete')) {
                $deleted += (int) $this->cart->delete($id);
            }
        }

        if ($deleted > 0) {
            $message = $this->text('Deleted %num item(s)', array('%num' => $deleted));
            $this->setMessage($message, 'success');
        }
    }

    /**
     * Sets a total number of shopping cart items
     */
    protected function setTotalListCart()
    {
        $options = $this->query_filter;
        $options['count'] = true;
        $this->total = (int) $this->cart->getList($options, 'cart_id');
    }

    /**
     * Returns an array of shopping cart items
     * @return array
     */
    protected function getListCart()
    {
        $options = $this->query_filter;
        $options['limit'] = $this->limit;

        $list = (array) $this->cart->getList($options, 'cart_id');

        $this->attachEntityUrl($list, 'product');
        return $list;
    }

    /**
     * Sets title on the cart overview page
     */
    protected function setTitleListCart()
    {
        $this->setTitle($this->text('Shopping cart items'));
    }

    /**
     * Sets breadcrumbs on the cart overview page
     */
    protected function setBreadcrumbListCart()
    {
        $this->setBreadcrumbHome();
    }

    /**
     * Render and output the cart overview page
     */
    protected function outputListCart()
    {
        $this->output('sale/cart/list');
    }

}
