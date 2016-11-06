<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers\admin;

use core\models\City as ModelsCity;
use core\models\Country as ModelsCountry;
use core\models\State as ModelsState;
use core\models\Zone as ModelsZone;
use core\controllers\admin\Controller as BackendController;

/**
 * Handles incoming requests and outputs data related to cities
 */
class City extends BackendController
{

    /**
     * Country model instance
     * @var \core\models\Country $country
     */
    protected $country;

    /**
     * State model instance
     * @var \core\models\State $state
     */
    protected $state;

    /**
     * City model instance
     * @var \core\models\City $city
     */
    protected $city;

    /**
     * Zone model instance
     * @var \core\models\Zone $zone
     */
    protected $zone;

    /**
     * Constructor
     * @param ModelsCountry $country
     * @param ModelsState $state
     * @param ModelsCity $city
     * @param ModelsZone $zone
     */
    public function __construct(ModelsCountry $country, ModelsState $state,
            ModelsCity $city, ModelsZone $zone)
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
        $state = $this->getStateCity($state_id);
        $country = $this->getCountryCity($country_code);

        $this->actionCity();

        $query = $this->getFilterQuery();
        $total = $this->getTotalCity($state_id, $query);
        $limit = $this->setPager($total, $query);
        $cities = $this->getListCity($limit, $query, $state_id);

        $this->setData('state', $state);
        $this->setData('cities', $cities);
        $this->setData('country', $country);

        $allowed = array('city_id', 'name', 'status');
        $this->setFilter($allowed, $query);

        $this->setTitleListCity($state);
        $this->setBreadcrumbListCity($country);
        $this->outputListCity();
    }

    /**
     * Returns an array of state data
     * @param integer $state_id
     * @return array
     */
    protected function getStateCity($state_id)
    {
        $state = $this->state->get($state_id);

        if (empty($state)) {
            $this->outputError(404);
        }

        return $state;
    }

    /**
     * Returns an array of country data
     * @param string $country_code
     * @return array
     */
    protected function getCountryCity($country_code)
    {
        $country = $this->country->get($country_code);

        if (empty($country)) {
            $this->outputError(404);
        }

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

        return null;
    }

    /**
     * Returns total number of cities depending on various conditions
     * @param integer $state_id
     * @param array $query
     * @return integer
     */
    protected function getTotalCity($state_id, array $query)
    {
        $options = array('count' => true, 'state_id' => $state_id);
        $options += $query;
        return $this->city->getList($options);
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
        $options = array('limit' => $limit, 'state_id' => $state_id);
        $options += $query;
        return $this->city->getList($options);
    }

    /**
     * Sets title on the city overview page
     * @param array $state
     */
    protected function setTitleListCity(array $state)
    {
        $text = $this->text('Cities of state %state', array(
            '%state' => $state['name']
        ));

        $this->setTitle($text);
    }

    /**
     * Sets breadcrumbs on the city overview page
     * @param array $country
     */
    protected function setBreadcrumbListCity(array $country)
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
            'url' => $this->url("admin/settings/states/{$country['code']}"),
            'text' => $this->text('States of %country', array(
                '%country' => $country['name']
            ))
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
        $city = $this->getCity($city_id);
        $state = $this->getStateCity($state_id);
        $country = $this->getCountryCity($country_code);
        $zones = $this->zone->getList(array('status' => 1));

        $this->setData('city', $city);
        $this->setData('state', $state);
        $this->setData('zones', $zones);
        $this->setData('country', $country);

        $this->submitCity($country, $state, $city);

        $this->setTitleEditCity($city);
        $this->setBreadcrumbEditCity();
        $this->outputEditCity();
    }

    /**
     * Returns an array of city data
     * @param integer $city_id
     * @return array
     */
    protected function getCity($city_id)
    {
        if (!is_numeric($city_id)) {
            return array();
        }

        $city = $this->city->get($city_id);

        if (empty($city)) {
            $this->outputError(404);
        }

        return $city;
    }

    /**
     * Saves an array of submitted city
     * @param array $country
     * @param array $state
     * @param array $city
     * @return mixed
     */
    protected function submitCity(array $country, array $state, array $city)
    {
        if ($this->isPosted('delete')) {
            return $this->deleteCity($country, $state, $city);
        }

        if (!$this->isPosted('save')) {
            return null;
        }

        $this->setSubmitted('city');
        $this->validateCity($country, $state, $city);

        if ($this->hasErrors('city')) {
            return null;
        }

        if (isset($city['city_id'])) {
            return $this->updateCity($country, $state, $city);
        }

        return $this->addCity($country, $state);
    }

    /**
     * Deletes a city
     * @param array $country
     * @param array $state
     * @param array $city
     */
    protected function deleteCity(array $country, array $state, array $city)
    {
        $this->controlAccess('city_delete');

        $deleted = $this->city->delete($city['city_id']);

        if ($deleted) {

            $url = "admin/settings/cities/{$country['code']}/{$state['state_id']}";
            $message = $this->text('City %name has been deleted', array(
                '%name' => $city['name']
            ));

            $this->redirect($url, $message, 'success');
        }

        $message = $this->text('Cannot delete city %name.', array(
            '%name' => $city['name']
        ));

        $this->redirect('', $message, 'warning');
    }

    /**
     * Validates an array of submitted city data
     * @param array $country
     * @param array $state
     * @param array $city
     */
    protected function validateCity(array $country, array $state, array $city)
    {
        $this->setSubmittedBool('status');
        $this->setSubmitted('update', $city);
        $this->setSubmitted('country', $country['code']);
        $this->setSubmitted('state_id', $state['state_id']);

        $this->validate('city');
    }

    /**
     * Updates a city
     * @param array $country
     * @param array $state
     * @param array $city
     */
    protected function updateCity(array $country, array $state, array $city)
    {
        $this->controlAccess('city_edit');

        $submitted = $this->getSubmitted();
        $this->city->update($city['city_id'], $submitted);

        $url = "admin/settings/cities/{$country['code']}/{$state['state_id']}";
        $message = $this->text('City %name has been updated', array(
            '%name' => $city['name']
        ));

        $this->redirect($url, $message, 'success');
    }

    /**
     * Adds a new city
     * @param array $country
     * @param array $state
     */
    protected function addCity(array $country, array $state)
    {
        $this->controlAccess('city_add');

        $submitted = $this->getSubmitted();
        $this->city->add($submitted);

        $url = "admin/settings/cities/{$country['code']}/{$state['state_id']}";
        $message = $this->text('City has been added');

        $this->redirect($url, $message, 'success');
    }

    /**
     * Sets titles on the city edit page
     * @param array $city
     */
    protected function setTitleEditCity(array $city)
    {
        if (isset($city['city_id'])) {
            $title = $this->text('Edit city %name', array('%name' => $city['name']));
        } else {
            $title = $this->text('Add city');
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
