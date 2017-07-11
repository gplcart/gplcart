<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\backend;

use gplcart\core\models\Zone as ZoneModel,
    gplcart\core\models\Country as CountryModel;
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
        $this->actionListCountry();

        $this->setTitleListCountry();
        $this->setBreadcrumbListCountry();

        $this->setFilterListCountry();
        $this->setTotalListCountry();
        $this->setPagerLimit();

        $this->setData('countries', $this->getListCountry());
        $this->outputListCountry();
    }

    /**
     * Set filter on the country overview page
     */
    protected function setFilterListCountry()
    {
        $allowed = array('name', 'native_name', 'code', 'status', 'weight');
        $this->setFilter($allowed);
    }

    /**
     * Applies an action to the selected countries
     */
    protected function actionListCountry()
    {
        $value = $this->getPosted('value', '', true, 'string');
        $action = $this->getPosted('action', '', true, 'string');
        $selected = $this->getPosted('selected', array(), true, 'array');

        if (empty($action)) {
            return null;
        }

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
            $message = $this->text('Updated %num items', array('%num' => $updated));
            $this->setMessage($message, 'success', true);
        }

        if ($deleted > 0) {
            $message = $this->text('Deleted %num items', array('%num' => $deleted));
            $this->setMessage($message, 'success', true);
        }
    }

    /**
     * Updates an array of country weights
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
     * Set a total number of countries
     */
    protected function setTotalListCountry()
    {
        $query = $this->query_filter;
        $query['count'] = true;
        $this->total = (int) $this->country->getList($query);
    }

    /**
     * Returns an array of countries
     * @return array
     */
    protected function getListCountry()
    {
        $query = $this->query_filter;
        $query['limit'] = $this->limit;
        return (array) $this->country->getList($query);
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
     * Displays the country edit form
     * @param string|null $code
     */
    public function editCountry($code = null)
    {
        $this->setCountry($code);

        $this->setTitleEditCountry();
        $this->setBreadcrumbEditCountry();

        $this->setData('code', $code);
        $this->setData('country', $this->data_country);
        $this->setData('zones', $this->getZonesCountry());
        $this->setData('can_delete', $this->canDeleteCountry());

        $this->submitEditCountry();
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
     * @return array
     */
    protected function getZonesCountry()
    {
        return $this->zone->getList(array('status' => 1));
    }

    /**
     * Set an array of country data
     * @param string $country_code
     */
    protected function setCountry($country_code)
    {
        if (!empty($country_code)) {
            $this->data_country = $this->country->get($country_code);
            if (empty($this->data_country)) {
                $this->outputHttpStatus(404);
            }
        }
    }

    /**
     * Saves a submitted country data
     */
    protected function submitEditCountry()
    {
        if ($this->isPosted('delete')) {
            $this->deleteCountry();
            return null;
        }

        if (!$this->isPosted('save') || !$this->validateEditCountry()) {
            return null;
        }

        if (isset($this->data_country['code'])) {
            $this->updateCountry();
        } else {
            $this->addCountry();
        }
    }

    /**
     * Validates a submitted country data
     * @return bool
     */
    protected function validateEditCountry()
    {
        $this->setSubmitted('country');
        $this->setSubmittedBool('status');
        $this->setSubmitted('update', $this->data_country);

        $this->validateComponent('country');

        return !$this->hasErrors();
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
     * Render and output the country edit page
     */
    protected function outputEditCountry()
    {
        $this->output('settings/country/edit');
    }

    /**
     * Displays address format items for the given country
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
     * Sets titles on the country format edit page
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
     * Render and output the country format edit page
     */
    protected function outputFormatCountry()
    {
        $this->output('settings/country/format');
    }

}
