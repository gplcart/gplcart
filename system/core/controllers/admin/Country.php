<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers\admin;

use core\Controller;
use core\models\Country as ModelsCountry;

/**
 * Handles incoming requests and outputs data related to countries
 */
class Country extends Controller
{

    /**
     * Country model instance
     * @var \core\models\Country $country
     */
    protected $country;

    /**
     * Constructor
     * @param ModelsCountry $country
     */
    public function __construct(ModelsCountry $country)
    {
        parent::__construct();

        $this->country = $country;
    }

    /**
     * Displays the country overview page
     */
    public function listCountry()
    {
        if ($this->isPosted('action')) {
            $this->actionCountry();
        }

        $query = $this->getFilterQuery();
        $total = $this->getTotalCountry($query);
        $limit = $this->setPager($total, $query);

        $default = $this->country->getDefault();
        $countries = $this->getListCountry($limit, $query);

        $this->setData('countries', $countries);
        $this->setData('default_country', $default);

        $allowed = array('name', 'native_name', 'code', 'status', 'weight');
        $this->setFilter($allowed, $query);

        $this->setTitleListCountry();
        $this->setBreadcrumbListCountry();
        $this->outputListCountry();
    }

    /**
     * Displays the country add/edit form
     * @param string|null $country_code
     */
    public function editCountry($country_code = null)
    {
        $country = $this->getCountry($country_code);

        $this->setData('country', $country);

        if ($this->isPosted('delete')) {
            $this->deleteCountry($country);
        }

        if ($this->isPosted('save')) {
            $this->submitCountry($country);
        }

        $this->setTitleEditCountry($country);
        $this->setBreadcrumbEditCountry();
        $this->outputEditCountry();
    }

    /**
     * Displays the address format items for a given country
     * @param string $country_code
     */
    public function formatCountry($country_code)
    {
        $country = $this->getCountry($country_code);
        $this->setData('format', $country['format']);

        if ($this->isPosted('save')) {
            $this->submitFormatCountry($country);
        }

        $this->setTitleFormatCountry($country);
        $this->setBreadcrumbFormatCountry();
        $this->outputFormatCountry();
    }

    /**
     * Returns total number of countries
     * @param array $query
     * @return integer
     */
    protected function getTotalCountry(array $query)
    {
        $query['count'] = true;
        return $this->country->getList($query);
    }

    /**
     * Returns an array of countries
     * @param array $limit
     * @param array $query
     * @return array
     */
    protected function getListCountry(array $limit, array $query)
    {
        $query['limit'] = $limit;
        return $this->country->getList($query);
    }

    /**
     * Renders the country overview page
     */
    protected function outputListCountry()
    {
        $this->output('settings/country/list');
    }

    /**
     * Sets titles on the country overview page
     */
    protected function setTitleListCountry()
    {
        $this->setTitle($this->text('Countries'));
    }

    /**
     * Sets breadcrumbs on the country overview page
     */
    protected function setBreadcrumbListCountry()
    {
        $this->setBreadcrumb(array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')));
    }

    /**
     * Renders the country edit page
     */
    protected function outputEditCountry()
    {
        $this->output('settings/country/edit');
    }

    /**
     * Sets titles on the country edit page
     * @param array $country
     */
    protected function setTitleEditCountry(array $country)
    {
        if (isset($country['name'])) {
            $title = $this->text('Edit country %name', array(
                '%name' => $country['name']));
        } else {
            $title = $this->text('Add country');
        }

        $this->setTitle($title);
    }

    /**
     * Sets breadcrumbs on the country edit page
     */
    protected function setBreadcrumbEditCountry()
    {
        $this->setBreadcrumb(array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')));

        $this->setBreadcrumb(array(
            'url' => $this->url('admin/settings/country'),
            'text' => $this->text('Countries')));
    }

    /**
     * Returns an array of country data
     * @param string $country_code
     * @return array
     */
    protected function getCountry($country_code)
    {
        if (empty($country_code)) {
            return array();
        }

        $country = $this->country->get($country_code);

        if (empty($country)) {
            $this->outputError(404);
        }

        return $country;
    }

    /**
     * Deletes a country
     * @param array $country
     */
    protected function deleteCountry(array $country)
    {
        $this->controlAccess('country_delete');

        $deleted = $this->country->delete($country['code']);

        if ($deleted) {
            $this->redirect('admin/settings/country', $this->text('Country has been deleted'), 'success');
        }

        $this->redirect('', $this->text('Cannot delete this country'), 'danger');
    }

    /**
     * Applies an action to the selected countries
     * @return boolean
     */
    protected function actionCountry()
    {
        $value = (int) $this->request->post('value');
        $action = (string) $this->request->post('action');
        $selected = (array) $this->request->post('selected', array());

        if ($action === 'weight' && $this->access('country_edit')) {
            foreach ($selected as $code => $weight) {
                $this->country->update($code, array('weight' => $weight));
            }

            $this->response->json(array(
                'success' => $this->text('Countries have been reordered')));
        }

        $updated = $deleted = 0;

        foreach ($selected as $code) {

            if ($action === 'status' && $this->access('country_edit')) {
                $updated += (int) $this->country->update($code, array('status' => $value));
            }

            if ($action === 'delete' && $this->access('country_delete')) {
                $deleted += (int) $this->country->delete($code);
            }
        }

        if ($updated > 0) {
            $this->setMessage($this->text('Countries have been updated'), 'success', true);
            return true;
        }

        if ($deleted > 0) {
            $this->setMessage($this->text('Countries have been deleted'), 'success', true);
            return true;
        }

        return false;
    }

    /**
     * Saves a submitted country data
     * @param array $country
     * @return null
     */
    protected function submitCountry(array $country)
    {
        $this->setSubmitted('country');
        $this->validateCountry($country);

        if ($this->hasErrors('country')) {
            return;
        }

        if (isset($country['code'])) {
            $this->updateCountry($country);
        }

        $this->addCountry();
    }

    /**
     * Updates a country
     * @param array $country
     */
    protected function updateCountry(array $country)
    {
        $this->controlAccess('country_edit');
        $this->country->update($country['code'], $this->getSubmitted());
        $this->redirect('admin/settings/country', $this->text('Country has been updated'), 'success');
    }

    /**
     * Adds a new country
     */
    protected function addCountry()
    {
        $this->controlAccess('country_add');
        $this->country->add($this->getSubmitted());
        $this->redirect('admin/settings/country', $this->text('Country has been added'), 'success');
    }

    /**
     * Validates a country data
     * @param array $country
     */
    protected function validateCountry(array $country)
    {
        $this->setSubmittedBool('status');
        $this->setSubmittedBool('default');

        if ($this->getSubmitted('default')) {
            $this->setSubmitted('status', 1);
        }

        $this->addValidator('code', array(
            'regexp' => array(
                'pattern' => '/^[A-Z]{2}$/',
                'required' => true),
            'country_code_unique' => array()
        ));

        $this->addValidator('name', array(
            'length' => array('min' => 1, 'max' => 255)
        ));

        $this->addValidator('native_name', array(
            'length' => array('min' => 1, 'max' => 255)
        ));

        $this->addValidator('weight', array(
            'numeric' => array(),
            'length' => array('max' => 2)
        ));

        $this->setValidators($country);
    }

    /**
     * Renders the country format page
     */
    protected function outputFormatCountry()
    {
        $this->output('settings/country/format');
    }

    /**
     * Sets titles on the county formats page
     * @param array $country
     */
    protected function setTitleFormatCountry(array $country)
    {
        $text = $this->text('Address format of %country', array(
            '%country' => $country['name']));

        $this->setTitle($text);
    }

    /**
     * Sets breadcrumbs on the country format edit page
     */
    protected function setBreadcrumbFormatCountry()
    {
        $this->setBreadcrumb(array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')));

        $this->setBreadcrumb(array(
            'url' => $this->url('admin/settings/country'),
            'text' => $this->text('Countries')));
    }

    /**
     * Saves a country format
     * @param array $country
     */
    protected function submitFormatCountry(array $country)
    {
        $this->controlAccess('country_format_edit');

        $format = $this->setSubmitted('format');

        // Fix checkboxes, enable required fields
        foreach ($format as &$item) {

            $item['required'] = isset($item['required']);
            $item['status'] = isset($item['status']);

            if ($item['required']) {
                $item['status'] = 1; // Required fields are always enabled
            }
        }

        $this->country->update($country['code'], array('format' => $format));
        $this->redirect('admin/settings/country', $this->text('Country has been updated'), 'success');
    }

}
