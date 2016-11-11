<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers;

use core\classes\Tool;
use core\models\State as ModelsState;
use core\models\Order as ModelsOrder;
use core\models\Address as ModelsAddress;
use core\models\Country as ModelsCountry;
use core\models\Payment as ModelsPayment;
use core\models\Shipping as ModelsShipping;
use core\controllers\Controller as FrontendController;

/**
 * Handles incoming requests and outputs data related to checkout process
 */
class Checkout extends FrontendController
{

    /**
     * Order model instance
     * @var \core\models\Order $order
     */
    protected $order;

    /**
     * Address model instance
     * @var \core\models\Address $address
     */
    protected $address;

    /**
     * Country model instance
     * @var \core\models\Country $country
     */
    protected $country;

    /**
     * State model instance
     * @var \core\models\State $state
     */
    protected $state;

    /**
     * Shipping model instance
     * @var \core\models\Shipping $shipping
     */
    protected $shipping;

    /**
     * Payment model instance
     * @var \core\models\Payment $payment
     */
    protected $payment;

    /**
     * Current state of address form
     * @var boolean
     */
    protected $address_form;

    /**
     * Current state of login form
     * @var bool
     */
    protected $login_form;

    /**
     * Whether the cart has been updated
     * @var boolean
     */
    protected $cart_updated;

    /**
     * Cart content for the current user
     * @var array
     */
    protected $cart_content;

    /**
     * Current country code
     * @var string
     */
    protected $country_code;

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
     * An array of order which is updating
     * @var array
     */
    protected $order_data;

    /**
     * An array of customer user data
     * @var array
     */
    protected $order_user_data;

    /**
     * Order user id. Greater than 0 when editing an order
     * @var integer
     */
    protected $order_id;

    /**
     * Order customer ID. Default to cart UID
     * @var string|integer 
     */
    protected $order_user_id;

    /**
     * Order store ID. Default to the current store
     * @var integer
     */
    protected $order_store_id;

    /**
     * Template data array
     * @var array
     */
    protected $form_data;

    /**
     * Constructor
     * @param ModelsCountry $country
     * @param ModelsState $state
     * @param ModelsAddress $address
     * @param ModelsOrder $order
     * @param ModelsShipping $shipping
     * @param ModelsPayment $payment
     */
    public function __construct(ModelsCountry $country, ModelsState $state,
            ModelsAddress $address, ModelsOrder $order,
            ModelsShipping $shipping, ModelsPayment $payment)
    {
        parent::__construct();

        $this->order = $order;
        $this->state = $state;
        $this->address = $address;
        $this->country = $country;
        $this->payment = $payment;
        $this->shipping = $shipping;

        $this->order_id = 0;
        
        $this->form_data = array();
        $this->order_data = array();
        $this->cart_content = array();
        $this->order_user_data = array();

        $this->login_form = false;
        $this->address_form = false;
        $this->cart_updated = false;

        $this->order_user_id = $this->cart_uid;
        $this->order_store_id = $this->store_id;
        $this->country_code = $this->country->getDefault();

        $this->admin_user_id = $this->uid;
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
     * @param integer $user_id
     */
    protected function setUserCheckout($user_id)
    {
        if (!is_numeric($user_id)) {
            $this->outputError(403);
        }

        $user = $this->user->get($user_id);

        if (empty($user['status'])) {
            $this->outputError(404);
        }

        $this->order_user_data = $user;
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
            $this->outputError(404);
        }

        $this->order_data = $order;
        $this->order_id = $order_id;

        $this->cart_uid = $order['user_id'];
        $this->order_user_id = $order['user_id'];
        $this->order_store_id = $order['store_id'];
        $this->order_user_data = $this->user->get($order['user_id']);
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

        $this->cart_content = $this->cart->getContent($data);
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
        if (empty($this->cart_content['items'])) {

            $data = array('admin' => $this->admin);

            $form = $this->render('checkout/form', $data);
            $this->setData('checkout_form', $form);
            $this->output('checkout/checkout');
        }
    }

    /**
     *
     * @return type
     */
    protected function setFormDataBeforeCheckout()
    {
        $default_order = array(
            'user_id' => $this->order_user_id,
            'creator' => $this->admin_user_id,
            'store_id' => $this->order_store_id,
            'status' => $this->order->getInitialStatus(),
            'currency' => $this->cart_content['currency']
        );

        // Override with existing order values if we're editing the order
        $order = Tool::merge($default_order, $this->order_data);

        $this->form_data['order'] = $order;
        $this->form_data['messages'] = array();
        $this->form_data['settings'] = array();
        $this->form_data['admin'] = $this->admin;
        $this->form_data['user'] = $this->order_user_data;
        
        $this->form_data['statuses'] = $this->order->getStatuses();
        $this->form_data['payment_methods'] = $this->payment->getList(true);
        $this->form_data['shipping_methods'] = $this->shipping->getList(true);
        $this->form_data['addresses'] = $this->address->getTranslatedList($this->order_user_id);
    }
    
    /**
     * Prepares form data before passing them to templates
     * @return null
     */
    protected function setFormDataAfterCheckout()
    {
        if (empty($this->cart_content)) {
            return null; // Required
        }

        $this->form_data['address'] = $this->getSubmitted('address', array());

        $this->form_data['login_form'] = $this->login_form;
        $this->form_data['address_form'] = $this->address_form;
        $this->form_data['country_code'] = $this->country_code;

        $options = array('country' => $this->country_code, 'status' => 1);
        $this->form_data['states'] = $this->state->getList($options);

        $this->form_data['countries'] = $this->country->getNames(true);
        $this->form_data['cart'] = $this->prepareCart($this->cart_content);
        $this->form_data['format'] = $this->country->getFormat($this->country_code, true);

        if (empty($this->form_data['states'])) {
            unset($this->form_data['format']['state_id']);
        }

        $this->calculateCheckout();
        
        $this->form_data['pane_admin'] = $this->render('checkout/panes/admin', $this->form_data);
        $this->form_data['pane_login'] = $this->render('checkout/panes/login', $this->form_data);
        $this->form_data['pane_review'] = $this->render('checkout/panes/review', $this->form_data);
        $this->form_data['settings'] = json_encode($this->form_data['settings'], JSON_FORCE_OBJECT);

        $this->form_data['pane_payment_methods'] = $this->render('checkout/panes/payment_methods', $this->form_data);
        $this->form_data['pane_shipping_methods'] = $this->render('checkout/panes/shipping_methods', $this->form_data);
        $this->form_data['pane_shipping_address'] = $this->render('checkout/panes/shipping_address', $this->form_data);
        return null;
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

        if ($this->isPosted('checkout_login') && empty($this->uid)) {
            $this->login_form = true;
        }

        $this->submitLoginCheckout();

        if ($this->isPosted('checkout_anonymous')) {
            $this->login_form = false;
        }

        $this->validateCouponCheckout();

        if ($this->hasErrors('order', false)) {
            return null;
        }

        $this->submitCartCheckout();
        $this->submitOrderCheckout();
        return null;
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
        $user = $this->getSubmitted('user');
        $result = $this->user->login($user);

        if (isset($result['user'])) {
            $result = $this->cart->login($result['user'], $this->cart_content);
        }

        if (!empty($result['user'])) {
            $this->redirect($result['redirect'], $result['message'], $result['severity']);
        }

        $this->setError('login', $result['message']);
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

        if ($this->order->codeMatches($price_rule_id, $code)) {
            $this->setMessageFormCheckout('components.success', $this->text('Code is valid'));
            return null;
        }

        $this->setError('pricerule_code', $this->text('Invalid code'));
        $this->setMessageFormCheckout('components.warning', $this->text('Invalid code'));
        return null;
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
     * @return boolean
     */
    protected function submitCartItemsCheckout()
    {
        $items = $this->getSubmitted('cart.items');

        if (empty($items)) {
            return false;
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
            return false;
        }

        $this->setSubmitted('cart.action.update', true);
        return true;
    }

    /**
     * Sets an array of messages to the checkout form
     * @param string $key
     * @param string|array $message
     */
    protected function setMessageFormCheckout($key, $message)
    {
        $messages = (array) $message;

        $flatten = Tool::flattenArray($messages);
        $string = implode('<br>', array_unique($flatten));
        Tool::setArrayValue($this->form_data['messages'], $key, $string);
    }

    /**
     * Updates cart quantity
     * @param string $sku
     * @param integer $quantity
     * @return bool
     */
    protected function updateCartQuantityCheckout($sku, $quantity)
    {
        $cart_id = $this->cart_content['items'][$sku]['cart_id'];
        return $this->cart->update($cart_id, array('quantity' => $quantity));
    }

    /**
     * Validates a cart item and returns foun errors
     * @param string $sku
     * @param array $item
     * @return array
     */
    protected function validateCartItemCheckout($sku, $item)
    {
        $item += array(
            'sku' => $sku,
            'user_id' => $this->order_user_id,
            'store_id' => $this->order_store_id
        );

        $this->setSubmitted("cart.items.$sku", $item);

        $product = $this->cart_content['items'][$sku]['product'];

        $this->addValidator("cart.items.$sku.quantity", array(
            'numeric' => array(),
            'length' => array('min' => 1, 'max' => 2)
        ));

        if (!$this->admin) {
            $this->addValidator("cart.items.$sku", array(
                'cart_limits' => array(
                    'increment' => false, 'data' => $product)
            ));
        }

        // Do not pass product data here
        // to avoid rewriting by the next validators
        return $this->setValidators();
    }

    /**
     * Moves a cart item to the wishlist
     * @return null|array
     */
    protected function moveCartWishlistCheckout()
    {
        $sku = $this->getSubmitted('cart.action.wishlist');

        if (empty($sku)) {
            return null;
        }

        $options = array(
            'user_id' => $this->order_user_id,
            'store_id' => $this->order_store_id
        );

        $result = $this->cart->moveToWishlist($options + array('sku' => $sku));

        if (empty($result['wishlist_id'])) {
            return null;
        }

        // Add JSON settings to update cart/wishlist quantities
        $this->form_data['settings']['quantity']['cart'] = $this->cart->getQuantity($options, 'total');
        $this->form_data['settings']['quantity']['wishlist'] = $this->wishlist->getList($options + array('count' => true));

        $this->setMessageFormCheckout('cart.success', $result['message']);

        $this->setSubmitted('cart.action.update', true);
        return $result;
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
        if (!$this->isSubmitted('cart.action.update')) {
            return null;
        }

        $this->setCartContentCheckout();

        if ($this->request->isAjax() || $this->isPosted('save')) {
            return null;
        }

        $message = $this->text('Cart has been updated');
        $this->redirect('', $message, 'success');
        return null;
    }

    /**
     * Saves an order to the database
     * @return null|void
     */
    protected function submitOrderCheckout()
    {
        if (!$this->isPosted('save')) {
            return null;
        }

        $this->validateAddressCheckout();
        $this->validateOrderCheckout();

        if ($this->hasErrors('order', false)) {
            return null;
        }

        $this->addAddressCheckout();

        // Add / update an order
        $submitted = $this->getSubmitted();
        $submitted += $this->form_data['order'];
        $submitted['cart'] = $this->cart_content;

        if (empty($this->order_data['order_id'])) {
            return $this->addOrderCheckout($submitted);
        }

        return $this->updateOrderCheckout($this->order_data['order_id'], $submitted);
    }

    /**
     * Validates a submitted address
     * @return null|array
     */
    protected function validateAddressCheckout()
    {
        if (empty($this->address_form)) {
            return null;
        }

        $this->setSubmitted('address.user_id', $this->order_user_id);
        return $this->validate('address', array('field' => 'address'));
    }

    /**
     * Validates an array of submitted values before creating an order
     */
    protected function validateOrderCheckout()
    {
        $this->validate('order');
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
        $result = $this->order->submit($submitted, $this->cart_content, $options);
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
        $this->form_data = Tool::merge($this->form_data, $submitted);

        $result = $this->order->calculate($this->cart_content, $this->form_data);
        $this->form_data['total_formatted'] = $this->price->format($result['total'], $result['currency']);
        $this->form_data['total'] = $result['total'];

        $components = $this->prepareOrderComponentsCheckout($result, $this->form_data);
        $this->form_data['price_components'] = $components;
    }

    /**
     * Prepares an array of price rule components
     * @param array $calculated
     * @param array $data
     * @return array
     */
    protected function prepareOrderComponentsCheckout(array $calculated,
            array $data)
    {
        $payment_methods = $this->shipping->getList();
        $shipping_methods = $this->payment->getList();

        $components = array();
        foreach ($calculated['components'] as $type => $component) {

            if (isset(${$type . '_methods'}) && isset(${$type . '_methods'}[$data[$type]['name']])) {
                $name = ${$type . '_methods'}[$data[$type]['name']];
            }

            if (isset($component['rule']['name'])) {
                $name = $component['rule']['name'];
            }

            if (!isset($name)) {
                $name = $this->text($type);
            }

            $components[$type] = array(
                'name' => $name,
                'price' => $component['price'],
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
        $form = $this->render('checkout/form', $this->form_data);

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
        $order = $this->getOrderCheckout($order_id);
        $this->controlAccessCompleteCheckout($order);

        $message = $this->getCompleteMessageCheckout($order);
        $templates = $this->getCompleteTemplatesCheckout($order);

        $this->setData('complete_message', $message);
        $this->setData('templates', $templates);

        $this->setTitleCompleteCheckout($order);
        $this->setBreadcrumbCompleteCheckout($order);
        $this->outputCompleteCheckout();
    }

    /**
     * Returns an array of rendered templates
     * provided by payment/shipping methods and used on the order complete page
     * @param array $order
     * @return array
     */
    protected function getCompleteTemplatesCheckout(array $order)
    {
        $templates = array();
        foreach (array('payment', 'shipping') as $type) {

            $method = $this->{$type}->get($order[$type]);

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
                'order' => $order,
                'method' => $method,
                'settings' => $settings
            );

            $templates[$type] = $this->render($template, $options);
        }

        return $templates;
    }

    /**
     * Ensures the order belongs to the current cart user
     * @param array $order
     */
    protected function controlAccessCompleteCheckout(array $order)
    {
        if (strcmp((string) $order['user_id'], $this->order_user_id) !== 0) {
            $this->outputError(403);
        }

        if ($order['status'] !== $this->order->getInitialStatus()) {
            $this->outputError(403);
        }
    }

    /**
     * Returns a complete order message
     * @param array $order
     * @return string
     */
    protected function getCompleteMessageCheckout(array $order)
    {
        return $this->order->getCompleteMessage($order);
    }

    /**
     * Returns an order
     * @param integer $order_id
     * @return array
     */
    protected function getOrderCheckout($order_id)
    {
        $order = $this->order->get($order_id);

        if (empty($order['order_id'])) {
            $this->outputError(404);
        }

        $order['total_formatted'] = $this->price->format($order['total'], $order['currency']);
        $order['total_formatted_decimal'] = $this->price->filterDecimal($order['total_formatted']);
        return $order;
    }

    /**
     * Sets titles on the complete order page
     * @param array $order
     */
    protected function setTitleCompleteCheckout(array $order)
    {
        $title = $this->text('Order #@num. Checkout completed', array(
            '@num' => $order['order_id']));

        $this->setTitle($title);
    }

    /**
     * Sets breadcrumbs on the complete order page
     * @param array $order
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
