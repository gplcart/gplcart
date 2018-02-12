<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\backend;

use gplcart\core\models\City as CityModel;
use gplcart\core\models\Country as CountryModel;
use gplcart\core\models\State as StateModel;
use gplcart\core\models\Zone as ZoneModel;

/**
 * Handles incoming requests and outputs data related to cities
 */
class City extends Controller
{

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
     * Country model instance
     * @var \gplcart\core\models\Country $country
     */
    protected $country;

    /**
     * Pager limit
     * @var array
     */
    protected $data_limit;

    /**
     * An array of country state data
     * @var array
     */
    protected $data_state = array();

    /**
     * An array of city data
     * @var array
     */
    protected $data_city = array();

    /**
     * An array of country data
     * @var array
     */
    protected $data_country = array();

    /**
     * @param CountryModel $country
     * @param StateModel $state
     * @param CityModel $city
     * @param ZoneModel $zone
     */
    public function __construct(CountryModel $country, StateModel $state, CityModel $city, ZoneModel $zone)
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

        $this->actionListCity();

        $this->setTitleListCity();
        $this->setBreadcrumbListCity();
        $this->setFilterListCity();
        $this->setPagerListCity();

        $this->setData('state', $this->data_state);
        $this->setData('country', $this->data_country);
        $this->setData('cities', $this->getListCity());

        $this->outputListCity();
    }

    /**
     * Set filter on the city overview page
     */
    protected function setFilterListCity()
    {
        $this->setFilter(array('city_id', 'name', 'status'));
    }

    /**
     * Sets an array of state data
     * @param integer $state_id
     */
    protected function setStateCity($state_id)
    {
        $this->data_state = $this->state->get($state_id);

        if (empty($this->data_state)) {
            $this->outputHttpStatus(404);
        }
    }

    /**
     * Returns an array of country data
     * @param string $country_code
     */
    protected function setCountryCity($country_code)
    {
        $this->data_country = $this->country->get($country_code);

        if (empty($this->data_country)) {
            $this->outputHttpStatus(404);
        }
    }

    /**
     * Applies an action to the selected cities
     */
    protected function actionListCity()
    {
        list($selected, $action, $value) = $this->getPostedAction();

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
            $message = $this->text('Updated %num item(s)', array('%num' => $updated));
            $this->setMessage($message, 'success');
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
    protected function setPagerListCity()
    {
        $options = $this->query_filter;
        $options['count'] = true;
        $options['state_id'] = $this->data_state['state_id'];

        $pager = array(
            'query' => $this->query_filter,
            'total' => (int) $this->city->getList($options)
        );

        return $this->data_limit = $this->setPager($pager);
    }

    /**
     * Returns an array of cities found for the filter conditions
     * @return array
     */
    protected function getListCity()
    {
        $conditions = $this->query_filter;
        $conditions['limit'] = $this->data_limit;
        $conditions['state_id'] = $this->data_state['state_id'];

        return (array) $this->city->getList($conditions);
    }

    /**
     * Sets title on the city overview page
     */
    protected function setTitleListCity()
    {
        $text = $this->text('Cities of state %name', array('%name' => $this->data_state['name']));
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
            'text' => $this->text('States of %name', array('%name' => $this->data_country['name']))
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
        $this->setData('can_delete', $this->canDeleteCity());

        $this->submitEditCity();
        $this->outputEditCity();
    }

    /**
     * Whether the city can be deleted
     * @return bool
     */
    protected function canDeleteCity()
    {
        return isset($this->data_city['city_id'])
            && $this->access('city_delete')
            && $this->city->canDelete($this->data_city['city_id']);
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
     * Sets an array of city data
     * @param integer $city_id
     */
    protected function setCity($city_id)
    {
        $this->data_city = array();

        if (is_numeric($city_id)) {
            $this->data_city = $this->city->get($city_id);
            if (empty($this->data_city)) {
                $this->outputHttpStatus(404);
            }
        }
    }

    /**
     * Handles a submitted city
     */
    protected function submitEditCity()
    {
        if ($this->isPosted('delete')) {
            $this->deleteCity();
        } else if ($this->isPosted('save') && $this->validateEditCity()) {
            if (isset($this->data_city['city_id'])) {
                $this->updateCity();
            } else {
                $this->addCity();
            }
        }
    }

    /**
     * Validates an array of submitted city data
     * @return bool
     */
    protected function validateEditCity()
    {
        $this->setSubmitted('city');
        $this->setSubmittedBool('status');
        $this->setSubmitted('update', $this->data_city);
        $this->setSubmitted('country', $this->data_country['code']);
        $this->setSubmitted('state_id', $this->data_state['state_id']);

        $this->validateComponent('city');

        return !$this->hasErrors();
    }

    /**
     * Deletes a city
     */
    protected function deleteCity()
    {
        $this->controlAccess('city_delete');

        if ($this->city->delete($this->data_city['city_id'])) {
            $url = "admin/settings/cities/{$this->data_country['code']}/{$this->data_state['state_id']}";
            $this->redirect($url, $this->text('City has been deleted'), 'success');
        }

        $this->redirect('', $this->text('City has not been deleted'), 'warning');
    }

    /**
     * Updates a city
     */
    protected function updateCity()
    {
        $this->controlAccess('city_edit');

        if ($this->city->update($this->data_city['city_id'], $this->getSubmitted())) {
            $url = "admin/settings/cities/{$this->data_country['code']}/{$this->data_state['state_id']}";
            $this->redirect($url, $this->text('City has been updated'), 'success');
        }

        $this->redirect('', $this->text('City has not been updated'), 'warning');
    }

    /**
     * Adds a new city
     */
    protected function addCity()
    {
        $this->controlAccess('city_add');

        if ($this->city->add($this->getSubmitted())) {
            $url = "admin/settings/cities/{$this->data_country['code']}/{$this->data_state['state_id']}";
            $this->redirect($url, $this->text('City has been added'), 'success');
        }

        $this->redirect('', $this->text('City has not been added'), 'warning');
    }

    /**
     * Sets page title on the city edit page
     */
    protected function setTitleEditCity()
    {
        if (isset($this->data_city['city_id'])) {
            $title = $this->text('Edit %name', array('%name' => $this->data_city['name']));
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

        $breadcrumbs[] = array(
            'url' => $this->url("admin/settings/states/{$this->data_country['code']}"),
            'text' => $this->text('States of %name', array('%name' => $this->data_country['name']))
        );

        $breadcrumbs[] = array(
            'url' => $this->url("admin/settings/cities/{$this->data_country['code']}/{$this->data_state['state_id']}"),
            'text' => $this->text('Cities of state %name', array('%name' => $this->data_state['name']))
        );

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Render and output the city edit page
     */
    protected function outputEditCity()
    {
        $this->output('settings/city/edit');
    }

}
