<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\backend;

use gplcart\core\controllers\backend\Controller as BackendController;
use gplcart\core\models\Address as AddressModel;

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
     * Pager limit
     * @var array
     */
    protected $data_limit;

    /**
     * @param AddressModel $address
     */
    public function __construct(AddressModel $address)
    {
        parent::__construct();

        $this->address = $address;
    }

    /**
     * Page callback
     * Displays the address overview page
     */
    public function listAddress()
    {
        $this->actionListAddress();

        $this->setTitleListAddress();
        $this->setBreadcrumbListAddress();
        $this->setFilterListAddress();
        $this->setPagerlListAddress();

        $this->setData('addresses', $this->getListAddress());
        $this->outputListAddress();
    }

    /**
     * Set filter query on the address overview page
     */
    protected function setFilterListAddress()
    {
        $allowed = array('city_id', 'address_id', 'address_1',
            'phone', 'user_id', 'user_email', 'user_email_like', 'full_name', 'postcode', 'city_name');

        $this->setFilter($allowed);
    }

    /**
     * Applies an action to the selected aliases
     */
    protected function actionListAddress()
    {
        list($selected, $action) = $this->getPostedAction();

        $deleted = 0;

        foreach ($selected as $id) {
            if ($action === 'delete' && $this->access('address_delete')) {
                $deleted += (int) $this->address->delete($id);
            }
        }

        if ($deleted > 0) {
            $message = $this->text('Deleted %num item(s)', array('%num' => $deleted));
            $this->setMessage($message, 'success');
        }
    }

    /**
     * Sets pager
     * @return array
     */
    protected function setPagerlListAddress()
    {
        $conditions = $this->query_filter;
        $conditions['count'] = true;

        $pager = array(
            'query' => $this->query_filter,
            'total' => (int) $this->address->getList($conditions)
        );

        return $this->data_limit = $this->setPager($pager);
    }

    /**
     * Returns an array of addresses using pager limits and the URL query conditions
     * @return array
     */
    protected function getListAddress()
    {
        $conditions = $this->query_filter;
        $conditions['limit'] = $this->data_limit;
        $addresses = (array) $this->address->getList($conditions);

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
