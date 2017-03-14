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
     * The current filter data
     * @var array
     */
    protected $data_filter = array();

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
        $this->actionAddress();

        $this->setTitleListAddress();
        $this->setBreadcrumbListAddress();

        $this->setFilterAddress();
        $limit = $this->setPager($this->getTotalAddress(), $this->data_filter);

        $addresses = $this->getListAddress($limit);
        $this->setJsSettingsListAddress($addresses);

        $this->setData('addresses', $addresses);
        $this->outputListAddress();
    }

    /**
     * Set filter query on the address overview page
     * @return array
     */
    protected function setFilterAddress()
    {
        $query = $this->getFilterQuery();

        $filters = array('city_id', 'address_id',
            'address_1', 'phone', 'user_id', 'user_email', 'full_name', 'postcode', 'city_name');

        $this->setFilter($filters, $query);
        return $this->data_filter = $query;
    }

    /**
     * Applies an action to the selected aliases
     * @return null
     */
    protected function actionAddress()
    {
        $action = (string) $this->request->post('action');

        if (empty($action)) {
            return null;
        }

        $selected = (array) $this->request->post('selected', array());

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
     * Returns total addresses found in the database
     * @return integer
     */
    protected function getTotalAddress()
    {
        $query = $this->data_filter;
        $query['count'] = true;
        return (int) $this->address->getList($query);
    }

    /**
     * Returns an array of addresses
     * @param array $limit
     * @return array
     */
    protected function getListAddress($limit)
    {
        $query = $this->data_filter;
        $query['limit'] = $limit;
        $addresses = (array) $this->address->getList($query);

        foreach ($addresses as &$address) {
            $address['translated'] = $this->address->getTranslated($address, true);
        }
        return $addresses;
    }

    /**
     * Set JS settings on the address overview page
     * @param array $addresses
     */
    protected function setJsSettingsListAddress(array $addresses)
    {
        $settings = array('key' => $this->config('gapi_browser_key', ''));
        foreach ($addresses as $address) {
            $query = $this->address->getGeocodeQuery($address);
            $settings['addresses'][$address['address_id']] = $query;
        }
        $this->setJsSettings('map', $settings);
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
     * Renders the address overview page
     */
    protected function outputListAddress()
    {
        $this->output('user/address/list');
    }

}
