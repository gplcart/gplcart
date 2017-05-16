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
     * An array of filter conditions
     * @var array
     */
    protected $data_filter = array();

    /**
     * Pager limits
     * @var array
     */
    protected $data_limit;

    /**
     * A total number of results found for the filter conditions
     * @var integer
     */
    protected $data_total;

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
        $this->setPagerListAddress();

        $this->setData('addresses', $this->getListAddress());
        $this->outputListAddress();
    }

    /**
     * Set pager on the address list page
     */
    protected function setPagerListAddress()
    {
        $this->data_limit = $this->setPager($this->data_total, $this->data_filter);
    }

    /**
     * Set filter query on the address overview page
     * @return array
     */
    protected function setFilterListAddress()
    {
        $query = $this->getFilterQuery();

        $filters = array('city_id', 'address_id',
            'address_1', 'phone', 'user_id', 'user_email', 'full_name', 'postcode', 'city_name');

        $this->setFilter($filters, $query);
    }

    /**
     * Applies an action to the selected aliases
     */
    protected function actionListAddress()
    {
        $action = (string) $this->getPosted('action');

        if (empty($action)) {
            return null;
        }

        $selected = (array) $this->getPosted('selected', array());

        $deleted = 0;
        foreach ($selected as $id) {
            if ($action === 'delete' && $this->access('address_delete')) {
                $deleted += (int) $this->address->delete($id);
            }
        }

        if ($deleted > 0) {
            $message = $this->text('Deleted %num addresses', array('%num' => $deleted));
            $this->setMessage($message, 'success', true);
        }
    }

    /**
     * Sets a total number of addresses found for the filter conditions
     */
    protected function setTotalListAddress()
    {
        $query = $this->data_filter;
        $query['count'] = true;
        $this->data_total = (int) $this->address->getList($query);
    }

    /**
     * Returns an array of addresses
     * @return array
     */
    protected function getListAddress()
    {
        $query = $this->data_filter;
        $query['limit'] = $this->data_limit;
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
        $breadcrumb = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')
        );
        $this->setBreadcrumb($breadcrumb);
    }

    /**
     * Render and output the address overview page
     */
    protected function outputListAddress()
    {
        $this->output('user/address/list');
    }

}
