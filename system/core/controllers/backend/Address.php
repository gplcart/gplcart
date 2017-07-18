<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\backend;

use gplcart\core\models\Address as AddressModel;
use gplcart\core\controllers\backend\Controller as BackendController;

/**
 * Handles incoming requests and outputs data related to user addresses
 */
class Address extends BackendController
{

    /**
     * Address model instance
     * @var \gplcart\core\models\Address $address
     */
    protected $address;

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
     */
    public function listAddress()
    {
        $this->actionListAddress();

        $this->setTitleListAddress();
        $this->setBreadcrumbListAddress();

        $this->setFilterListAddress();
        $this->setTotalListAddress();
        $this->setPagerLimit();

        $this->setData('addresses', $this->getListAddress());
        $this->outputListAddress();
    }

    /**
     * Set filter query on the address overview page
     */
    protected function setFilterListAddress()
    {
        $allowed = array('city_id', 'address_id', 'address_1', 'phone',
            'user_id', 'user_email', 'full_name', 'postcode', 'city_name');

        $this->setFilter($allowed);
    }

    /**
     * Applies an action to the selected aliases
     */
    protected function actionListAddress()
    {
        $action = $this->getPosted('action', '', true, 'string');
        $selected = $this->getPosted('selected', array(), true, 'array');

        if (empty($action)) {
            return null;
        }

        $deleted = 0;
        foreach ($selected as $id) {
            if ($action === 'delete' && $this->access('address_delete')) {
                $deleted += (int) $this->address->delete($id);
            }
        }

        if ($deleted > 0) {
            $message = $this->text('Deleted %num items', array('%num' => $deleted));
            $this->setMessage($message, 'success', true);
        }
    }

    /**
     * Sets a total number of addresses found for the filter conditions
     */
    protected function setTotalListAddress()
    {
        $query = $this->query_filter;
        $query['count'] = true;
        $this->total = (int) $this->address->getList($query);
    }

    /**
     * Returns an array of addresses
     * @return array
     */
    protected function getListAddress()
    {
        $query = $this->query_filter;
        $query['limit'] = $this->limit;
        $addresses = (array) $this->address->getList($query);
        return $this->prepareListAddress($addresses);
    }

    /**
     * Prepares an array of addresses
     * @param array $addresses
     * @return array
     */
    protected function prepareListAddress(array $addresses)
    {
        foreach ($addresses as &$address) {
            $address['translated'] = $this->address->getTranslated($address, true);
        }
        return $addresses;
    }

    /**
     * Sets titles on the address overview page
     */
    protected function setTitleListAddress()
    {
        $this->setTitle($this->text('Addresses'));
    }

    /**
     * Sets breadcrumbs on the address overview page
     */
    protected function setBreadcrumbListAddress()
    {
        $this->setBreadcrumbBackend();
    }

    /**
     * Render and output the address overview page
     */
    protected function outputListAddress()
    {
        $this->output('user/address/list');
    }

}
