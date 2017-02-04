<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\frontend;

use gplcart\core\models\State as StateModel;
use gplcart\core\models\Order as OrderModel;
use gplcart\core\models\Address as AddressModel;
use gplcart\core\models\Country as CountryModel;
use gplcart\core\models\Payment as PaymentModel;
use gplcart\core\models\Shipping as ShippingModel;
use gplcart\core\controllers\frontend\Controller as FrontendController;

/**
 * Handles incoming requests and outputs data related to checkout process
 */
class Checkout extends FrontendController
{

    /**
     * Order model instance
     * @var \gplcart\core\models\Order $order
     */
    protected $order;

    /**
     * Address model instance
     * @var \gplcart\core\models\Address $address
     */
    protected $address;

    /**
     * Country model instance
     * @var \gplcart\core\models\Country $country
     */
    protected $country;

    /**
     * State model instance
     * @var \gplcart\core\models\State $state
     */
    protected $state;

    /**
     * Shipping model instance
     * @var \gplcart\core\models\Shipping $shipping
     */
    protected $shipping;

    /**
     * Payment model instance
     * @var \gplcart\core\models\Payment $payment
     */
    protected $payment;

    /**
     * Current state of address form
     * @var boolean
     */
    protected $address_form = false;

    /**
     * Current state of login form
     * @var bool
     */
    protected $login_form = false;

    /**
     * Whether the cart has been updated
     * @var boolean
     */
    protected $cart_updated = false;

    /**
     * Whether we're in admin mode
     * @var boolean
     */
    protected $admin;

    /**
     * Admin user ID
     * @var integer
     */
    protected $admin_user_id;

    /**
     * The current order
     * @var array
     */
    protected $data_order = array();

    /**
     * The current cart content
     * @var array
     */
    protected $data_cart = array();

    /**
     * An array of customer user data
     * @var array
     */
    protected $data_user = array();

    /**
     * Form data array
     * @var array
     */
    protected $data_form = array();

    /**
     * Order user id. Greater than 0 when editing an order
     * @var integer
     */
    protected $order_id = 0;

    /**
     * Order customer ID. Default to cart UID
     * @var mixed
     */
    protected $order_user_id;

    /**
     * Order store ID. Default to the current store
     * @var integer
     */
    protected $order_store_id;

    /**
     * Constructor
     * @param CountryModel $country
     * @param StateModel $state
     * @param AddressModel $address
     * @param OrderModel $order
     * @param ShippingModel $shipping
     * @param PaymentModel $payment
     */
    public function __construct(CountryModel $country, StateModel $state,
            AddressModel $address, OrderModel $order, ShippingModel $shipping,
            PaymentModel $payment)
    {
        parent::__construct();

        $this->order = $order;
        $this->state = $state;
        $this->address = $address;
        $this->country = $country;
        $this->payment = $payment;
        $this->shipping = $shipping;

        $this->admin_user_id = $this->uid;
        $this->order_user_id = $this->cart_uid;
        $this->order_store_id = $this->store_id;
    }

    /**
     * Page callback for add order form
     * @param integer $user_id
     */
    public function addUserOrderCheckout($user_id)
    {
        $this->setAdminModeCheckout();
        $this->setUserCheckout($user_id);
        $this->editCheckout();
    }

    /**
     * Page callback for edit order form
     * @param integer $order_id
     */
    public function editOrderCheckout($order_id)
    {
        $this->setAdminModeCheckout();
        $this->setOrderCheckout($order_id);
        $this->editCheckout();
    }

    /**
     * Sets flag that we're in admin mode, i.e adding/updating an order
     */
    protected function setAdminModeCheckout()
    {
        $this->admin = ($this->access('order_add') || $this->access('order_edit'));
    }

    /**
     * Loads a user and sets properties for a new order
     * @param integer|string $user_id
     */
    protected function setUserCheckout($user_id)
    {
        if (!is_numeric($user_id)) {
            $this->outputHttpStatus(403);
        }

        $user = $this->user->get($user_id);

        if (empty($user['status'])) {
            $this->outputHttpStatus(404);
        }

        $this->data_user = $user;
        $this->order_user_id = $user_id;
        $this->order_store_id = $user['store_id'];
    }

    /**
     * Displays the checkout page
     */
    public function editCheckout()
    {
        $this->setCartContentCheckout();

        $this->setTitleEditCheckout();
        $this->setBreadcrumbEditCheckout();

        $this->controlAccessCheckout();
        $this->setFormDataBeforeCheckout();

        $this->submitCheckout();

        $this->setFormDataAfterCheckout();
        $this->setDataFormCheckout();

        $this->outputEditCheckout();
    }

    /**
     * Sets an order data to be updated
     * @param null|integer $order_id
     */
    protected function setOrderCheckout($order_id)
    {
        $order = $this->order->get($order_id);

        if (empty($order)) {
            $this->outputHttpStatus(404);
        }

        $this->attachItemTotalFormatted($order);

        $this->data_order = $order;
        $this->order_id = $order_id;

        $this->cart_uid = $order['user_id'];
        $this->order_user_id = $order['user_id'];
        $this->order_store_id = $order['store_id'];
        $this->data_user = $this->user->get($order['user_id']);
    }

    /**
     * Sets a cart content depending on whether we're editing an existing order
     * or creating a new one during checkout
     */
    protected function setCartContentCheckout()
    {
        $data = array(
            'user_id' => $this->cart_uid,
            'order_id' => $this->order_id,
            'store_id' => $this->order_store_id
        );

        $this->data_cart = $this->cart->getContent($data);
    }

    /**
     * Sets titles on the checkout page
     */
    protected function setTitleEditCheckout()
    {
        $this->setTitle($this->text('Checkout'));
    }

    /**
     * Sets breadcrumbs on the checkout page
     */
    protected function setBreadcrumbEditCheckout()
    {
        $breadcrumb = array(
            'text' => $this->text('Home'),
            'url' => $this->url('/')
        );

        $this->setBreadcrumb($breadcrumb);
    }

    /**
     * Controls access to the checkout page
     */
    protected function controlAccessCheckout()
    {
        if (!empty($this->data_cart['items'])) {
            return null;
        }

        $data = array('admin' => $this->admin);

        $form = $this->render('checkout/form', $data);
        $this->setData('checkout_form', $form);
        $this->output('checkout/checkout');
    }

    /**
     * Sets initial form data
     */
    protected function setFormDataBeforeCheckout()
    {
        $default_order = array(
            'user_id' => $this->order_user_id,
            'creator' => $this->admin_user_id,
            'store_id' => $this->order_store_id,
            'currency' => $this->data_cart['currency'],
            'status' => $this->order->getInitialStatus()
        );

        // Override with existing order values if we're editing the order
        $order = gplcart_array_merge($default_order, $this->data_order);

        $this->data_form['order'] = $order;
        $this->data_form['messages'] = array();
        $this->data_form['admin'] = $this->admin;
        $this->data_form['user'] = $this->data_user;

        $this->data_form['statuses'] = $this->order->getStatuses();
        $this->data_form['payment_methods'] = $this->payment->getList(true);
        $this->data_form['shipping_methods'] = $this->shipping->getList(true);

        // It's for price rule calculator
        $this->data_form['store_id'] = $this->order_store_id;
        $this->data_form['currency'] = $this->data_cart['currency'];
    }

    /**
     * Set finish data before passing to templates
     * @return null
     */
    protected function setFormDataAfterCheckout()
    {
        if (empty($this->data_cart)) {
            return null;
        }

        $countries = $this->country->getNames(true);
        $default_country = count($countries) == 1 ? key($countries) : '';

        $address = $this->getSubmitted('address', array());
        $address += array('country' => $default_country);

        $this->data_form['address'] = $address;
        $this->data_form['countries'] = $countries;
        $this->data_form['login_form'] = $this->login_form;
        $this->data_form['address_form'] = $this->address_form;
        $this->data_form['format'] = $this->country->getFormat($address['country']);

        $state_conditions = array('country' => $address['country'], 'status' => 1);
        $this->data_form['states'] = $this->state->getList($state_conditions);

        if (empty($this->data_form['states'])) {
            unset($this->data_form['format']['state_id']);
        }

        $this->data_form['cart'] = $this->prepareCart($this->data_cart);
        $this->data_form['addresses'] = $this->address->getTranslatedList($this->order_user_id);

        $excess = $this->address->getExcess($this->order_user_id, $this->data_form['addresses']);

        $this->data_form['can_add_address'] = empty($excess);
        $this->data_form['can_save_address'] = empty($excess)//
                && !empty($this->uid)//
                && (!empty($address['country']) || empty($countries));

        $this->calculateCheckout();
        $this->setFormDataPanesOrder();
    }

    /**
     * Sets rendered panes
     */
    protected function setFormDataPanesOrder()
    {
        $panes = array('admin', 'login', 'review', 'payment_methods',
            'shipping_methods', 'shipping_address');

        foreach ($panes as $pane) {
            $this->data_form["pane_$pane"] = $this->render("checkout/panes/$pane", $this->data_form);
        }
    }

    /**
     * Handles submitted actions
     * @return null
     */
    protected function submitCheckout()
    {
        $this->setSubmitted('order');

        $address = $this->getSubmitted('address', array());

        if (!empty($address)) {
            $this->address_form = true;
        }

        if ($this->isPosted('add_address') || $this->isPosted('get_states')) {
            $this->address_form = true;
        }

        if ($this->isPosted('cancel_address_form')) {
            $this->address_form = false;
        }

        $this->submitAddAddressCheckout();

        if ($this->isPosted('checkout_login') && empty($this->uid)) {
            $this->login_form = true;
        }

        if ($this->isPosted('update')) {
            $this->setMessage($this->text('Form has been updated'), 'success', false);
        }

        $this->submitLoginCheckout();

        if ($this->isPosted('checkout_anonymous')) {
            $this->login_form = false;
        }

        $this->validateCouponCheckout();
        $this->submitCartCheckout();
        $this->submitOrderCheckout();
    }

    /**
     * Saves a submitted address
     */
    protected function submitAddAddressCheckout()
    {
        if (!$this->isPosted('save_address')) {
            return null;
        }

        $errors = $this->validateAddressCheckout();

        if (empty($errors)) {
            $this->addAddressCheckout();
            $this->address_form = false;
        }
    }

    /**
     * Handles login action
     */
    protected function submitLoginCheckout()
    {
        if ($this->isPosted('login')) {
            $this->login_form = true;
            $this->loginCheckout();
        }
    }

    /**
     * Logs in a customer during checkout
     */
    protected function loginCheckout()
    {
        $result = $this->user->login($this->getSubmitted('user'));

        if (isset($result['user'])) {
            $result = $this->cart->login($result['user'], $this->data_cart);
        }

        if (empty($result['user'])) {
            $this->setError('login', $result['message']);
        } else {
            $this->redirect($result['redirect'], $result['message'], $result['severity']);
        }
    }

    /**
     * Validates a coupon code
     * @return null
     */
    protected function validateCouponCheckout()
    {
        $price_rule_id = (int) $this->request->post('check_pricerule');

        if (empty($price_rule_id)) {
            return null;
        }

        $code = $this->getSubmitted('data.pricerule_code', '');

        if ($code === '') {
            return null;
        }

        if (!$this->order->priceRuleCodeMatches($price_rule_id, $code)) {
            $this->setError('pricerule_code', $this->text('Invalid code'));
            $this->setMessageFormCheckout('components.warning', $this->text('Invalid code'));
        }
    }

    /**
     * Handles various cart actions
     */
    protected function submitCartCheckout()
    {
        $this->submitCartItemsCheckout();
        $this->moveCartWishlistCheckout();

        $this->deleteCartCheckout();
        $this->updateCartCheckout();
    }

    /**
     * Applies an action to the cart items
     * @return null
     */
    protected function submitCartItemsCheckout()
    {
        $items = $this->getSubmitted('cart.items');

        if (empty($items)) {
            return null;
        }

        $errors = array();
        foreach ($items as $sku => $item) {
            $errors += $this->validateCartItemCheckout($sku, $item);
            if (empty($errors)) {
                $this->updateCartQuantityCheckout($sku, $item['quantity']);
            }
        }

        if (!empty($errors)) {
            $this->setMessageFormCheckout('cart.warning', $errors);
            return null;
        }

        $this->setSubmitted('cart.action.update', true);
    }

    /**
     * Sets an array of messages to the checkout form
     * @param string $key
     * @param string|array $message
     */
    protected function setMessageFormCheckout($key, $message)
    {
        settype($message, 'array');

        $flatten = gplcart_array_flatten($message);
        $string = implode('<br>', array_unique($flatten));
        gplcart_array_set_value($this->data_form['messages'], $key, $string);
    }

    /**
     * Updates cart quantity
     * @param string $sku
     * @param integer $quantity
     * @return bool
     */
    protected function updateCartQuantityCheckout($sku, $quantity)
    {
        $cart_id = $this->data_cart['items'][$sku]['cart_id'];
        return $this->cart->update($cart_id, array('quantity' => $quantity));
    }

    /**
     * Validates a cart item and returns possible errors
     * @param string $sku
     * @param array $item
     * @return array
     */
    protected function validateCartItemCheckout($sku, $item)
    {
        $item += array(
            'sku' => $sku,
            'increment' => false,
            'admin' => $this->admin,
            'user_id' => $this->order_user_id,
            'store_id' => $this->order_store_id
        );

        $this->setSubmitted('update', $item);
        $this->setSubmitted("cart.items.$sku", $item);

        return $this->validate('cart', array('parents' => "cart.items.$sku"));
    }

    /**
     * Moves a cart item to the wishlist
     */
    protected function moveCartWishlistCheckout()
    {
        $sku = $this->getSubmitted('cart.action.wishlist');

        if (empty($sku)) {
            return null;
        }

        $options = array(
            'sku' => $sku,
            'user_id' => $this->order_user_id,
            'store_id' => $this->order_store_id
        );

        $result = $this->cart->moveToWishlist($options);

        if (isset($result['wishlist_id'])) {
            $this->setSubmitted('cart.action.update', true);
            $this->setMessage($result['message'], 'success');
        }
    }

    /**
     * Deletes an item from the cart
     * @return boolean
     */
    protected function deleteCartCheckout()
    {
        $cart_id = $this->getSubmitted('cart.action.delete');

        if (empty($cart_id)) {
            return false;
        }

        $this->setSubmitted('cart.action.update', true);
        return $this->cart->delete(array('cart_id' => $cart_id));
    }

    /**
     * Updates the current cart
     * @return null
     */
    protected function updateCartCheckout()
    {
        if ($this->isSubmitted('cart.action.update')) {
            $this->setCartContentCheckout();
        }
    }

    /**
     * Saves an order to the database
     * @return null
     */
    protected function submitOrderCheckout()
    {
        if (!$this->isPosted('save')) {
            return null;
        }

        $address_errors = $this->validateAddressCheckout();
        $order_errors = $this->validateOrderCheckout();

        if ($this->address_form) {
            // Since we're going to save a new address and have no address ID yet,
            // so order validator will throw "Shipping address required" error
            // which is useless for users and should be replaced with
            // more informative per-field errors (if any) from address validator
            unset($order_errors['shipping_address']);
        }

        if (empty($address_errors) && empty($order_errors)) {
            $this->addAddressCheckout();
            $this->saveOrderCheckout();
            return null;
        }

        $errors = gplcart_array_merge($order_errors, $address_errors);
        $this->setError(null, $errors);
    }

    /**
     * Adds/updates an order
     */
    protected function saveOrderCheckout()
    {
        $submitted = $this->getSubmitted();
        $submitted += $this->data_form['order'];
        $submitted['cart'] = $this->data_cart;

        if (empty($this->data_order['order_id'])) {
            $this->addOrderCheckout($submitted);
        } else {
            $this->updateOrderCheckout($this->data_order['order_id'], $submitted);
        }
    }

    /**
     * Validates a submitted address
     * @return array
     */
    protected function validateAddressCheckout()
    {
        if ($this->address_form) {
            $this->setSubmitted('address.user_id', $this->order_user_id);
            return $this->validate('address', array('parents' => 'address'));
        }
        return array();
    }

    /**
     * Validates an array of submitted values before creating an order
     * @return array
     */
    protected function validateOrderCheckout()
    {
        $this->setSubmitted('update', array()); // Reset all values set before

        $this->setSubmitted('store_id', $this->store_id);
        $this->setSubmitted('user_id', $this->order_user_id);
        $this->setSubmitted('creator', $this->admin_user_id);

        return $this->validate('order');
    }

    /**
     * Saves a submitted address
     */
    protected function addAddressCheckout()
    {
        $submitted = $this->getSubmitted('address');

        if ($this->address_form && !empty($submitted)) {
            $address_id = $this->address->add($submitted);
            $this->setSubmitted('shipping_address', $address_id);
            $this->address->controlLimit($this->order_user_id);
        }
    }

    /**
     * Adds a new order
     */
    protected function addOrderCheckout(array $submitted)
    {
        $options = array('admin' => $this->admin);
        $result = $this->order->submit($submitted, $this->data_cart, $options);
        $this->redirect($result['redirect'], $result['message'], $result['severity']);
    }

    /**
     * Updates an order
     * @param type $order_id
     */
    protected function updateOrderCheckout($order_id, array $submitted)
    {
        $this->controlAccess('order_edit');

        $this->order->update($order_id, $submitted);
        $this->order->addLog($submitted['log'], $this->uid, $order_id);

        $message = $this->text('Order has been updated');
        $this->redirect("admin/sale/order/$order_id", $message, 'success');
    }

    /**
     * Calculates order totals
     */
    protected function calculateCheckout()
    {
        $submitted = array('order' => $this->getSubmitted());
        $this->data_form = gplcart_array_merge($this->data_form, $submitted);

        $result = $this->order->calculate($this->data_form);

        $this->data_form['total'] = $result['total'];
        $this->data_form['total_formatted'] = $this->price->format($result['total'], $result['currency']);

        $components = $this->prepareOrderComponentsCheckout($result);
        $this->data_form['price_components'] = $components;
    }

    /**
     * Prepares an array of price rule components
     * @param array $calculated
     * @return array
     */
    protected function prepareOrderComponentsCheckout($calculated)
    {
        $components = array();
        foreach ($calculated['components'] as $type => $component) {

            switch ($type) {
                case 'shipping':
                    $methods = $this->shipping->getList();
                    break;
                case 'payment':
                    $methods = $this->payment->getList();
                    break;
            }

            if (isset($methods) && isset($methods[$this->data_form[$type]['name']])) {
                $name = $methods[$this->data_form[$type]['name']];
            } else if (isset($component['rule']['name'])) {
                $name = $component['rule']['name'];
            }

            $components[$type] = array(
                'price' => $component['price'],
                'name' => isset($name) ? $name : $this->text($type),
                'rule' => isset($component['rule']) ? $component['rule'] : false,
                'price_formatted' => $this->price->format($component['price'], $calculated['currency'])
            );
        }
        return $components;
    }

    /**
     * Sets form on the checkout page
     */
    protected function setDataFormCheckout()
    {
        $form = $this->render('checkout/form', $this->data_form);

        if ($this->request->isAjax()) {
            $this->response->html($form);
        }

        $this->setData('checkout_form', $form);
    }

    /**
     * Outputs the checkout page
     */
    protected function outputEditCheckout()
    {
        $this->output('checkout/checkout');
    }

    /**
     * Displays the complete order page
     * @param integer $order_id
     */
    public function completeCheckout($order_id)
    {
        $this->setOrderCheckout($order_id);

        $this->setTitleCompleteCheckout();
        $this->setBreadcrumbCompleteCheckout();

        $this->controlAccessCompleteCheckout();

        $this->setData('templates', $this->getCompleteTemplatesCheckout());
        $this->setData('complete_message', $this->getCompleteMessageCheckout());

        $this->outputCompleteCheckout();
    }

    /**
     * Returns an array of rendered templates
     * provided by payment/shipping methods and used on the order complete page
     * @return array
     */
    protected function getCompleteTemplatesCheckout()
    {
        $templates = array();
        foreach (array('payment', 'shipping') as $type) {

            switch ($type) {
                case 'shipping':
                    $method = $this->shipping->get($this->data_order[$type]);
                    break;
                case 'payment':
                    $method = $this->payment->get($this->data_order[$type]);
                    break;
            }

            if (empty($method['status']) || empty($method['template']['complete'])) {
                continue;
            }

            $settings = array();
            $template = $method['template']['complete'];

            if (!empty($method['module'])) {
                $template = "{$method['module']}|$template";
                $settings = $this->config->module($method['module']);
            }

            $options = array(
                'method' => $method,
                'settings' => $settings,
                'order' => $this->data_order
            );

            $templates[$type] = $this->render($template, $options);
        }

        return $templates;
    }

    /**
     * Ensures the order belongs to the current cart user
     */
    protected function controlAccessCompleteCheckout()
    {
        if (strcmp((string) $this->data_order['user_id'], $this->order_user_id) !== 0) {
            $this->outputHttpStatus(403);
        }

        if ($this->data_order['status'] !== $this->order->getInitialStatus()) {
            $this->outputHttpStatus(403);
        }
    }

    /**
     * Returns a complete order message
     * @return string
     */
    protected function getCompleteMessageCheckout()
    {
        return $this->order->getCompleteMessage($this->data_order);
    }

    /**
     * Sets titles on the complete order page
     */
    protected function setTitleCompleteCheckout()
    {
        $vars = array('@num' => $this->data_order['order_id']);
        $title = $this->text('Order #@num. Checkout completed', $vars);
        $this->setTitle($title);
    }

    /**
     * Sets breadcrumbs on the complete order page
     */
    protected function setBreadcrumbCompleteCheckout()
    {
        $breadcrumb = array(
            'text' => $this->text('Home'),
            'url' => $this->url('/')
        );

        $this->setBreadcrumb($breadcrumb);
    }

    /**
     * Outputs the complete order page
     */
    protected function outputCompleteCheckout()
    {
        $this->output('checkout/complete');
    }

}
