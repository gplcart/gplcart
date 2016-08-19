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
    public function countries()
    {

        if ($this->isPosted('action')) {
            $this->action();
        }

        $query = $this->getFilterQuery();
        $total = $this->getTotalCountries($query);
        $limit = $this->setPager($total, $query);

        $this->setData('countries', $this->getCountries($limit, $query));
        $this->setData('default_country', $this->country->getDefault());

        $allowed = array('name', 'native_name', 'code', 'status', 'weight');
        $this->setFilter($allowed, $query);

        $this->setTitleCountries();
        $this->setBreadcrumbCountries();
        $this->outputCountries();
    }

    /**
     * Displays the country add/edit form
     * @param string|null $country_code
     */
    public function edit($country_code = null)
    {
        $country = $this->get($country_code);

        $this->setData('country', $country);

        if ($this->isPosted('delete')) {
            $this->delete($country);
        }

        if ($this->isPosted('save')) {
            $this->submit($country);
        }

        $this->setTitleEdit($country);
        $this->setBreadcrumbEdit();
        $this->outputEdit();
    }

    /**
     * Displays the address format items for a given country
     * @param string $country_code
     */
    public function format($country_code)
    {
        $country = $this->get($country_code);
        $this->setData('format', $country['format']);

        if ($this->isPosted('save')) {
            $this->submitFormat($country);
        }

        $this->setTitleFormat($country);
        $this->setBreadcrumbFormat();
        $this->outputFormat();
    }

    /**
     * Returns total number of countries
     * @param array $query
     * @return integer
     */
    protected function getTotalCountries(array $query)
    {
        return $this->country->getList(array('count' => true) + $query);
    }

    /**
     * Returns an array of countries
     * @param array $limit
     * @param array $query
     * @return array
     */
    protected function getCountries(array $limit, array $query)
    {
        return $this->country->getList(array('limit' => $limit) + $query);
    }

    /**
     * Renders the country overview page
     */
    protected function outputCountries()
    {
        $this->output('settings/country/list');
    }

    /**
     * Sets titles on the country overview page
     */
    protected function setTitleCountries()
    {
        $this->setTitle($this->text('Countries'));
    }

    /**
     * Sets breadcrumbs on the country overview page
     */
    protected function setBreadcrumbCountries()
    {
        $this->setBreadcrumb(array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')));
    }

    /**
     * Renders the country edit page
     */
    protected function outputEdit()
    {
        $this->output('settings/country/edit');
    }

    /**
     * Sets titles on the country edit page
     * @param array $country
     */
    protected function setTitleEdit(array $country)
    {
        if (isset($country['name'])) {
            $title = $this->text('Edit country %name', array('%name' => $country['name']));
        } else {
            $title = $this->text('Add country');
        }

        $this->setTitle($title);
    }

    /**
     * Sets breadcrumbs on the country edit page
     */
    protected function setBreadcrumbEdit()
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
    protected function get($country_code)
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
    protected function delete(array $country)
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
    protected function action()
    {
        $value = (int) $this->request->post('value');
        $action = (string) $this->request->post('action');
        $selected = (array) $this->request->post('selected', array());

        if ($action === 'weight' && $this->access('country_edit')) {
            foreach ($selected as $code => $weight) {
                $this->country->update($code, array('weight' => $weight));
            }

            $this->response->json(array('success' => $this->text('Countries have been reordered')));
        }

        $updated = $deleted = 0;

        foreach ($selected as $code) {

            if ($action === 'status' && $this->access('country_edit')) {
                $updated += (int) $this->country->update($code, array('status' => (int) $value));
            }

            if ($action === 'delete' && $this->access('country_delete')) {
                $deleted += (int) $this->country->delete($code);
            }
        }

        if ($updated > 0) {
            $this->session->setMessage($this->text('Countries have been updated'), 'success');
            return true;
        }

        if ($deleted > 0) {
            $this->session->setMessage($this->text('Countries have been deleted'), 'success');
            return true;
        }

        return false;
    }

    /**
     * Saves a country
     * @param array $country
     * @return null
     */
    protected function submit(array $country)
    {
        $this->setSubmitted('country');
        $this->validate($country);

        if ($this->hasErrors('country')) {
            return;
        }

        if (isset($country['code'])) {
            $this->controlAccess('country_edit');
            $this->country->update($country['code'], $this->getSubmitted());
            $this->redirect('admin/settings/country', $this->text('Country has been updated'), 'success');
        }

        $this->controlAccess('country_add');
        $this->country->add($this->getSubmitted());
        $this->redirect('admin/settings/country', $this->text('Country has been added'), 'success');
    }

    /**
     * Validates a country data
     * @param array $country
     */
    protected function validate(array $country)
    {
        $this->setSubmittedBool('status');
        $this->setSubmittedBool('default');

        if ($this->getSubmitted('default')) {
            $this->setSubmitted('status', 1);
        }

        $this->addValidator('code', array(
            'regexp' => array('pattern' => '/^[a-zA-Z]{2}$/'),
            'country_code' => array()));

        $this->addValidator('name', array(
            'length' => array('min' => 1, 'max' => 255)));

        $this->addValidator('native_name', array(
            'length' => array('min' => 1, 'max' => 255)));

        $this->addValidator('weight', array(
            'numeric' => array(),
            'length' => array('max' => 2)));

        $errors = $this->setValidators($country);

        if (empty($errors)) {
            $this->setSubmitted('code', strtoupper($this->getSubmitted('code')));
        }
    }

    /**
     * Renders the country format page
     */
    protected function outputFormat()
    {
        $this->output('settings/country/format');
    }

    /**
     * Sets titles on the county formats page
     * @param array $country
     */
    protected function setTitleFormat(array $country)
    {
        $this->setTitle($this->text('Address format of %country', array(
                    '%country' => $country['name'])));
    }

    /**
     * Sets breadcrumbs on the country format edit page
     */
    protected function setBreadcrumbFormat()
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
    protected function submitFormat(array $country)
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
