<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\frontend;

use gplcart\core\models\Address as AddressModel;
use gplcart\core\controllers\frontend\Controller as FrontendController;

/**
 * Handles incoming requests and outputs data related to user addresses shown in accounts
 */
class AccountAddress extends FrontendController
{

    /**
     * Address model instance
     * @var \gplcart\core\models\Address $address
     */
    protected $address;

    /**
     * An array of user data
     * @var array
     */
    protected $data_user = array();

    /**
     * @param AddressModel $address
     */
    public function __construct(AddressModel $address)
    {
        parent::__construct();

        $this->address = $address;
    }

    /**
     * Displays the address overview page
     * @param integer $user_id
     */
    public function listAccountAddress($user_id)
    {
        $this->setUserAccountAddress($user_id);
        $this->actionAccountAddress();

        $this->setTitleListAccountAddress();
        $this->setBreadcrumbListAccountAddress();

        $this->setData('user', $this->data_user);
        $this->setData('addresses', $this->getListAccountAddress());

        $this->outputListAccountAddress();
    }

    /**
     * Sets a user data
     * @param integer $user_id
     */
    protected function setUserAccountAddress($user_id)
    {
        $this->data_user = $this->user->get($user_id);

        if (empty($this->data_user)) {
            $this->outputHttpStatus(404);
        }

        if (empty($this->data_user['status']) && !$this->access('user')) {
            $this->outputHttpStatus(403);
        }
    }

    /**
     * Returns an array of addresses
     * @return array
     */
    protected function getListAccountAddress()
    {
        $addresses = $this->address->getTranslatedList($this->data_user['user_id']);
        return $this->prepareListAccountAddress($addresses);
    }

    /**
     * Prepares an array of user addresses
     * @param array $addresses
     * @return array
     */
    protected function prepareListAccountAddress(array $addresses)
    {
        $prepared = array();
        foreach ($addresses as $address_id => $items) {
            $prepared[$address_id]['items'] = $items;
            $prepared[$address_id]['locked'] = !$this->address->canDelete($address_id);
        }

        return $prepared;
    }

    /**
     * Deletes an address
     */
    protected function actionAccountAddress()
    {
        $key = 'delete';
        $this->controlToken($key);
        $address_id = $this->getQuery($key);

        if (!empty($address_id)) {
            if ($this->address->delete($address_id)) {
                $this->redirect('', $this->text('Address has been deleted'), 'success');
            }

            $this->redirect('', $this->text('Unable to delete'), 'warning');
        }
    }

    /**
     * Sets breadcrumbs on the address overview page
     */
    protected function setBreadcrumbListAccountAddress()
    {
        $breadcrumbs = array();

        $breadcrumbs[] = array(
            'url' => $this->url('/'),
            'text' => $this->text('Shop')
        );

        $breadcrumbs[] = array(
            'text' => $this->text('Account'),
            'url' => $this->url("account/{$this->data_user['user_id']}")
        );

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Sets titles on the address overview page
     */
    protected function setTitleListAccountAddress()
    {
        $this->setTitle($this->text('Addresses'));
    }

    /**
     * Render and output the address overview page
     */
    protected function outputListAccountAddress()
    {
        $this->output('account/addresses');
    }

}
