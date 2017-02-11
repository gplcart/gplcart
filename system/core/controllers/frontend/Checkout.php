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
     * Current state of shipping address form
     * @var boolean
     */
    protected $shipping_address_form = false;

    /**
     * Current state of payment address form
     * @var boolean
     */
    protected $payment_address_form = false;

    /**
     * Whether payment address should be provided
     * @var bool
     */
    protected $has_payment_address = false;

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
     * Displays the checkout page when admin adds a new order for a user
     * @param integer $user_id
     */
    public function createOrderCheckout($user_id)
    {
        $this->setAdminModeCheckout('add');
        $this->setUserCheckout($user_id);
        $this->editCheckout();
    }

    /**
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
     * Loads an order from the database
     * @param integer $order_id
     */
    protected function setOrderCheckout($order_id)
    {
        $order = $this->order->get($order_id);

        if (empty($order)) {
            $this->outputHttpStatus(404);
        }

        $order['status'] = $this->order->getInitialStatus();

        $this->data_order = $order;
        $this->order_id = $order_id;
        $this->order_user_id = $order['user_id'];
        $this->order_store_id = $order['store_id'];
        $this->data_user = $this->user->get($order['user_id']);
    }

    /**
     * Loads the current cart content
     * @return array
     */
    protected function setCartContentCheckout()
    {
        $data = array(
            'user_id' => $this->cart_uid,
            'order_id' => $this->order_id,
            'store_id' => $this->order_store_id
        );

        return $this->data_cart = $this->cart->getContent($data);
    }

    /**
     * Sets title on the checkout page
     */
    protected function setTitleEditCheckout()
    {
        $text = $this->text('Checkout');

        switch ($this->admin) {
            case 'clone':
                $vars = array('@num' => $this->data_order['order_id']);
                $text = $this->text('Cloning order #@num', $vars);
                break;
            case 'add':
                $vars = array('@name' => $this->data_user['name']);
                $text = $this->text('Add order for user @name', $vars);
                break;
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
            $form = $this->render('checkout/form', array('admin' => $this->admin));
            $this->setData('checkout_form', $form);
            $this->output('checkout/checkout');
        }
    }

    /**
     * Sets initial form data
     */
    protected function setFormDataBeforeCheckout()
    {
        $default_order = array(
            'comment' => '',
            'user_id' => $this->order_user_id,
            'creator' => $this->admin_user_id,
            'store_id' => $this->order_store_id,
            'currency' => $this->data_cart['currency'],
            'status' => $this->order->getInitialStatus()
        );

        $order = gplcart_array_merge($default_order, $this->data_order);

        $this->data_form['order'] = $order;
        $this->data_form['messages'] = array();
        $this->data_form['admin'] = $this->admin;
        $this->data_form['user'] = $this->data_user;

        $this->data_form['statuses'] = $this->order->getStatuses();
        $this->data_form['payment_methods'] = $this->payment->getList(true);
        $this->data_form['shipping_methods'] = $this->shipping->getList(true);

        // Price rule calculator requires this data
        $this->data_form['store_id'] = $this->order_store_id;
        $this->data_form['currency'] = $this->data_cart['currency'];
    }

    /**
     * Prepares form data before passing to templates
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

        if (!isset($address['payment']['country'])) {
            $address['payment']['country'] = $default_country;
        }

        if (!isset($address['shipping']['country'])) {
            $address['shipping']['country'] = $default_country;
        }

        $this->data_form['address'] = $address;
        $this->data_form['countries'] = $countries;
        $this->data_form['login_form'] = $this->login_form;
        $this->data_form['has_payment_address'] = $this->has_payment_address;
        $this->data_form['payment_address_form'] = $this->payment_address_form;
        $this->data_form['shipping_address_form'] = $this->shipping_address_form;

        $this->data_form['cart'] = $this->prepareCart($this->data_cart);
        $this->data_form['addresses'] = $this->address->getTranslatedList($this->order_user_id);

        $excess = $this->address->getExcess($this->order_user_id, $this->data_form['addresses']);

        $this->data_form['can_add_address'] = empty($excess);
        $this->data_form['can_save_address'] = empty($excess) && !empty($this->uid);

        foreach ($address as $type => $fields) {
            $this->data_form['format'][$type] = $this->country->getFormat($fields['country']);
            $this->data_form['states'][$type] = $this->state->getList(array('country' => $fields['country'], 'status' => 1));

            if (empty($this->data_form['states'][$type])) {
                unset($this->data_form['format'][$type]['state_id']);
            }
        }

        $this->calculateCheckout();
        $this->setFormDataPanesOrder();
    }

    /**
     * Sets rendered panes
     */
    protected function setFormDataPanesOrder()
    {
        $panes = array('login', 'review', 'payment_methods',
            'shipping_methods', 'shipping_address', 'payment_address');

        foreach ($panes as $pane) {
            $this->data_form["pane_$pane"] = $this->render("checkout/panes/$pane", $this->data_form);
        }
    }

    /**
     * Handles submitted actions
     */
    protected function submitCheckout()
    {
        $this->setSubmitted('order');

        $this->setAddressFormCheckout();

        $this->submitAddAddressCheckout();

        if ($this->isPosted('checkout_login') && empty($this->uid)) {
            $this->login_form = true;
        }

        if ($this->isPosted('payment_address')) {
            $this->has_payment_address = true;
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
     * Controls state of address forms (open/closed)
     */
    protected function setAddressFormCheckout()
    {
        $this->payment_address_form = $this->isSubmitted('address.payment');
        $this->shipping_address_form = $this->isSubmitted('address.shipping');

        $actions = array(
            'add_address' => true,
            'get_states' => true,
            'cancel_address_form' => false
        );

        foreach ($actions as $field => $action) {
            $value = $this->request->post($field);
            if (isset($value)) {
                $this->{"{$value}_address_form"} = $action;
            }
        }
    }

    /**
     * Saves a submitted address
     * @return null
     */
    protected function submitAddAddressCheckout()
    {
        $type = $this->request->post('save_address');

        if (empty($type)) {
            return null;
        }

        $errors = $this->validateAddressCheckout($type);

        if (empty($errors)) {
            $this->addAddressCheckout($type);
            $this->{"{$type}_address_form"} = false;
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
     * Log in a customer during checkout
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
            $this->setMessageFormCheckout('components.danger', $this->text('Invalid code'));
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
            'admin' => !empty($this->admin),
            'user_id' => $this->order_user_id,
            'store_id' => $this->order_store_id
        );

        $this->setSubmitted('update', $item);
        $this->setSubmitted("cart.items.$sku", $item);

        return $this->validate('cart', array('parents' => "cart.items.$sku"));
    }

    /**
     * Moves a cart item to the wishlist
     * @return null
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
     */
    protected function deleteCartCheckout()
    {
        $cart_id = $this->getSubmitted('cart.action.delete');

        if (!empty($cart_id)) {
            $this->setSubmitted('cart.action.update', true);
            $this->cart->delete(array('cart_id' => $cart_id));
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
     * @return null
     */
    protected function submitOrderCheckout()
    {
        if (!$this->isPosted('save')) {
            return null;
        }

        $order_errors = $this->validateOrderCheckout();

        foreach (array('payment', 'shipping') as $type) {
            $address_errors = $this->validateAddressCheckout($type);

            if (!empty($address_errors)) {
                $order_errors = gplcart_array_merge($order_errors, $address_errors);
            }

            if ($this->{"{$type}_address_form"}) {
                unset($order_errors["{$type}_address"]);
            }

            if (empty($address_errors)) {
                $this->addAddressCheckout($type);
            }
        }

        if (empty($order_errors)) {
            $this->addOrderCheckout();
        } else {
            $this->setError(null, $order_errors);
        }
    }

    /**
     * Validates a submitted address
     * @param string $type
     * @return array
     */
    protected function validateAddressCheckout($type)
    {
        if ($this->{"{$type}_address_form"}) {
            $this->setSubmitted("address.{$type}.user_id", $this->order_user_id);
            return $this->validate('address', array('parents' => "address.$type"));
        }

        return array();
    }

    /**
     * Validates an array of submitted data before creating an order
     * @return array
     */
    protected function validateOrderCheckout()
    {
        if (!$this->has_payment_address) {
            $this->unsetSubmitted('address.payment');
        }

        $this->setSubmitted('update', array());
        $this->setSubmitted('store_id', $this->store_id);
        $this->setSubmitted('user_id', $this->order_user_id);
        $this->setSubmitted('creator', $this->admin_user_id);

        return $this->validate('order');
    }

    /**
     * Adds a submitted address
     * @param string $type
     */
    protected function addAddressCheckout($type)
    {
        $submitted = $this->getSubmitted("address.$type");

        if ($this->{"{$type}_address_form"} && !empty($submitted)) {
            $address_id = $this->address->add($submitted);
            $this->setSubmitted("{$type}_address", $address_id);
            $this->address->controlLimit($this->order_user_id);
        }
    }

    /**
     * Adds a new order
     */
    protected function addOrderCheckout()
    {
        $submitted = $this->getSubmittedOrderCheckout();

        $result = $this->order->submit($submitted, $this->data_cart, array('admin' => $this->admin));
        $this->finishOrderCheckout($result, $submitted);
    }

    /**
     * Performs final tasks after an order has been created
     * @param array $result
     * @param array $submitted
     */
    protected function finishOrderCheckout(array $result, array $submitted)
    {
        if (empty($this->admin)) {
            $this->redirect($result['redirect'], $result['message'], $result['severity']);
        }

        $this->finishAddOrderCheckout($result);
        $this->finishCloneOrderCheckout($result, $submitted);
    }

    /**
     * Performs final tasks after an order has been added for a user
     * @param array $result
     * @return null
     */
    protected function finishAddOrderCheckout(array $result)
    {
        if ($this->admin !== 'add') {
            return null;
        }

        $vars = array(
            '@num' => $result['order']['order_id'],
            '@name' => $result['order']['user_name'],
            '@status' => $this->order->getStatusName($result['order']['status'])
        );

        $message = $this->text('Order #@num has been created for user @name. Order status: @status', $vars);
        $this->redirect("admin/sale/order/{$result['order']['order_id']}", $message, 'success');
    }

    /**
     * Performs final tasks after an order has been cloned
     * @param array $result
     * @param array $submitted
     * @return null
     */
    protected function finishCloneOrderCheckout(array $result, array $submitted)
    {
        if ($this->admin !== 'clone') {
            return null;
        }

        $log = array(
            'user_id' => $this->uid,
            'order_id' => $this->data_order['order_id'],
            'text' => $this->text('Cloned into order #@num', array('@num' => $result['order']['order_id']))
        );

        $this->order->addLog($log);

        $vars = array(
            '@num' => $this->data_order['order_id'],
            '@url' => $this->url("admin/sale/order/{$this->order_id}"),
            '@status' => $this->order->getStatusName($submitted['status'])
        );

        $message = $this->text('Order has been cloned from order <a href="@url">@num</a>. Order status: @status', $vars);
        $this->redirect("admin/sale/order/{$result['order']['order_id']}", $message, 'success');
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

        if (empty($this->admin)) {
            return $submitted;
        }

        $submitted['total'] = $this->price->amount($submitted['total'], $submitted['currency']);

        if (empty($submitted['data']['components'])) {
            return $submitted;
        }

        foreach ($submitted['data']['components'] as $id => &$price) {
            if ($price == 0) {
                unset($submitted['data']['components'][$id]);
                continue;
            }
            $price = $this->price->amount($price, $submitted['currency']);
        }

        return $submitted;
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
        $this->data_form['price_components'] = $this->prepareOrderComponentsCheckout($result);

        $this->attachItemTotalDecimal($this->data_form);
        $this->attachItemTotalFormatted($this->data_form);
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
            $components[$type] = array(
                'rule' => $component['rule'],
                'price' => $component['price'],
                'name' => $component['rule']['name'],
                'price_decimal' => $this->price->decimal($component['price'], $calculated['currency']),
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
        $this->controlAccessCompleteCheckout();

        $this->setTitleCompleteCheckout();
        $this->setBreadcrumbCompleteCheckout();

        $this->setData('templates', $this->getCompleteTemplatesCheckout());
        $this->setData('complete_message', $this->getCompleteMessageCheckout());

        $this->outputCompleteCheckout();
    }

    /**
     * Returns an array of rendered templates provided by payment/shipping methods
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
     * Controls access to the complete order page
     */
    protected function controlAccessCompleteCheckout()
    {
        if ($this->data_order['user_id'] !== $this->cart_uid) {
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
        $title = $this->text('Checkout completed', $vars);
        $this->setTitle($title);
    }

    /**
     * Sets breadcrumbs on the complete order page
     */
    protected function setBreadcrumbCompleteCheckout()
    {
        $breadcrumb = array(
            'url' => $this->url('/'),
            'text' => $this->text('Home')
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
