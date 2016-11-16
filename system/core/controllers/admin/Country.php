<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers\admin;

use core\models\Zone as ModelsZone;
use core\models\Country as ModelsCountry;
use core\controllers\admin\Controller as BackendController;

/**
 * Handles incoming requests and outputs data related to countries
 */
class Country extends BackendController
{

    /**
     * Country model instance
     * @var \core\models\Country $country
     */
    protected $country;

    /**
     * Zone model instance
     * @var \core\models\Zone $zone
     */
    protected $zone;

    /**
     * Constructor
     * @param ModelsCountry $country
     * @param ModelsZone $zone
     */
    public function __construct(ModelsCountry $country, ModelsZone $zone)
    {
        parent::__construct();

        $this->zone = $zone;
        $this->country = $country;
    }

    /**
     * Displays the country overview page
     */
    public function listCountry()
    {
        $this->actionCountry();

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
     * Applies an action to the selected countries
     * @return null|void
     */
    protected function actionCountry()
    {
        $action = (string) $this->request->post('action');

        if (empty($action)) {
            return null;
        }

        $value = (int) $this->request->post('value');
        $selected = (array) $this->request->post('selected', array());

        if ($action === 'weight' && $this->access('country_edit')) {
            return $this->updateWeight($selected);
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
            $message = $this->text('Countries have been updated');
            $this->setMessage($message, 'success', true);
        }

        if ($deleted > 0) {
            $message = $this->text('Countries have been deleted');
            $this->setMessage($message, 'success', true);
        }

        return null;
    }

    /**
     * Updates an array of country with a new weight
     * @param array $items
     */
    protected function updateWeight(array $items)
    {
        foreach ($items as $code => $weight) {
            $this->country->update($code, array('weight' => $weight));
        }

        $response = array('success' => $this->text('Countries have been reordered'));
        $this->response->json($response);
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
        $breadcrumb = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')
        );

        $this->setBreadcrumb($breadcrumb);
    }

    /**
     * Renders the country overview page
     */
    protected function outputListCountry()
    {
        $this->output('settings/country/list');
    }

    /**
     * Displays the country add/edit form
     * @param string|null $code
     */
    public function editCountry($code = null)
    {
        $country = $this->getCountry($code);
        $zones = $this->getZonesCountry();

        $can_delete = (!empty($code)//
                && $this->access('country_delete')//
                && $this->country->canDelete($code));

        $this->setData('code', $code);
        $this->setData('zones', $zones);
        $this->setData('country', $country);
        $this->setData('can_delete', $can_delete);

        $this->submitCountry($country);

        $this->setTitleEditCountry($country);
        $this->setBreadcrumbEditCountry();
        $this->outputEditCountry();
    }

    /**
     * Returns an array of enabled zones
     * @return type
     */
    protected function getZonesCountry()
    {
        return $this->zone->getList(array('status' => 1));
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
     * Saves a submitted country data
     * @param array $country
     * @return mixed
     */
    protected function submitCountry(array $country)
    {
        if ($this->isPosted('delete')) {
            return $this->deleteCountry($country);
        }

        if (!$this->isPosted('save')) {
            return null;
        }

        $this->setSubmitted('country');
        $this->validateCountry($country);

        if ($this->hasErrors('country')) {
            return null;
        }

        if (isset($country['code'])) {
            return $this->updateCountry($country);
        }

        return $this->addCountry();
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
            $message = $this->text('Country has been deleted');
            $this->redirect('admin/settings/country', $message, 'success');
        }

        $message = $this->text('Cannot delete this country');
        $this->redirect('', $message, 'danger');
    }

    /**
     * Validates a country data
     * @param array $country
     */
    protected function validateCountry(array $country)
    {
        $this->setSubmittedBool('status');
        $this->setSubmittedBool('default');
        $this->setSubmitted('update', $country);
        $this->validate('country');
    }

    /**
     * Updates a country
     * @param array $country
     */
    protected function updateCountry(array $country)
    {
        $this->controlAccess('country_edit');

        $submitted = $this->getSubmitted();
        $this->country->update($country['code'], $submitted);

        $message = $this->text('Country has been updated');
        $this->redirect('admin/settings/country', $message, 'success');
    }

    /**
     * Adds a new country
     */
    protected function addCountry()
    {
        $this->controlAccess('country_add');

        $values = $this->getSubmitted();
        $this->country->add($values);

        $message = $this->text('Country has been added');
        $this->redirect('admin/settings/country', $message, 'success');
    }

    /**
     * Sets titles on the country edit page
     * @param array $country
     */
    protected function setTitleEditCountry(array $country)
    {
        $title = $this->text('Add country');

        if (isset($country['name'])) {
            $title = $this->text('Edit country %name', array('%name' => $country['name']));
        }

        $this->setTitle($title);
    }

    /**
     * Sets breadcrumbs on the country edit page
     */
    protected function setBreadcrumbEditCountry()
    {
        $breadcrumbs = array();

        $breadcrumbs[] = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')
        );

        $breadcrumbs[] = array(
            'url' => $this->url('admin/settings/country'),
            'text' => $this->text('Countries')
        );

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Renders the country edit page
     */
    protected function outputEditCountry()
    {
        $this->output('settings/country/edit');
    }

    /**
     * Displays the address format items for a given country
     * @param string $country_code
     */
    public function formatCountry($country_code)
    {
        $country = $this->getCountry($country_code);
        $this->setData('format', $country['format']);

        $this->submitFormatCountry($country);
        $this->setTitleFormatCountry($country);
        $this->setBreadcrumbFormatCountry();
        $this->outputFormatCountry();
    }

    /**
     * Saves a country format
     * @param array $country
     */
    protected function submitFormatCountry(array $country)
    {
        if ($this->isPosted('save')) {
            $this->controlAccess('country_format_edit');
            $this->setSubmitted('format');
            $this->updateFormatCountry($country);
        }
    }

    /**
     * Updates a country format
     * @param array $country
     */
    protected function updateFormatCountry(array $country)
    {
        $format = $this->getSubmitted();

        // Fix checkboxes, enable required fields
        foreach ($format as $id => &$item) {

            $item['required'] = isset($item['required']);
            $item['status'] = isset($item['status']);

            if ($id === 'country') {
                $item['status'] = 1;
                $item['required'] = 1;
            }

            if ($item['required']) {
                $item['status'] = 1; // Required fields are always enabled
            }
        }

        $this->country->update($country['code'], array('format' => $format));

        $message = $this->text('Country has been updated');
        $this->redirect('admin/settings/country', $message, 'success');
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
        $breadcrumbs = array();

        $breadcrumbs[] = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')
        );

        $breadcrumbs[] = array(
            'url' => $this->url('admin/settings/country'),
            'text' => $this->text('Countries')
        );

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Renders the country format page
     */
    protected function outputFormatCountry()
    {
        $this->output('settings/country/format');
    }

}
