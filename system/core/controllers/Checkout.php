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
     * Submitted address
     * @var array
     */
    protected $submitted_address;

    /**
     * Constructor
     * @param ModelsCountry $country
     * @param ModelsState $state
     * @param ModelsAddress $address
     * @param ModelsOrder $order
     */
    public function __construct(ModelsCountry $country, ModelsState $state,
            ModelsAddress $address, ModelsOrder $order)
    {
        parent::__construct();

        $this->order = $order;
        $this->state = $state;
        $this->address = $address;
        $this->country = $country;

        $this->cart_content = $this->cart->getByUser($this->cart_uid, $this->store_id);

        $this->login_form = false;
        $this->cart_updated = false;
        $this->address_form = false;

        $this->form_data = array();
        $this->submitted_address = array();

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

        $this->submitActionsCheckout();

        $this->setFormDataAfterCheckout();

        $this->setDataFormCheckout();

        $this->outputCheckout();
    }

    /**
     * 
     * @return type
     */
    protected function setFormDataBeforeCheckout()
    {
        $this->form_data['settings'] = array();
        $this->form_data['addresses'] = $this->address->getTranslatedList($this->cart_uid);
        $this->form_data['payment_methods'] = $this->order->getPaymentMethods(true);
        $this->form_data['shipping_methods'] = $this->order->getShippingMethods(true);
    }

    protected function setFormDataAfterCheckout()
    {
        if (empty($this->cart_content)) {
            return; // Required
        }

        $this->address_form = empty($this->form_data['addresses']);

        if (isset($this->submitted_address['country'])) {
            $this->country_code = $this->submitted_address['country'];
        }

        if (!empty($this->submitted_address)) {
            $this->form_data['address'] = $this->submitted_address;
        }

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
        $this->form_data['pane_payment_methods'] = $this->render('checkout/panes/payment_methods', $this->form_data);
        $this->form_data['pane_shipping_methods'] = $this->render('checkout/panes/shipping_methods', $this->form_data);
        $this->form_data['pane_shipping_address'] = $this->render('checkout/panes/shipping_address', $this->form_data);
        $this->form_data['settings'] = json_encode($this->form_data['settings'], JSON_FORCE_OBJECT);
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
     */
    protected function submitActionsCheckout()
    {
        $this->setSubmitted('order');
        $this->setSubmitted('user_id', $this->cart_uid);
        $this->setSubmitted('store_id', $this->store_id);

        $default = array('country' => $this->country_code);
        $this->submitted_address = $this->getSubmitted('address', $default);

        if (!empty($this->submitted_address)) {
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

        $this->submitOrderCheckout();
        $this->submitCartCheckout();
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
     * Saves an order to the database
     * @return null
     */
    protected function submitOrderCheckout()
    {
        if (!$this->isPosted('save')) {
            return;
        }

        $this->validateAddressCheckout();
        $this->validateOrderCheckout();

        if (!$this->hasErrors('order', false)) {
            $this->addAddressCheckout();
            $this->addOrderCheckout();
        }
    }

    /**
     * Adds a new order
     */
    protected function addOrderCheckout()
    {
        $submitted = $this->getSubmitted();
        $result = $this->order->submit($submitted, $this->cart_content);
        $this->redirect($result['redirect'], $result['message'], $result['severity']);
    }

    /**
     * Saves a submitted address
     * @return boolean
     */
    protected function addAddressCheckout()
    {
        if (!$this->address_form) {
            return;
        }

        $user_id = $this->form_data['order']['user_id'];
        $this->submitted_address['user_id'] = $user_id;
        $address = $this->address->add($this->submitted_address);

        $this->setSubmitted('shipping_address', $address);


        $this->address->controlLimit($user_id);
        return true;
    }

    /**
     * Validates a submitted address
     * @return boolean
     */
    protected function validateAddressCheckout()
    {
        if (!$this->address_form) {
            return;
        }

        $format = $this->country->getFormat($this->country_code, true);

        foreach ($this->submitted_address as $key => $value) {
            if (empty($value) && !empty($format[$key]['required'])) {
                $this->setError("order.address.$key", $this->text('Required field'));
            }
        }
    }

    /**
     * Validates checkout form
     * @return boolean
     */
    protected function validateOrderCheckout()
    {
        /*
          $has_error = false;

          if (!$this->address_form && empty($this->form_data['order']['shipping_address'])) {
          $this->errors['address'] = $this->text('Invalid address');
          $has_error = true;
          }

          if (!empty($this->form_data['shipping_services']) && empty($this->form_data['order']['shipping'])) {
          $this->errors['shipping'] = $this->text('Invalid shipping service');
          $has_error = true;
          }

          if (!empty($this->form_data['payment_services']) && empty($this->form_data['order']['payment'])) {
          $this->errors['payment'] = $this->text('Invalid payment service');
          $has_error = true;
          }

          if (!empty($this->form_data['payment_services']) && empty($this->form_data['order']['payment'])) {
          $this->errors['payment'] = $this->text('Invalid payment service');
          $has_error = true;
          }

          return !$has_error;
         * *
         */
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
    protected function submitCartCheckout()
    {
        $this->quantityCartCheckout();

        $this->moveCartWishlistCheckout();
        $this->deleteCartCheckout();
        $this->refreshCartCheckout();
    }

    /**
     * Moves a cart item to the wishlist
     */
    protected function moveCartWishlistCheckout()
    {
        $sku = $this->getSubmitted('cart.action.wishlist');

        if (empty($sku)) {
            return;
        }

        $options = array(
            'user_id' => $this->cart_uid,
            'store_id' => $this->store_id
        );

        $result = $this->cart->moveToWishlist($options + array('sku' => $sku));

        if (empty($result['wishlist_id'])) {
            return;
        }

        $this->form_data['settings']['quantity']['cart'] = $this->cart->getQuantity($options, 'total');
        $this->form_data['settings']['quantity']['wishlist'] = $this->wishlist->getList($options + array('count' => true));

        $this->form_data['messages']['cart'] = $result['message'];
        $this->setSubmitted('cart.action.update', true);
    }

    /**
     * Applies an action to the cart items
     * @return boolean
     */
    protected function quantityCartCheckout()
    {
        $items = $this->getSubmitted('cart.items');

        if (empty($items)) {
            return;
        }

        foreach ($items as $sku => $item) {

            $item += array(
                'sku' => $sku,
                'user_id' => $this->cart_uid,
                'store_id' => $this->store_id
            );

            $this->setSubmitted("cart.items.$sku", $item);

            $cart_id = $this->cart_content['items'][$sku]['cart_id'];
            $product = $this->cart_content['items'][$sku]['product'];

            $this->addValidator("cart.items.$sku.quantity", array(
                'numeric' => array(),
                'length' => array('min' => 1, 'max' => 2)
            ));

            $this->addValidator("cart.items.$sku", array(
                'cart_limits' => array('increment' => false)
            ));

            $errors = $this->setValidators($product);

            if (empty($errors)) {
                $this->setSubmitted('cart.action.update', true);
                $this->cart->update($cart_id, array('quantity' => $item['quantity']));
                continue;
            }

            $messages = Tool::flattenArray($errors);
            $this->form_data['messages']['cart']['warning'] = implode('<br>', $messages);
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
     * Refreshes the cart
     */
    protected function refreshCartCheckout()
    {
        if (!$this->isSubmitted('cart.action.update')) {
            return;
        }

        $this->cart_content = $this->cart->getByUser($this->cart_uid, $this->store_id);

        if (!$this->request->isAjax()) {
            $this->redirect('', $this->text('Your cart has been updated'), 'success');
        }
    }

    /**
     * Calculates order totals
     */
    protected function calculateCheckout()
    {
        $submitted = $this->getSubmitted();

        $this->form_data = Tool::merge($this->form_data, $submitted);

        $calculated = $this->order->calculate($this->cart_content, $this->form_data);
        $this->form_data['total_formatted'] = $this->price->format($calculated['total'], $calculated['currency']);
        $this->form_data['total'] = $calculated['total'];

        $components = $this->prepareOrderComponentsCheckout($calculated, $this->cart_content, $this->form_data);
        $this->form_data['price_components'] = $components;
    }

    /**
     * Prepares an array of price rule components
     * @param array $calculated
     * @param array $cart
     * @param array $order
     * @return array
     */
    protected function prepareOrderComponentsCheckout(array $calculated,
            array $cart, array $order)
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
     * Validates a coupon code
     */
    protected function validateCouponCheckout()
    {
        $price_rule_id = (int) $this->request->post('check_pricerule');

        if (empty($price_rule_id)) {
            return;
        }
        
        $code = $this->getSubmitted('pricerule_code', '');
        
        if($code === ''){
            return;
        }

        if ($this->order->codeMatches($price_rule_id, $code)) {
            $this->form_data['messages']['components']['success'] = $this->text('Code is valid');
            return;
        }
        
        $this->setError('pricerule_code', $this->text('Invalid code'));
        $this->form_data['messages']['components']['warning'] = $this->text('Invalid code');
    }

    /**
     * Modifies an array of services before rendering
     * @param array $services
     * @param array $cart
     * @param array $order
     * @return array
     */
    protected function prepareServices(array $services, array $cart,
            array $order)
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

}
