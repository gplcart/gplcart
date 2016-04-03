<?php

namespace core\controllers;

use core\Controller;
use core\models\Cart;
use core\models\Image;
use core\models\State;
use core\models\Order;
use core\models\Price;
use core\models\Address;
use core\models\Country;

class Checkout extends Controller
{

    /**
     * Cart model instance
     * @var \core\models\Cart $cart
     */
    protected $cart;

    /**
     * Order model instance
     * @var \core\models\Order $order
     */
    protected $order;

    /**
     * Image model instance
     * @var \core\models\Image $image
     */
    protected $image;

    /**
     * Address model instance
     * @var \core\models\Address $address
     */
    protected $address;

    /**
     * Price model instance
     * @var \core\models\Price $price
     */
    protected $price;

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
     * Currrent user cart ID
     * @var string
     */
    protected $cart_user_id;

    /**
     * Current cart content
     * @var array
     */
    protected $cart_content;

    /**
     * Current state of address form
     * @var boolean
     */
    protected $address_form;

    /**
     * Current state of login form
     * @var type
     */
    protected $login_form;

    /**
     * Wheter the cart has been updated
     * @var boolean
     */
    protected $cart_updated;

    /**
     * Cart action
     * @var string
     */
    protected $cart_action;

    /**
     * Current country code
     * @var string
     */
    protected $country_code;

    /**
     * Cart items limit
     * @var integer
     */
    protected $quantity_limit;

    /**
     * Template data array
     * @var array
     */
    protected $form_data;

    /**
     * Whether the cart reached the limit
     * @var boolean
     */
    protected $limit_reached;

    /**
     * Submitted address
     * @var array
     */
    protected $submitted_address;

    /**
     * Total cart items quantity
     * @var integer
     */
    protected static $total_quantity;

    /**
     * Constructor
     * @param Cart $cart
     * @param Country $country
     * @param State $state
     * @param Address $address
     * @param Order $order
     * @param Price $price
     * @param Image $image
     */
    public function __construct(Cart $cart, Country $country, State $state, Address $address, Order $order, Price $price, Image $image)
    {
        parent::__construct();

        $this->cart = $cart;
        $this->image = $image;
        $this->order = $order;
        $this->price = $price;
        $this->state = $state;
        $this->address = $address;
        $this->country = $country;

        static::$total_quantity = 0;

        $this->login_form = false;
        $this->cart_updated = false;
        $this->address_form = false;
        $this->limit_reached = false;

        $this->form_data = array();
        $this->submitted_address = array();

        $this->cart_user_id = $this->cart->uid();
        $this->country_code = $this->country->getDefault();
        $this->quantity_limit = (int) $this->config->get('cart_total_limit', 20);
        $this->quantity_limit_sku = (int) $this->config->get('cart_sku_limit', 10);
        $this->cart_content = $this->cart->getByUser($this->cart_user_id, false);
    }

    /**
     * Displays the checkout page
     */
    public function checkout()
    {
        $this->setTitleCheckout();
        $this->setBreadcrumbCheckout();

        if (empty($this->cart_content['items'])) {
            $this->data['checkout_form'] = $this->render('checkout/form');
            $this->output('checkout/checkout');
        }

        $this->submitted = $this->request->post('cart');

        $this->form_data = array(
            'order' => array(
                'store_id' => $this->store_id,
                'user_id' => $this->cart_user_id,
                'total' => $this->cart_content['total'],
                'currency' => $this->cart_content['currency']));

        $this->form_data['addresses'] = $this->address->getTranslatedList($this->cart_user_id);
        $this->form_data['shipping_services'] = $this->order->getServices('shipping', $this->cart_content, $this->form_data['order']);
        $this->form_data['payment_services'] = $this->order->getServices('payment', $this->cart_content, $this->form_data['order']);
        $this->address_form = empty($this->form_data['addresses']);

        if (!empty($this->submitted['items']) && $this->request->post('update')) {
            $this->updateCart();
        }

        if (!empty($this->submitted['plus']) || !empty($this->submitted['minus'])) {
            $this->actionCart();
        }

        if (!empty($this->submitted['wishlist'])) {
            $this->moveWishlist();
        }

        if (!empty($this->submitted['delete'])) {
            $this->deleteCart();
        }

        if ($this->cart_updated) {
            $this->refreshCart();
        }

        $this->form_data['order'] = $this->request->post('order', array()) + $this->form_data['order'];
        $this->submitted_address = $this->request->post('address', array('country' => $this->country_code));
        $check_code = $this->request->post('check_code');

        if (isset($check_code)) {
            $this->validateCode($check_code);
        }

        if (isset($this->submitted_address['country'])) {
            $this->country_code = $this->submitted_address['country'];
        }

        if ($this->request->post('add_address') || $this->request->post('get_states') || $this->submitted_address) {
            $this->address_form = true;
        }

        if ($this->request->post('cancel_address_form')) {
            $this->address_form = false;
        }

        if ($this->request->post('checkout_login') && empty($this->uid)) {
            $this->login_form = true;
        }

        if ($this->request->post('login')) {
            $this->loginUser();
        }

        if ($this->request->post('checkout_anonymous')) {
            $this->login_form = false;
        }

        if ($this->request->post('save')) {
            $this->submit();
        }

        if ($this->submitted_address) {
            $this->form_data['address'] = $this->submitted_address;
        }

        $this->form_data['login_form'] = $this->login_form;
        $this->form_data['address_form'] = $this->address_form;
        $this->form_data['country_code'] = $this->country_code;

        $this->form_data['countries'] = $this->country->getNames(true);
        $this->form_data['cart'] = $this->prepareCartItems($this->cart_content);
        $this->form_data['format'] = $this->country->getFormat($this->country_code, true);
        $this->form_data['states'] = $this->state->getList(array('country' => $this->country_code, 'status' => 1));

        $this->calculate();

        $this->form_data['pane_login'] = $this->render('checkout/panes/login', $this->form_data);
        $this->form_data['pane_review'] = $this->render('checkout/panes/review', $this->form_data);
        $this->form_data['pane_payment_services'] = $this->render('checkout/panes/payment_services', $this->form_data);
        $this->form_data['pane_shipping_address'] = $this->render('checkout/panes/shipping_address', $this->form_data);
        $this->form_data['pane_shipping_services'] = $this->render('checkout/panes/shipping_services', $this->form_data);

        $form = $this->render('checkout/form', $this->form_data);

        if ($this->request->ajax()) {
            $this->response->html($form);
        }

        $this->data['checkout_form'] = $form;
        $this->outputCheckout();
    }

    /**
     * Displays the complete order page
     * @param integer $order_id
     */
    public function complete($order_id)
    {
        $order = $this->getOrder($order_id);
        $this->data['text'] = $this->order->getCompleteMessage($order);
        
        $this->setTitleComplete($order);
        $this->setBreadcrumbComplete($order);
        $this->outputComplete();
    }

    /**
     * Sets titles on the checkout page
     * @param array $order
     */
    protected function setTitleCheckout()
    {
        $this->setTitle($this->text('Checkout'));
    }

    /**
     * Sets breadcrumbs on the checkout page
     * @param array $order
     */
    protected function setBreadcrumbCheckout()
    {
        $this->setBreadcrumb(array('text' => $this->text('Home'), 'url' => $this->url('/')));
    }

    /**
     * Outputs the checkout page
     */
    protected function outputCheckout()
    {
        $this->output('checkout/checkout');
    }

    /**
     * Sets titles on the complete order page
     * @param array $order
     */
    protected function setTitleComplete(array $order)
    {
        $this->setTitle($this->text('Checkout completed'));
    }

    /**
     * Sets breadcrumbs on the complete order page
     * @param array $order
     */
    protected function setBreadcrumbComplete(array $order)
    {
        $this->setBreadcrumb(array('text' => $this->text('Home'), 'url' => $this->url('/')));
    }

    /**
     * Outputs the complete order page
     */
    protected function outputComplete()
    {
        $this->output('checkout/complete');
    }

    /**
     * Updates the cart quantity
     */
    protected function updateCart()
    {
        foreach ($this->submitted['items'] as $cart_id => $item) {
            if ((int) $item['quantity'] === 0) {
                continue;
            }

            static::$total_quantity += (int) $item['quantity'];

            if ($this->quantity_limit && static::$total_quantity >= $this->quantity_limit) {
                $this->form_data['form_errors']['cart'] = $this->text('Sorry, you cannot have more than %s items in your cart', array('%s' => $this->quantity_limit));
                break;
            }

            $this->cart->update($cart_id, array('quantity' => (int) $item['quantity']));
            $this->cart_updated = true;
        }
    }

    /**
     * Moview a cart item to a wishlist
     */
    protected function moveWishlist()
    {
        if ($this->cart->moveToWishlist($this->submitted['wishlist'])) {
            $this->form_data['form_messages']['cart'] = $this->text('Product has been moved to your <a href="!href">wishlist</a>', array('!href' => $this->url('wishlist')));
            $this->cart_updated = true;
        }
    }

    /**
     * Applies an action to the cart items
     * @return boolean
     */
    protected function actionCart()
    {
        if (!empty($this->submitted['plus'])) {
            $this->cart_action = $this->submitted['plus'];
            if ($this->quantity_limit_sku && $this->cart_content['items'][$this->cart_action]['quantity'] < $this->quantity_limit_sku) {
                $this->cart_content['items'][$this->cart_action]['quantity'] ++;
            }
        }

        if (!empty($this->submitted['minus'])) {
            $this->cart_action = $this->submitted['minus'];

            if ($this->cart_content['items'][$this->cart_action]['quantity'] > 1) {
                $this->cart_content['items'][$this->cart_action]['quantity'] --;
            }
        }

        if ($this->quantity_limit && (int) $this->cart_content['quantity'] >= $this->quantity_limit) {
            $this->limit_reached = !empty($this->submitted['plus']);
        }

        if ($this->cart_content['quantity'] <= 0) {
            return false;
        }

        if ($this->limit_reached) {
            $this->form_data['form_errors']['cart'] = $this->text('Sorry, you cannot have more than %s items in your cart', array(
                '%s' => $this->quantity_limit));
            return false;
        }

        $this->cart_updated = true;

        $this->cart->update($this->cart_action, array(
            'quantity' => $this->cart_content['items'][$this->cart_action]['quantity']));

        return true;
    }

    /**
     * Deletes an item from the cart
     */
    protected function deleteCart()
    {
        $this->cart_updated = true;
        $this->cart->delete($this->submitted['delete']);
    }

    /**
     * Refreshes the cart
     */
    protected function refreshCart()
    {
        $this->cart_content = $this->cart->getByUser($this->cart_user_id, false); // false - disable cart cache

        if (!$this->request->ajax()) {
            $this->redirect('', $this->text('Your cart has been updated'), 'success');
        }
    }

    /**
     * Logs in anonymous user
     */
    protected function loginUser()
    {
        $this->login_form = true;
        $result = $this->login($this->cart_content);

        if (isset($result['user'])) {
            $options = array(
                'redirect' => 'checkout',
                'message_type' => 'success',
                'message' => $this->text('Hello, %name. Now you\'re logged in', array('%name' => $result['user']['name'])));

            $result = array_replace($result, $options);
            $this->redirect($result['redirect'], $result['message'], $result['message_type']);
        }

        $this->form_data['form_errors']['login'] = $this->text('Invalid E-mail and/or password');
    }

    /**
     * Modifies an array of cart items before rendering
     * @param array $cart
     * @return array
     */
    protected function prepareCartItems(array $cart)
    {
        if (empty($cart['items'])) {
            return array();
        }

        $imagestyle = $this->config->module($this->theme, 'image_style_cart', 3);

        foreach ($cart['items'] as &$item) {
            $imagepath = '';
            if (empty($item['product']['combination_id']) && !empty($item['product']['images'])) {
                $imagefile = reset($item['product']['images']);
                $imagepath = $imagefile['path'];
            }

            if (!empty($item['product']['option_file_id']) && !empty($item['product']['images'][$item['product']['option_file_id']]['path'])) {
                $imagepath = $item['product']['images'][$item['product']['option_file_id']]['path'];
            }

            $item['total_formatted'] = $this->price->format($item['total'], $cart['currency']);
            $item['price_formatted'] = $this->price->format($item['price'], $cart['currency']);

            if (empty($imagepath)) {
                $item['thumb'] = $this->image->placeholder($imagestyle);
            } else {
                $item['thumb'] = $this->image->url($imagestyle, $imagepath);
            }
        }

        $cart['total_formatted'] = $this->price->format($cart['total'], $cart['currency']);
        return $cart;
    }

    /**
     * Calculates order totals
     */
    protected function calculate()
    {
        $calculated = $this->order->calculate($this->cart_content, $this->form_data);
        $this->form_data['total_formatted'] = $this->price->format($calculated['total'], $calculated['currency']);
        $this->form_data['total'] = $calculated['total'];

        $components = $this->prepareComponents($calculated, $this->cart_content, $this->form_data['order']);
        $this->form_data['price_components'] = $components;
    }

    /**
     * Prepares an array of price rule components
     * @param array $calculated
     * @param array $cart
     * @param array $order
     * @return array
     */
    protected function prepareComponents(array $calculated, array $cart, array $order)
    {
        $components = array();
        foreach ($calculated['components'] as $type => $component) {
            $name = $this->text($type);

            if (in_array($type, array('shipping', 'payment'))) {
                $service = $this->order->getService($order[$type], $type, $cart, $order);

                if (isset($service['name'])) {
                    $name = $service['name'];
                }
            }

            if (isset($component['rule']['name'])) {
                $name = $component['rule']['name'];
            }

            $components[$type] = array(
                'name' => $name,
                'rule' => isset($component['rule']) ? $component['rule'] : false,
                'price' => $component['price'],
                'formatted_price' => $this->price->format($component['price'], $calculated['currency'])
            );
        }

        return $components;
    }
    
    /**
     * Saves an order to the database
     * @return null
     */
    protected function submit()
    {
        if ($this->address_form) {
            $this->submitAddress();
        }

        $this->validate();

        if (!empty($this->form_data['form_errors'])) {
            return;
        }
        
        $result = $this->order->submit($this->form_data['order'], $this->cart_content);
        
        if (empty($result['order']['order_id'])) {
            return;
        }
        
        $order_id = $result['order']['order_id'];
        
        $redirect = empty($result['redirect']) ? "checkout/complete/$order_id" : $result['redirect'];
        $message = empty($result['message']) ? '' : $result['message'];
        $message_type = empty($result['message_type']) ? 'info' : $result['message_type'];
        $this->redirect($redirect, $message, $message_type);
    }

    /**
     * Validates checkout form
     * @return boolean
     */
    protected function validate()
    {
        $has_error = false;

        if (!$this->address_form && empty($this->form_data['order']['shipping_address'])) {
            $this->form_data['form_errors']['address'] = $this->text('Invalid address');
            $has_error = true;
        }

        if ($this->form_data['shipping_services'] && empty($this->form_data['order']['shipping'])) {
            $this->form_data['form_errors']['shipping'] = $this->text('Invalid shipping service');
            $has_error = true;
        }

        if ($this->form_data['payment_services'] && empty($this->form_data['order']['payment'])) {
            $this->form_data['form_errors']['payment'] = $this->text('Invalid payment service');
            $has_error = true;
        }

        if ($this->form_data['payment_services'] && empty($this->form_data['order']['payment'])) {
            $this->form_data['form_errors']['payment'] = $this->text('Invalid payment service');
            $has_error = true;
        }

        return !$has_error;
    }

    /**
     * Saves a submitted address
     * @return boolean
     */
    protected function submitAddress()
    {
        if (!$this->validateAddress()) {
            return false;
        }
        
        $user_id = $this->form_data['order']['user_id'];
        $this->submitted_address['user_id'] = $user_id;
        $this->form_data['order']['shipping_address'] = $this->address->add($this->submitted_address);
        
        $this->address->reduceLimit($user_id);
        return true;
    }

    /**
     * Validates a submitted address
     * @return boolean
     */
    protected function validateAddress()
    {
        $has_errors = false;
        $format = $this->country->getFormat($this->country_code, true);

        foreach ($this->submitted_address as $key => $value) {
            if (empty($value) && !empty($format[$key]['required'])) {
                $this->form_data['form_errors']['address'][$key] = $this->text('Required field');
                $has_errors = true;
            }
        }

        return !$has_errors;
    }

    /**
     * Validates a coupon code
     * @param integer $price_rule_id
     * @return boolean
     */
    protected function validateCode($price_rule_id)
    {
        $code = '';
        if (isset($this->form_data['order']['code'])) {
            $code = $this->form_data['order']['code'];
        }

        if ($code && $this->order->codeMatches($price_rule_id, $code)) {
            return true;
        }

        $this->form_data['form_errors']['code'] = $this->text('Invalid code');
        return false;
    }

    /**
     * Modifies an array of services before rendering
     * @param array $services
     * @param array $cart
     * @param array $order
     * @return array
     */
    protected function prepareServices(array $services, array $cart, array $order)
    {
        foreach ($services as &$service) {
            $service['price'] = $this->price->convert((int) $service['price'], $service['currency'], $order['currency']);
            $service['price_formatted'] = $this->price->format($service['price'], $order['currency']);

            if (empty($service['template'])) {
                continue;
            }

            $data = array('cart' => $cart, 'order' => $order);

            if (!empty($service['template_data'])) {
                $data = (array) $service['template_data'];
            }

            $service['html'] = $this->render($service['template'], $data, true);
        }

        return $services;
    }

    /**
     * Returns an order
     * @param integer $order_id
     * @return array
     */
    protected function getOrder($order_id)
    {
        $order = $this->order->get($order_id);

        if (empty($order['order_id'])) {
            $this->outputError(404);
        }

        if (strcmp((string) $order['user_id'], $this->cart->uid()) !== 0) {
            $this->outputError(404);
        }

        return $order;
    }

    /**
     * Logs in a customer during checkout
     * @param array $cart
     * @return array
     */
    protected function login(array $cart)
    {
        $email = $this->request->post('email');
        $password = $this->request->post('password', '', false);

        $result = $this->user->login($email, $password);

        if (isset($result['user'])) {
            $this->cart->login($result['user'], $cart);
        }

        return $result;
    }
}
