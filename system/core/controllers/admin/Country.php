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
        $action = (string) $this->request->post('action');
        $value = (int) $this->request->post('value');
        $selected = $this->request->post('selected', array());

        if (!empty($action)) {
            $this->action($selected, $action, $value);
        }

        $query = $this->getFilterQuery();
        $limit = $this->setPager($this->getTotalCountries($query), $query);

        $this->data['countries'] = $this->getCountries($limit, $query);
        $this->data['default_country'] = $this->country->getDefault();

        $this->setFilter(array('name', 'native_name', 'code', 'status', 'weight'), $query);

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

        $this->data['country'] = $country;

        if ($this->request->post('delete')) {
            $this->delete($country);
        }

        if ($this->request->post('save')) {
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
        $this->data['format'] = $country['format'];

        if ($this->request->post('save')) {
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
        $this->setBreadcrumb(array('url' => $this->url('admin'), 'text' => $this->text('Dashboard')));
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
        $this->setBreadcrumb(array('url' => $this->url('admin'), 'text' => $this->text('Dashboard')));
        $this->setBreadcrumb(array('url' => $this->url('admin/settings/country'), 'text' => $this->text('Countries')));
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
     * @return null
     */
    protected function delete(array $country)
    {
        if (empty($country['code'])) {
            return;
        }

        $this->controlAccess('country_delete');

        if (!empty($country['default'])) {
            $this->redirect('', $this->text('You cannot delete default country'), 'danger');
        }

        $this->country->delete($country['code']);
        $this->redirect('admin/settings/country', $this->text('Country has been deleted'), 'success');
    }

    /**
     * Applies an action to the selected countries
     * @param array $selected
     * @param string $action
     * @param string $value
     * @return boolean
     */
    protected function action(array $selected, $action, $value)
    {
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
        $this->submitted = $this->request->post('country', array());

        $this->validate($country);

        $errors = $this->formErrors();

        if (!empty($errors)) {
            $this->data['country'] = $this->submitted;
            return;
        }

        if (isset($country['code'])) {
            $this->controlAccess('country_edit');
            $this->country->update($country['code'], $this->submitted);
            $this->redirect('admin/settings/country', $this->text('Country has been updated'), 'success');
        }

        $this->controlAccess('country_add');
        $this->country->add($this->submitted);
        $this->redirect('admin/settings/country', $this->text('Country has been added'), 'success');
    }

    /**
     * Validates a country data
     * @param array $country
     */
    protected function validate(array $country)
    {
        $this->submitted['status'] = !empty($this->submitted['status']);
        $this->submitted['default'] = !empty($this->submitted['default']);

        if (!empty($this->submitted['default'])) {
            $this->submitted['status'] = 1;
        }

        $this->validateCode($country);
        $this->validateName();
        $this->validateWeight();
    }

    /**
     * Validates a country code
     * @param array $country
     * @return boolean
     */
    protected function validateCode(array $country)
    {
        if (!preg_match('/^[a-zA-Z]{2}$/', $this->submitted['code'])) {
            $this->errors['code'] = $this->text('Invalid country code. You must use only 2-digit ISO 3166-2 codes');
            return false;
        }

        if (empty($country['code']) || $country['code'] !== $this->submitted['code']) {
            if ($this->country->get($this->submitted['code'])) {
                $this->errors['code'] = $this->text('This country code already exists');
                return false;
            }
        }

        $this->submitted['code'] = strtoupper($this->submitted['code']);
        return true;
    }

    /**
     * Validates country names
     */
    protected function validateName()
    {
        if (empty($this->submitted['name']) || mb_strlen($this->submitted['name']) > 255) {
            $this->errors['name'] = $this->text('Content must be %min - %max characters long', array('%min' => 1, '%max' => 255));
        }

        if (empty($this->submitted['native_name']) || mb_strlen($this->submitted['native_name']) > 255) {
            $this->errors['native_name'] = $this->text('Content must be %min - %max characters long', array('%min' => 1, '%max' => 255));
        }
    }

    /**
     * Validates weight field
     * @return boolean
     */
    protected function validateWeight()
    {
        if ($this->submitted['weight']) {
            if (!is_numeric($this->submitted['weight']) || strlen($this->submitted['weight']) > 2) {
                $this->errors['weight'] = $this->text('Only numeric value and no more than %s digits', array('%s' => 2));
                return false;
            }
            return true;
        }

        $this->submitted['weight'] = 0;
        return true;
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
        $this->setTitle($this->text('Address format of %country', array('%country' => $country['native_name'])));
    }

    /**
     * Sets breadcrumbs on the country format edit page
     */
    protected function setBreadcrumbFormat()
    {
        $this->setBreadcrumb(array('url' => $this->url('admin'), 'text' => $this->text('Dashboard')));
        $this->setBreadcrumb(array('url' => $this->url('admin/settings/country'), 'text' => $this->text('Countries')));
    }

    /**
     * Saves a country format
     * @param array $country
     */
    protected function submitFormat(array $country)
    {
        $this->controlAccess('country_format_edit');
        $format = (array) $this->request->post('format');

        // Fix checkboxes, enable required fields
        foreach ($format as &$item) {
            $item['required'] = isset($item['required']);
            $item['status'] = isset($item['status']);

            if ($item['required']) {
                $item['status'] = 1;
            }
        }

        $this->country->update($country['code'], array('format' => $format));
        $this->redirect('admin/settings/country', $this->text('Country has been updated'), 'success');
    }

}
