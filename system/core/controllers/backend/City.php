<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\backend;

use gplcart\core\models\Zone as ZoneModel;
use gplcart\core\models\City as CityModel;
use gplcart\core\models\State as StateModel;
use gplcart\core\models\Country as CountryModel;
use gplcart\core\controllers\backend\Controller as BackendController;

/**
 * Handles incoming requests and outputs data related to cities
 */
class City extends BackendController
{

    /**
     * Country model instance
     * @var \gplcart\core\models\Country $country
     */
    protected $country;

    /**
     * State model instance
     * @var \gplcart\core\models\State $state
     */
    protected $state;

    /**
     * City model instance
     * @var \gplcart\core\models\City $city
     */
    protected $city;

    /**
     * Zone model instance
     * @var \gplcart\core\models\Zone $zone
     */
    protected $zone;

    /**
     * The current country state
     * @var array
     */
    protected $data_state = array();

    /**
     * The current city
     * @var array
     */
    protected $data_city = array();

    /**
     * The current country
     * @var array
     */
    protected $data_country = array();

    /**
     * Constructor
     * @param CountryModel $country
     * @param StateModel $state
     * @param CityModel $city
     * @param ZoneModel $zone
     */
    public function __construct(CountryModel $country, StateModel $state,
            CityModel $city, ZoneModel $zone)
    {
        parent::__construct();

        $this->zone = $zone;
        $this->city = $city;
        $this->state = $state;
        $this->country = $country;
    }

    /**
     * Displays the city overview page
     * @param string $country_code
     * @param integer $state_id
     */
    public function listCity($country_code, $state_id)
    {
        $this->setStateCity($state_id);
        $this->setCountryCity($country_code);

        $this->actionCity();

        $this->setTitleListCity();
        $this->setBreadcrumbListCity();

        $query = $this->getFilterQuery();
        $allowed = array('city_id', 'name', 'status');
        $this->setFilter($allowed, $query);

        $total = $this->getTotalCity($state_id, $query);
        $limit = $this->setPager($total, $query);

        $this->setData('state', $this->data_state);
        $this->setData('country', $this->data_country);
        $this->setData('cities', $this->getListCity($limit, $query, $state_id));

        $this->outputListCity();
    }

    /**
     * Returns an array of state data
     * @param integer $state_id
     * @return array
     */
    protected function setStateCity($state_id)
    {
        $state = $this->state->get($state_id);

        if (empty($state)) {
            $this->outputHttpStatus(404);
        }

        $this->data_state = $state;
        return $state;
    }

    /**
     * Returns an array of country data
     * @param string $country_code
     * @return array
     */
    protected function setCountryCity($country_code)
    {
        $country = $this->country->get($country_code);

        if (empty($country)) {
            $this->outputHttpStatus(404);
        }

        $this->data_country = $country;
        return $country;
    }

    /**
     * Applies an action to the selected cities
     * @return null
     */
    protected function actionCity()
    {
        $action = (string) $this->request->post('action');

        if (empty($action)) {
            return null;
        }

        $value = (int) $this->request->post('value');
        $selected = (array) $this->request->post('selected', array());

        $deleted = $updated = 0;
        foreach ($selected as $id) {

            if ($action === 'status' && $this->access('city_edit')) {
                $updated += (int) $this->city->update($id, array('status' => $value));
            }

            if ($action === 'delete' && $this->access('city_delete')) {
                $deleted += (int) $this->city->delete($id);
            }
        }

        if ($updated > 0) {
            $message = $this->text('Cities have been updated');
            $this->setMessage($message, 'success', true);
        }

        if ($deleted > 0) {
            $message = $this->text('Cities have been deleted');
            $this->setMessage($message, 'success', true);
        }
    }

    /**
     * Returns total number of cities depending on various conditions
     * @param integer $state_id
     * @param array $query
     * @return integer
     */
    protected function getTotalCity($state_id, array $query)
    {
        $options = array(
            'count' => true, 'state_id' => $state_id);

        $options += $query;
        return (int) $this->city->getList($options);
    }

    /**
     * Returns an array of cities
     * @param array $limit
     * @param array $query
     * @param integer $state_id
     * @return array
     */
    protected function getListCity(array $limit, array $query, $state_id)
    {
        $options = array(
            'limit' => $limit, 'state_id' => $state_id);

        $options += $query;
        return $this->city->getList($options);
    }

    /**
     * Sets title on the city overview page
     */
    protected function setTitleListCity()
    {
        $vars = array('%state' => $this->data_state['name']);
        $text = $this->text('Cities of state %state', $vars);
        $this->setTitle($text);
    }

    /**
     * Sets breadcrumbs on the city overview page
     */
    protected function setBreadcrumbListCity()
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

        $breadcrumbs[] = array(
            'url' => $this->url("admin/settings/states/{$this->data_country['code']}"),
            'text' => $this->text('States of %country', array('%country' => $this->data_country['name']))
        );

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Renders the city overview page
     */
    protected function outputListCity()
    {
        $this->output('settings/city/list');
    }

    /**
     * Displays the city edit page
     * @param string $country_code
     * @param integer $state_id
     * @param null|integer $city_id
     */
    public function editCity($country_code, $state_id, $city_id = null)
    {
        $this->setCity($city_id);
        $this->setStateCity($state_id);
        $this->setCountryCity($country_code);

        $this->setTitleEditCity();
        $this->setBreadcrumbEditCity();

        $this->setData('city', $this->data_city);
        $this->setData('state', $this->data_state);
        $this->setData('country', $this->data_country);
        $this->setData('zones', $this->getZonesCity());

        $this->submitCity();
        $this->outputEditCity();
    }

    /**
     * Returns an array of enabled zones
     * @return array
     */
    protected function getZonesCity()
    {
        return $this->zone->getList(array('status' => 1));
    }

    /**
     * Returns an array of city data
     * @param integer $city_id
     * @return array
     */
    protected function setCity($city_id)
    {
        if (!is_numeric($city_id)) {
            return array();
        }

        $city = $this->city->get($city_id);

        if (empty($city)) {
            $this->outputHttpStatus(404);
        }

        $this->data_city = $city;
        return $city;
    }

    /**
     * Saves an array of submitted city
     * @return null
     */
    protected function submitCity()
    {
        if ($this->isPosted('delete')) {
            $this->deleteCity();
            return null;
        }

        if (!$this->isPosted('save')) {
            return null;
        }

        $this->setSubmitted('city');
        $this->validateCity();

        if ($this->hasErrors('city')) {
            return null;
        }

        if (isset($this->data_city['city_id'])) {
            $this->updateCity();
            return null;
        }

        $this->addCity();
    }

    /**
     * Deletes a city
     */
    protected function deleteCity()
    {
        $this->controlAccess('city_delete');

        $deleted = $this->city->delete($this->data_city['city_id']);

        if ($deleted) {

            $url = "admin/settings/cities/{$this->data_country['code']}/{$this->data_state['state_id']}";
            $vars = array('%name' => $this->data_city['name']);
            $message = $this->text('City %name has been deleted', $vars);

            $this->redirect($url, $message, 'success');
        }

        $vars = array('%name' => $city['name']);
        $message = $this->text('Cannot delete city %name.', $vars);

        $this->redirect('', $message, 'warning');
    }

    /**
     * Validates an array of submitted city data
     */
    protected function validateCity()
    {
        $this->setSubmittedBool('status');
        $this->setSubmitted('update', $this->data_city);
        $this->setSubmitted('country', $this->data_country['code']);
        $this->setSubmitted('state_id', $this->data_state['state_id']);

        $this->validate('city');
    }

    /**
     * Updates a city
     */
    protected function updateCity()
    {
        $this->controlAccess('city_edit');

        $this->city->update($this->data_city['city_id'], $this->getSubmitted());

        $url = "admin/settings/cities/{$this->data_country['code']}/{$this->data_state['state_id']}";
        $vars = array('%name' => $this->data_city['name']);
        $message = $this->text('City %name has been updated', $vars);

        $this->redirect($url, $message, 'success');
    }

    /**
     * Adds a new city
     */
    protected function addCity()
    {
        $this->controlAccess('city_add');

        $this->city->add($this->getSubmitted());

        $url = "admin/settings/cities/{$this->data_country['code']}/{$this->data_state['state_id']}";
        $message = $this->text('City has been added');

        $this->redirect($url, $message, 'success');
    }

    /**
     * Sets titles on the city edit page
     */
    protected function setTitleEditCity()
    {
        $title = $this->text('Add city');

        if (isset($this->data_city['city_id'])) {
            $vars = array('%name' => $this->data_city['name']);
            $title = $this->text('Edit city %name', $vars);
        }

        $this->setTitle($title);
    }

    /**
     * Sets breadcrumbs on the city edit page
     */
    protected function setBreadcrumbEditCity()
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
     * Renders the city edit page
     */
    protected function outputEditCity()
    {
        $this->output('settings/city/edit');
    }

}
