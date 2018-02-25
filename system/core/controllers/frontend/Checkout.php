<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\frontend;

use gplcart\core\models\Address;
use gplcart\core\models\CartAction;
use gplcart\core\models\Country;
use gplcart\core\models\Order;
use gplcart\core\models\OrderAction;
use gplcart\core\models\OrderDimension;
use gplcart\core\models\OrderHistory;
use gplcart\core\models\Payment;
use gplcart\core\models\Shipping;
use gplcart\core\models\CountryState;
use gplcart\core\models\UserAction;
use gplcart\core\traits\Checkout as CheckoutTrait;

/**
 * Handles incoming requests and outputs data related to checkout process
 */
class Checkout extends Controller
{

    use CheckoutTrait;

    /**
     * Order model instance
     * @var \gplcart\core\models\Order $order
     */
    protected $order;

    /**
     * Order action model instance
     * @var \gplcart\core\models\OrderAction $order_action
     */
    protected $order_action;

    /**
     * User access model instance
     * @var \gplcart\core\models\UserAction $user_action
     */
    protected $user_action;

    /**
     * Order history model instance
     * @var \gplcart\core\models\OrderHistory $order_history
     */
    protected $order_history;

    /**
     * Order dimension model instance
     * @var \gplcart\core\models\OrderDimension $order_dimension
     */
    protected $order_dimension;

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
     * @var \gplcart\core\models\CountryState $state
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
     * Cart action model instance
     * @var \gplcart\core\models\CartAction $cart_action
     */
    protected $cart_action;

    /**
     * Current state of shipping address form
     * @var boolean
     */
    protected $show_shipping_address_form = false;

    /**
     * Current state of payment address form
     * @var boolean
     */
    protected $show_payment_address_form = false;

    /**
     * Whether payment address should be provided
     * @var bool
     */
    protected $same_payment_address = true;

    /**
     * Current state of login form
     * @var bool
     */
    protected $show_login_form = false;

    /**
     * Admin mode
     * @var string
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
     * Checkout constructor.
     * @param Country $country
     * @param CountryState $state
     * @param Address $address
     * @param Order $order
     * @param OrderAction $order_action
     * @param OrderHistory $order_history
     * @param UserAction $user_action
     * @param OrderDimension $order_dimension
     * @param Shipping $shipping
     * @param Payment $payment
     * @param CartAction $cart_action
     */
    public function __construct(Country $country, CountryState $state, Address $address,
                                Order $order, OrderAction $order_action, OrderHistory $order_history,
                                UserAction $user_action, OrderDimension $order_dimension,
                                Shipping $shipping, Payment $payment, CartAction $cart_action)
    {
        parent::__construct();

        $this->order = $order;
        $this->state = $state;
        $this->address = $address;
        $this->country = $country;
        $this->payment = $payment;
        $this->shipping = $shipping;
        $this->user_action = $user_action;
        $this->cart_action = $cart_action;
        $this->order_action = $order_action;
        $this->order_history = $order_history;
        $this->order_dimension = $order_dimension;

        $this->admin_user_id = $this->uid;
        $this->order_user_id = $this->cart_uid;
        $this->order_store_id = $this->store_id;
    }

    /**
     * Page callback
     * Displays the checkout page when admin adds a new order for a customer
     * @param integer $user_id
     */
    public function createOrderCheckout($user_id)
    {
        $this->setAdminModeCheckout('add');
        $this->setUserCheckout($user_id);
        $this->editCheckout();
    }

    /**
     * Page callback
     * Displays the checkout page when admin cloning an order
     * @param integer $order_id
     */
    public function cloneOrderCheckout($order_id)
    {
        $this->setOrderCheckout($order_id);
        $this->setAdminModeCheckout('clone');
        $this->editCheckout();
    }

    /**
     * Sets the current admin mode
     * @param string
     */
    protected function setAdminModeCheckout($mode)
    {
        $this->admin = null;

        if ($this->access('order_add')) {

            if ($mode === 'add') {
                $this->admin = 'add';
            }

            if ($mode === 'clone' && $this->access('order_edit')) {
                $this->admin = 'clone';
            }
        }
    }

    /**
     * Loads a user from the database
     * @param integer $user_id
     */
    protected function setUserCheckout($user_id)
    {
        $this->data_user = array();

        if (!is_numeric($user_id)) {
            $this->outputHttpStatus(403);
        }

        $this->data_user = $this->user->get($user_id);

        if (empty($this->data_user['status'])) {
            $this->outputHttpStatus(404);
        }

        $this->order_user_id = $user_id;
        $this->order_store_id = $this->data_user['store_id'];
    }

    /**
     * Page callback
     * Displays the checkout page
     */
    public function editCheckout()
    {
        $this->setCartContentCheckout();
        $this->setTitleEditCheckout();
        $this->setBreadcrumbEditCheckout();
        $this->controlAccessCheckout();

        $this->setFormDataBeforeCheckout();
        $this->submitEditCheckout();
        $this->setFormDataAfterCheckout();

        $this->setDataFormCheckout();
        $this->outputEditCheckout();
    }

    /**
     * Load and set an order from the database
     * @param integer $order_id
     */
    protected function setOrderCheckout($order_id)
    {
        $this->data_order = $this->order->get($order_id);

        if (empty($this->data_order)) {
            $this->outputHttpStatus(404);
        }

        $this->prepareOrder($this->data_order);

        $this->order_id = $order_id;
        $this->order_user_id = $this->data_order['user_id'];
        $this->order_store_id = $this->data_order['store_id'];
        $this->data_user = $this->user->get($this->data_order['user_id']);
    }

    /**
     * Prepare an array of order data
     * @param array $order
     */
    protected function prepareOrder(array &$order)
    {
        $this->setItemTotalFormatted($order, $this->price);
        $this->setItemTotalFormattedNumber($order, $this->price);
    }

    /**
     * Load and set the current cart content
     * @return array
     */
    protected function setCartContentCheckout()
    {
        $options = array(
            'user_id' => $this->cart_uid,
            'order_id' => $this->order_id,
            'store_id' => $this->order_store_id
        );

        return $this->data_cart = $this->getCart($options);
    }

    /**
     * Sets title on the checkout page
     */
    protected function setTitleEditCheckout()
    {
        if ($this->admin === 'clone') {
            $text = $this->text('Cloning order #@num', array('@num' => $this->data_order['order_id']));
        } else if ($this->admin === 'add') {
            $text = $this->text('Add order for user %name', array('%name' => $this->data_user['name']));
        } else {
            $text = $this->text('Checkout');
        }

        $this->setTitle($text);
    }

    /**
     * Sets breadcrumbs on the checkout page
     */
    protected function setBreadcrumbEditCheckout()
    {
        $breadcrumb = array(
            'url' => $this->url('/'),
            'text' => $this->text('Home')
        );

        $this->setBreadcrumb($breadcrumb);
    }

    /**
     * Controls access to the checkout page
     */
    protected function controlAccessCheckout()
    {
        if (empty($this->data_cart['items'])) {
            $form = $this->render('checkout/form', array('admin' => $this->admin), true);
            $this->setData('checkout_form', $form);
            $this->output('checkout/checkout');
        }
    }

    /**
     * Sets initial form data
     */
    protected function setFormDataBeforeCheckout()
    {
        $this->data_form = array();

        $default_order = $this->getDefaultOrder();
        $order = gplcart_array_merge($default_order, $this->data_order);

        $payment_methods = $this->getPaymentMethodsCheckout();
        $shipping_methods = $this->getShippingMethodsCheckout();

        if (count($payment_methods) == 1) {
            reset($payment_methods);
            $order['payment'] = key($payment_methods);
        }

        if (count($shipping_methods)) {
            reset($shipping_methods);
            $order['shipping'] = key($shipping_methods);
        }

        $this->data_form['order'] = $order;
        $this->data_form['messages'] = array();
        $this->data_form['admin'] = $this->admin;
        $this->data_form['user'] = $this->data_user;

        $this->data_form['statuses'] = $this->order->getStatuses();
        $this->data_form['payment_methods'] = $payment_methods;
        $this->data_form['shipping_methods'] = $shipping_methods;

        $this->data_form['has_dynamic_payment_methods'] = $this->hasDynamicMethods($payment_methods);
        $this->data_form['has_dynamic_shipping_methods'] = $this->hasDynamicMethods($shipping_methods);

        // Price rule calculator requires this data
        $this->data_form['store_id'] = $this->order_store_id;
        $this->data_form['currency'] = $this->data_cart['currency'];

        $this->setFormDataDimensionsCheckout();
    }

    /**
     * Returns an array of default order data
     * @return array
     */
    protected function getDefaultOrder()
    {
        return array(
            'comment' => '',
            'payment' => '',
            'shipping' => '',
            'user_id' => $this->order_user_id,
            'creator' => $this->admin_user_id,
            'store_id' => $this->order_store_id,
            'currency' => $this->data_cart['currency'],
            'status' => $this->order->getStatusInitial(),
            'size_unit' => $this->config('order_size_unit', 'mm'),
            'weight_unit' => $this->config('order_weight_unit', 'g'),
            'data' => array()
        );
    }

    /**
     * Returns an array of enabled payment methods
     * @return array
     */
    protected function getPaymentMethodsCheckout()
    {
        $list = $this->payment->getList(array('status' => true));
        $this->prepareMethodsCheckout($list);
        return $list;
    }

    /**
     * Returns an array of enabled shipping methods
     * @return array
     */
    protected function getShippingMethodsCheckout()
    {
        $list = $this->shipping->getList(array('status' => true));
        $this->prepareMethodsCheckout($list);
        return $list;
    }

    /**
     * Prepare payment and shipping methods
     * @param array $list
     */
    protected function prepareMethodsCheckout(array &$list)
    {
        foreach ($list as &$item) {
            if (isset($item['module']) && isset($item['image'])) {
                $path = GC_DIR_MODULE . "/{$item['module']}/{$item['image']}";
                $item['image'] = $this->url(gplcart_path_relative($path));
            }
        }
    }

    /**
     * Prepares the form data before passing to the templates
     */
    protected function setFormDataAfterCheckout()
    {
        if (empty($this->data_cart)) {
            return null;
        }

        $this->data_form['cart'] = $this->data_cart;

        $this->setFormDataAddressCheckout();

        if (empty($this->data_form['addresses'])) {
            $this->show_shipping_address_form = true;
        }

        $this->data_form['default_payment_method'] = false;
        $this->data_form['default_shipping_method'] = false;
        $this->data_form['same_payment_address'] = $this->same_payment_address;
        $this->data_form['get_payment_methods'] = $this->isPosted('get_payment_methods');
        $this->data_form['get_shipping_methods'] = $this->isPosted('get_shipping_methods');
        $this->data_form['show_login_form'] = $this->show_login_form;
        $this->data_form['show_payment_address_form'] = $this->show_payment_address_form;
        $this->data_form['show_shipping_address_form'] = $this->show_shipping_address_form;
        $this->data_form['show_payment_methods'] = !$this->data_form['has_dynamic_payment_methods'];
        $this->data_form['show_shipping_methods'] = !$this->data_form['has_dynamic_shipping_methods'];

        $submitted = array('order' => $this->getSubmitted());

        $this->data_form['context_templates'] = array(
            'payment' => $this->getPaymentMethodTemplate('context', $submitted['order'], $this->payment),
            'shipping' => $this->getShippingMethodTemplate('context', $submitted['order'], $this->shipping),
        );

        $this->data_form = gplcart_array_merge($this->data_form, $submitted);

        $this->setFormDataRequestServicesCheckout('payment');
        $this->setFormDataRequestServicesCheckout('shipping');
        $this->setFormDataCalculatedCheckout();
        $this->setFormDataPanesCheckout();
    }

    /**
     * Sets the checkout address variables
     */
    protected function setFormDataAddressCheckout()
    {
        $countries = array();

        foreach ((array) $this->country->getList(array('status' => true)) as $code => $country) {
            $countries[$code] = $country['native_name'];
        }

        $default_country = count($countries) == 1 ? key($countries) : '';

        $address = $this->getSubmitted('address', array());

        if (!isset($address['payment']['country'])) {
            $address['payment']['country'] = $default_country;
        }

        if (!isset($address['shipping']['country'])) {
            $address['shipping']['country'] = $default_country;
        }

        $this->data_form['address'] = $address;
        $this->data_form['countries'] = $countries;
        $this->data_form['addresses'] = $this->address->getTranslatedList($this->order_user_id);

        $excessed = $this->address->getExceeded($this->order_user_id, $this->data_form['addresses']);

        $this->data_form['can_add_address'] = empty($excessed);
        $this->data_form['can_save_address'] = empty($excessed) && !empty($this->uid);

        foreach ($address as $type => $fields) {
            $this->data_form['format'][$type] = $this->country->getFormat($fields['country']);
            $this->data_form['states'][$type] = $this->state->getList(array('country' => $fields['country'], 'status' => 1));
            if (empty($this->data_form['states'][$type])) {
                unset($this->data_form['format'][$type]['state_id']);
            }
        }
    }

    /**
     * Sets boolean flags to request dynamic shipping/payment methods
     * @param string $type
     */
    protected function setFormDataRequestServicesCheckout($type)
    {
        $this->data_form["request_{$type}_methods"] = false;

        if (!empty($this->data_form["get_{$type}_methods"])
            || (!empty($this->data_form['order'][$type]) && !empty($this->data_form["has_dynamic_{$type}_methods"]))) {
            $this->data_form["show_{$type}_methods"] = true;
            $this->data_form["request_{$type}_methods"] = true;
        }
    }

    /**
     * Calculates and sets order dimensions
     */
    protected function setFormDataDimensionsCheckout()
    {
        if (!empty($this->data_cart)) {

            $order = $this->data_form['order'];

            // Cart data passed by reference to convert product dimensions and measurement units
            $this->data_form['order']['volume'] = $this->order_dimension->getVolume($order, $this->data_cart);
            $this->data_form['order']['weight'] = $this->order_dimension->getWeight($order, $this->data_cart);
        }
    }

    /**
     * Calculates order total and price components
     */
    protected function setFormDataCalculatedCheckout()
    {
        $result = $this->order->calculate($this->data_form);

        $this->data_form['total'] = $result['total'];
        $this->data_form['total_decimal'] = $result['total_decimal'];
        $this->data_form['total_formatted'] = $result['total_formatted'];
        $this->data_form['price_components'] = $this->prepareOrderComponentsCheckout($result);
    }

    /**
     * Whether the list of shipping/payment methods contains at least one dynamic method
     * @param array $methods
     * @return boolean
     */
    protected function hasDynamicMethods(array $methods)
    {
        foreach ($methods as $method) {
            if (!empty($method['dynamic'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Sets rendered panes
     */
    protected function setFormDataPanesCheckout()
    {
        $panes = array('login', 'review', 'payment_methods',
            'shipping_methods', 'shipping_address', 'payment_address', 'comment', 'action');

        foreach ($panes as $pane) {
            $this->data_form["pane_$pane"] = $this->render("checkout/panes/$pane", $this->data_form);
        }
    }

    /**
     * Handles submitted actions
     */
    protected function submitEditCheckout()
    {
        $this->setSubmitted('order');

        $this->setAddressFormCheckout();
        $this->submitAddAddressCheckout();

        if ($this->isPosted('checkout_login') && empty($this->uid)) {
            $this->show_login_form = true;
        }

        $this->same_payment_address = (bool) $this->getPosted('same_payment_address', true, false, 'bool');

        if ($this->isPosted('update')) {
            $this->setMessage($this->text('Form has been updated'), 'success', false);
        }

        $this->submitLoginCheckout();

        if ($this->isPosted('checkout_anonymous')) {
            $this->show_login_form = false;
        }

        $this->validateCouponCheckout();
        $this->submitCartCheckout();
        $this->submitOrderCheckout();
    }

    /**
     * Controls state of address forms (open/closed)
     */
    protected function setAddressFormCheckout()
    {
        $this->show_payment_address_form = $this->isSubmitted('address.payment');
        $this->show_shipping_address_form = $this->isSubmitted('address.shipping');

        $actions = array(
            'get_states' => true,
            'add_address' => true,
            'cancel_address_form' => false
        );

        foreach ($actions as $field => $action) {
            $value = $this->getPosted($field, '', true, 'string');
            if (isset($value)) {
                $this->{"show_{$value}_address_form"} = $action;
            }
        }
    }

    /**
     * Saves a submitted address
     */
    protected function submitAddAddressCheckout()
    {
        $type = $this->getPosted('save_address', '', true, 'string');

        if (empty($type)) {
            return null;
        }

        $errors = $this->validateAddressCheckout($type);

        if (empty($errors)) {
            $this->addAddressCheckout($type);
            $this->{"show_{$type}_address_form"} = false;
        }
    }

    /**
     * Handles the log in action
     */
    protected function submitLoginCheckout()
    {
        if ($this->isPosted('login')) {
            $this->show_login_form = true;
            $this->loginCheckout();
        }
    }

    /**
     * Log in a customer during checkout
     */
    protected function loginCheckout()
    {
        $result = $this->user_action->login($this->getSubmitted('user'));

        if (isset($result['user'])) {
            $result = $this->cart_action->login($result['user'], $this->data_cart);
        }

        if (!empty($result['user'])) {
            $this->redirect($result['redirect'], $result['message'], $result['severity']);
        }

        $this->setError('login', $result['message']);
    }

    /**
     * Validates a coupon code
     */
    protected function validateCouponCheckout()
    {
        $price_rule_id = $this->getPosted('check_pricerule', null, true, 'integer');

        if (empty($price_rule_id)) {
            return null;
        }

        $code = $this->getSubmitted('data.pricerule_code', '');

        if ($code === '') {
            return null;
        }

        if (!$this->order->priceRuleCodeMatches($price_rule_id, $code)) {
            $this->setError('pricerule_code', $this->text('Invalid code'));
            $this->setMessageFormCheckout('components.danger', $this->text('Invalid code'));
        }
    }

    /**
     * Handles various cart actions
     */
    protected function submitCartCheckout()
    {
        $this->submitCartItemsCheckout();
        $this->deleteCartCheckout();
        $this->updateCartCheckout();
    }

    /**
     * Applies an action to the cart items
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
                $this->updateCartQuantityCheckout($sku, $item);
            }
        }

        if (empty($errors)) {
            $this->setSubmitted('cart.action.update', true);
        } else {
            $this->setMessageFormCheckout('cart.danger', $errors);
        }
    }

    /**
     * Sets an array of messages on the checkout form
     * @param string $key
     * @param string|array $message
     */
    protected function setMessageFormCheckout($key, $message)
    {
        gplcart_array_set($this->data_form['messages'], $key, $this->format($message));
    }

    /**
     * Updates cart quantity
     * @param string $sku
     * @param array $item
     */
    protected function updateCartQuantityCheckout($sku, array $item)
    {
        if (isset($this->data_cart['items'][$sku]['cart_id'])) {
            $cart_id = $this->data_cart['items'][$sku]['cart_id'];
            $this->cart->update($cart_id, array('quantity' => $item['quantity']));
        }
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
            'admin' => !empty($this->admin),
            'user_id' => $this->order_user_id,
            'store_id' => $this->order_store_id
        );

        $this->setSubmitted('update', $item);
        $this->setSubmitted("cart.items.$sku", $item);

        return $this->validateComponent('cart', array('parents' => "cart.items.$sku"));
    }

    /**
     * Deletes an item from the cart
     */
    protected function deleteCartCheckout()
    {
        $cart_id = $this->getSubmitted('cart.action.delete');

        if (!empty($cart_id)) {
            $this->setSubmitted('cart.action.update', true);
            $this->cart->delete($cart_id);
        }
    }

    /**
     * Updates the current cart
     */
    protected function updateCartCheckout()
    {
        if ($this->isSubmitted('cart.action.update')) {
            $this->setCartContentCheckout();
        }
    }

    /**
     * Saves an order to the database
     */
    protected function submitOrderCheckout()
    {
        if (!$this->isPosted('save')) {
            return null;
        }

        $errors = array();

        foreach (array('payment', 'shipping') as $type) {

            $address_errors = $this->validateAddressCheckout($type);

            if (!empty($address_errors)) {
                $errors = gplcart_array_merge($errors, $address_errors);
            }

            if (empty($address_errors)) {
                $this->addAddressCheckout($type);
            }
        }

        $order_errors = $this->validateOrderCheckout();
        $errors = gplcart_array_merge($errors, $order_errors);

        if (empty($errors)) {
            $this->addOrderCheckout();
        } else {
            $this->setError(null, $errors);
        }
    }

    /**
     * Validates a submitted address
     * @param string $type
     * @return array
     */
    protected function validateAddressCheckout($type)
    {
        if ($this->{"show_{$type}_address_form"}) {
            $this->setSubmitted("address.{$type}.user_id", $this->order_user_id);
            return $this->validateComponent('address', array('parents' => "address.$type"));
        }

        return array();
    }

    /**
     * Validates an array of submitted data before creating an order
     * @return array
     */
    protected function validateOrderCheckout()
    {
        if ($this->same_payment_address) {
            $this->unsetSubmitted('address.payment');
        }

        $this->setSubmitted('update', array());
        $this->setSubmitted('store_id', $this->store_id);
        $this->setSubmitted('user_id', $this->order_user_id);
        $this->setSubmitted('creator', $this->admin_user_id);

        if ($this->admin) {
            $this->setSubmitted('status', $this->order->getStatusInitial());
        }

        return $this->validateComponent('order');
    }

    /**
     * Adds a submitted address
     * @param string $type
     */
    protected function addAddressCheckout($type)
    {
        $submitted = $this->getSubmitted("address.$type");

        if ($this->{"show_{$type}_address_form"} && !empty($submitted)) {

            $address_id = $this->address->add($submitted);
            $this->setSubmitted("{$type}_address", $address_id);

            foreach ($this->address->getExceeded($this->order_user_id) as $address) {
                $this->address->delete($address['address_id']);
            }
        }
    }

    /**
     * Adds a new order
     */
    protected function addOrderCheckout()
    {
        $submitted = $this->getSubmittedOrderCheckout();
        $result = $this->order_action->add($submitted, array('admin' => $this->admin));
        $this->finishOrderCheckout($result);
    }

    /**
     * Performs final tasks after an order has been created
     * @param array $result
     */
    protected function finishOrderCheckout(array $result)
    {
        if ($this->admin === 'add') {
            $this->finishAddOrderCheckout($result);
        } else if ($this->admin === 'clone') {
            $this->finishCloneOrderCheckout($result);
        }

        $this->redirect($result['redirect'], $result['message'], $result['severity']);
    }

    /**
     * Performs final tasks after an order has been added for a user
     * @param array $result
     */
    protected function finishAddOrderCheckout(array $result)
    {
        if (!empty($result['order']['order_id'])) {

            $vars = array(
                '@num' => $result['order']['order_id'],
                '@name' => $result['order']['customer_name'],
                '@status' => $this->order->getStatusName($result['order']['status'])
            );

            $message = $this->text('Order #@num has been created for user @name. Order status: @status', $vars);
            $this->redirect("admin/sale/order/{$result['order']['order_id']}", $message, 'success');
        }
    }

    /**
     * Performs final tasks after an order has been cloned
     * @param array $result
     */
    protected function finishCloneOrderCheckout(array $result)
    {
        if (!empty($result['order']['order_id'])) {

            $log = array(
                'user_id' => $this->uid,
                'order_id' => $this->data_order['order_id'],
                'text' => $this->text('Cloned into order #@num', array('@num' => $result['order']['order_id']))
            );

            $this->order_history->add($log);

            $vars = array(
                '@num' => $this->data_order['order_id'],
                '@url' => $this->url("admin/sale/order/{$this->order_id}"),
                '@status' => $this->order->getStatusName($result['order']['status'])
            );

            $message = $this->text('Order has been cloned from order <a href="@url">@num</a>. Order status: @status', $vars);
            $this->redirect("admin/sale/order/{$result['order']['order_id']}", $message, 'success');
        }
    }

    /**
     * Returns an array of prepared submitted order data
     * @return array
     */
    protected function getSubmittedOrderCheckout()
    {
        $submitted = $this->getSubmitted();
        $submitted += $this->data_form['order'];
        $submitted['cart'] = $this->data_cart;

        $submitted['data']['user'] = array(
            'ip' => $this->server->remoteAddr(),
            'agent' => $this->server->userAgent()
        );

        if (empty($this->admin)) {
            return $submitted;
        }

        // Convert decimal prices from inputs in admin mode
        $submitted['total'] = $this->price->amount($submitted['total'], $submitted['currency']);

        if (empty($submitted['data']['components'])) {
            return $submitted;
        }

        $this->prepareSubmittedOrderComponentsCheckout($submitted);
        return $submitted;
    }

    /**
     * Prepare submitted order components
     * @param array $submitted
     */
    protected function prepareSubmittedOrderComponentsCheckout(array &$submitted)
    {
        foreach ($submitted['data']['components'] as $id => &$component) {

            if (!isset($component['price'])) {
                continue;
            }

            if (empty($component['price'])) {
                unset($submitted['data']['components'][$id]);
                continue;
            }

            $component['currency'] = $submitted['currency'];
            $component['price'] = $this->price->amount($component['price'], $submitted['currency']);
        }
    }

    /**
     * Prepares an array of price rule components
     * @param array $calculated
     * @return array
     */
    protected function prepareOrderComponentsCheckout($calculated)
    {
        $component_types = $this->order->getComponentTypes();

        $components = array();
        foreach ($calculated['components'] as $type => $component) {

            $components[$type] = array(
                'price' => $component['price'],
                'price_decimal' => $this->price->decimal($component['price'], $calculated['currency']),
                'price_formatted' => $this->price->format($component['price'], $calculated['currency'])
            );

            if (empty($component['rule'])) {
                $components[$type]['name'] = $component_types[$type];
                continue;
            }

            $components[$type]['rule'] = $component['rule'];
            $components[$type]['name'] = $component['rule']['name'];
        }

        return $components;
    }

    /**
     * Sets form on the checkout page
     */
    protected function setDataFormCheckout()
    {
        $form = $this->render('checkout/form', $this->data_form, true);

        if ($this->isAjax()) {
            $this->response->outputHtml($form);
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

}
