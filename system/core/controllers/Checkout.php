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
    protected $cart_content = array();

    /**
     * Current country code
     * @var string
     */
    protected $country_code;

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

        $this->cart_content = $this->cart->getByUser($this->cart_uid, $this->store_id);

        $this->cart_updated = false;
        $this->login_form = false;
        $this->address_form = false;
        $this->form_data = array();

        $this->country_code = $this->country->getDefault();
    }

    /**
     * Displays the checkout page
     */
    public function indexCheckout()
    {
        $this->setTitleIndexCheckout();
        $this->setBreadcrumbIndexCheckout();

        $this->controlAccessCheckout();

        $this->setFormDataBeforeCheckout();

        $this->submitCheckout();

        $this->setFormDataAfterCheckout();

        $this->setDataFormCheckout();

        $this->outputCheckout();
    }

    /**
     * Sets titles on the checkout page
     */
    protected function setTitleIndexCheckout()
    {
        $this->setTitle($this->text('Checkout'));
    }

    /**
     * Sets breadcrumbs on the checkout page
     */
    protected function setBreadcrumbIndexCheckout()
    {
        $this->setBreadcrumb(array('text' => $this->text('Home'), 'url' => $this->url('/')));
    }

    /**
     * Controls access to the checkout page
     */
    protected function controlAccessCheckout()
    {
        if (empty($this->cart_content['items'])) {
            $form = $this->render('checkout/form');
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
        $this->form_data['order'] = array(
            'user_id' => $this->cart_uid,
            'store_id' => $this->store_id,
            'status' => $this->order->getInitialStatus(),
            'currency' => $this->cart_content['currency']
        );

        $this->form_data['messages'] = array();
        $this->form_data['settings'] = array();
        $this->form_data['addresses'] = $this->address->getTranslatedList($this->cart_uid);

        $this->form_data['payment_methods'] = $this->payment->getMethods(true);
        $this->form_data['shipping_methods'] = $this->shipping->getMethods(true);
    }

    /**
     * 
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
            return;
        }

        $this->submitCartCheckout();
        $this->submitOrderCheckout();
    }

    /**
     * 
     * @return type
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
     */
    protected function validateCouponCheckout()
    {
        $price_rule_id = (int) $this->request->post('check_pricerule');

        if (empty($price_rule_id)) {
            return;
        }

        $code = $this->getSubmitted('data.pricerule_code', '');

        if ($code === '') {
            return;
        }

        if ($this->order->codeMatches($price_rule_id, $code)) {
            $this->setMessageFormCheckout('components.success', $this->text('Code is valid'));
            return;
        }

        $this->setError('pricerule_code', $this->text('Invalid code'));
        $this->setMessageFormCheckout('components.warning', $this->text('Invalid code'));
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
            'user_id' => $this->cart_uid,
            'store_id' => $this->store_id
        );

        $this->setSubmitted("cart.items.$sku", $item);

        $product = $this->cart_content['items'][$sku]['product'];

        $this->addValidator("cart.items.$sku.quantity", array(
            'numeric' => array(),
            'length' => array('min' => 1, 'max' => 2)
        ));

        $this->addValidator("cart.items.$sku", array(
            'cart_limits' => array(
                'increment' => false, 'data' => $product)
        ));

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
            'user_id' => $this->cart_uid,
            'store_id' => $this->store_id
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
     * @return null|void
     */
    protected function updateCartCheckout()
    {
        if (!$this->isSubmitted('cart.action.update')) {
            return null;
        }

        $this->cart_content = $this->cart->getByUser($this->cart_uid, $this->store_id);

        if ($this->request->isAjax() || $this->isPosted('save')) {
            return null;
        }

        $message = $this->text('Your cart has been updated');
        return $this->redirect('', $message, 'success');
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

        return $this->addOrderCheckout();
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

        $this->setSubmitted('address.user_id', $this->cart_uid);

        $this->addValidator('address', array(
            'country_format' => array()
        ));

        return $this->setValidators();
    }

    /**
     * Validates an array of submitted values before creating an order
     */
    protected function validateOrderCheckout()
    {
        if (!$this->address_form) {
            $this->addValidator('shipping_address', array(
                'required' => array(
                    'message' => $this->text('Please select a shipping address'))
            ));
        }

        $this->addValidator('shipping', array(
            'required' => array(
                'message' => $this->text('Please select a shipping method'))
        ));

        $this->addValidator('payment', array(
            'required' => array(
                'message' => $this->text('Please select a payment method'))
        ));

        $this->setValidators();
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
            $this->address->controlLimit($this->cart_uid);
        }
    }

    /**
     * Adds a new order
     */
    protected function addOrderCheckout()
    {
        $submitted = $this->getSubmitted();
        $submitted += $this->form_data['order'];

        $result = $this->order->submit($submitted, $this->cart_content);
        $this->redirect($result['redirect'], $result['message'], $result['severity']);
    }

    /**
     * Prepares form data before passing them to templates
     */
    protected function setFormDataAfterCheckout()
    {
        if (empty($this->cart_content)) {
            return; // Required
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

        $this->form_data['pane_login'] = $this->render('checkout/panes/login', $this->form_data);
        $this->form_data['pane_review'] = $this->render('checkout/panes/review', $this->form_data);
        $this->form_data['settings'] = json_encode($this->form_data['settings'], JSON_FORCE_OBJECT);
        $this->form_data['pane_payment_methods'] = $this->render('checkout/panes/payment_methods', $this->form_data);
        $this->form_data['pane_shipping_methods'] = $this->render('checkout/panes/shipping_methods', $this->form_data);
        $this->form_data['pane_shipping_address'] = $this->render('checkout/panes/shipping_address', $this->form_data);
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
        $payment_methods = $this->shipping->getMethods();
        $shipping_methods = $this->payment->getMethods();

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
    protected function outputCheckout()
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

            $method = $this->{$type}->getMethod($order[$type]);

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
        if (strcmp((string) $order['user_id'], $this->cart_uid) !== 0) {
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
