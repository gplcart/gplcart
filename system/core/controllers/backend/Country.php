<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\backend;

use gplcart\core\models\Zone as ZoneModel;
use gplcart\core\models\Country as CountryModel;
use gplcart\core\controllers\backend\Controller as BackendController;

/**
 * Handles incoming requests and outputs data related to countries
 */
class Country extends BackendController
{

    /**
     * Country model instance
     * @var \gplcart\core\models\Country $country
     */
    protected $country;

    /**
     * Zone model instance
     * @var \gplcart\core\models\Zone $zone
     */
    protected $zone;

    /**
     * The current country
     * @var array
     */
    protected $data_country = array();

    /**
     * Constructor
     * @param CountryModel $country
     * @param ZoneModel $zone
     */
    public function __construct(CountryModel $country, ZoneModel $zone)
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

        $this->setTitleListCountry();
        $this->setBreadcrumbListCountry();

        $query = $this->getFilterQuery();
        $total = $this->getTotalCountry($query);
        $limit = $this->setPager($total, $query);

        $this->setData('countries', $this->getListCountry($limit, $query));

        $allowed = array('name', 'native_name', 'code', 'status', 'weight');
        $this->setFilter($allowed, $query);

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
            return $this->updateWeightCountry($selected);
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
    protected function updateWeightCountry(array $items)
    {
        foreach ($items as $code => $weight) {
            $this->country->update($code, array('weight' => $weight));
        }

        $response = array('success' => $this->text('Items have been reordered'));
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
        return (int) $this->country->getList($query);
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
        $this->setCountry($code);

        $this->setTitleEditCountry();
        $this->setBreadcrumbEditCountry();

        $this->setData('code', $code);
        $this->setData('zones', $this->getZonesCountry());
        $this->setData('country', $this->data_country);
        $this->setData('can_delete', $this->canDeleteCountry());

        $this->submitCountry();
        $this->outputEditCountry();
    }

    /**
     * Whether the current country can be deleted
     * @return bool
     */
    protected function canDeleteCountry()
    {
        return isset($this->data_country['code'])//
                && $this->access('country_delete')//
                && $this->country->canDelete($this->data_country['code']);
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
    protected function setCountry($country_code)
    {
        if (empty($country_code)) {
            return array();
        }

        $country = $this->country->get($country_code);

        if (empty($country)) {
            $this->outputHttpStatus(404);
        }

        return $this->data_country = $country;
    }

    /**
     * Saves a submitted country data
     * @return null
     */
    protected function submitCountry()
    {
        if ($this->isPosted('delete')) {
            $this->deleteCountry();
            return null;
        }

        if (!$this->isPosted('save') || !$this->validateCountry()) {
            return null;
        }

        if (isset($this->data_country['code'])) {
            $this->updateCountry();
        } else {
            $this->addCountry();
        }
    }

    /**
     * Validates a country data
     * @return bool
     */
    protected function validateCountry()
    {
        $this->setSubmitted('country');

        $this->setSubmittedBool('status');
        $this->setSubmitted('update', $this->data_country);

        $this->validate('country');

        return !$this->hasErrors('country');
    }

    /**
     * Deletes a country
     */
    protected function deleteCountry()
    {
        $this->controlAccess('country_delete');

        $deleted = $this->country->delete($this->data_country['code']);

        if ($deleted) {
            $message = $this->text('Country has been deleted');
            $this->redirect('admin/settings/country', $message, 'success');
        }

        $message = $this->text('Unable to delete this country');
        $this->redirect('', $message, 'danger');
    }

    /**
     * Updates a country
     */
    protected function updateCountry()
    {
        $this->controlAccess('country_edit');

        $submitted = $this->getSubmitted();
        $this->country->update($this->data_country['code'], $submitted);

        $message = $this->text('Country has been updated');
        $this->redirect('admin/settings/country', $message, 'success');
    }

    /**
     * Adds a new country
     */
    protected function addCountry()
    {
        $this->controlAccess('country_add');

        $this->country->add($this->getSubmitted());

        $message = $this->text('Country has been added');
        $this->redirect('admin/settings/country', $message, 'success');
    }

    /**
     * Sets titles on the country edit page
     */
    protected function setTitleEditCountry()
    {
        $title = $this->text('Add country');

        if (isset($this->data_country['name'])) {
            $vars = array('%name' => $this->data_country['name']);
            $title = $this->text('Edit country %name', $vars);
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
        $this->setCountry($country_code);

        $this->setTitleFormatCountry();
        $this->setBreadcrumbFormatCountry();

        $this->setData('format', $this->data_country['format']);

        $this->submitFormatCountry();
        $this->outputFormatCountry();
    }

    /**
     * Saves a country format
     */
    protected function submitFormatCountry()
    {
        if ($this->isPosted('save')) {
            $this->controlAccess('country_format_edit');
            $this->setSubmitted('format');
            $this->updateFormatCountry();
        }
    }

    /**
     * Updates a country format
     */
    protected function updateFormatCountry()
    {
        $format = $this->getSubmitted();

        // Fix checkboxes, enable required fields
        foreach ($format as $id => &$item) {

            $item['status'] = isset($item['status']);
            $item['required'] = isset($item['required']);

            if ($id === 'country') {
                $item['status'] = 1;
                $item['required'] = 1;
            }

            if ($item['required']) {
                $item['status'] = 1;
            }
        }

        $this->country->update($this->data_country['code'], array('format' => $format));

        $message = $this->text('Country has been updated');
        $this->redirect('admin/settings/country', $message, 'success');
    }

    /**
     * Sets titles on the county formats page
     */
    protected function setTitleFormatCountry()
    {
        $vars = array('%name' => $this->data_country['name']);
        $text = $this->text('Address format of %name', $vars);
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
