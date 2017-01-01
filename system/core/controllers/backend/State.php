<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\backend;

use gplcart\core\models\Zone as ZoneModel;
use gplcart\core\models\State as StateModel;
use gplcart\core\models\Country as CountryModel;
use gplcart\core\controllers\backend\Controller as BackendController;

/**
 * Handles incoming requests and outputs data related to country states
 */
class State extends BackendController
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
     * Zone model instance
     * @var \gplcart\core\models\Zone $zone
     */
    protected $zone;

    /**
     * Constructor
     * @param CountryModel $country
     * @param StateModel $state
     * @param ZoneModel $zone
     */
    public function __construct(CountryModel $country, StateModel $state,
            ZoneModel $zone)
    {
        parent::__construct();

        $this->zone = $zone;
        $this->state = $state;
        $this->country = $country;
    }

    /**
     * Displays the states overview page
     * @param string $code
     */
    public function listState($code)
    {
        $country = $this->getCountry($code);

        $this->actionState();

        $query = $this->getFilterQuery();
        $total = $this->getTotalState($code, $query);
        $limit = $this->setPager($total, $query);
        $states = $this->getListState($limit, $query, $code);

        $this->setData('states', $states);
        $this->setData('country', $country);

        $filters = array('name', 'code', 'status', 'state_id');

        $this->setFilter($filters, $query);

        $this->setTitleListState($country);
        $this->setBreadcrumbListState();
        $this->outputListState();
    }

    /**
     * Returns a country
     * @param string $code
     * @return array
     */
    protected function getCountry($code)
    {
        $country = $this->country->get($code);

        if (empty($country)) {
            $this->outputError(404);
        }

        return $country;
    }

    /**
     * Applies an action to the selected country states
     */
    protected function actionState()
    {
        $action = (string) $this->request->post('action');

        if (empty($action)) {
            return null;
        }

        $value = (int) $this->request->post('value');
        $selected = (array) $this->request->post('selected', array());

        $deleted = $updated = 0;
        foreach ($selected as $id) {

            if ($action == 'status' && $this->access('state_edit')) {
                $updated += (int) $this->state->update($id, array('status' => $value));
            }

            if ($action == 'delete' && $this->access('state_delete')) {
                $deleted += (int) $this->state->delete($id);
            }
        }

        if ($updated > 0) {
            $vars = array('%num' => $updated);
            $text = $this->text('Updated %num country states', $vars);
            $this->setMessage($text, 'success', true);
        }

        if ($deleted > 0) {
            $vars = array('%num' => $deleted);
            $text = $this->text('Deleted %num country states', $vars);
            $this->setMessage($text, 'success', true);
        }

        return null;
    }

    /**
     * Returns a total number of states for a given country
     * @param string $code
     * @param array $query
     * @return integer
     */
    protected function getTotalState($code, array $query)
    {
        $options = array(
            'count' => true,
            'country' => $code
        );

        $options += $query;
        return (int) $this->state->getList($options);
    }

    /**
     * Returns an array of states for a given country
     * @param array $limit
     * @param array $query
     * @param string $country
     * @return array
     */
    protected function getListState(array $limit, array $query, $country)
    {
        $options = array(
            'limit' => $limit,
            'country' => $country
        );

        $options += $query;
        return (array) $this->state->getList($options);
    }

    /**
     * Sets titles on the states overview page
     * @param array $country
     */
    protected function setTitleListState(array $country)
    {
        $vars = array('%country' => $country['name']);
        $text = $this->text('States of %country', $vars);
        $this->setTitle($text);
    }

    /**
     * Sets breadcrumbs on the states overview page
     */
    protected function setBreadcrumbListState()
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
     * Renders the state overview page
     */
    protected function outputListState()
    {
        $this->output('settings/state/list');
    }

    /**
     * Displays the state edit page
     * @param string $country_code
     * @param integer|null $state_id
     */
    public function editState($country_code, $state_id = null)
    {
        $state = $this->getState($state_id);
        $country = $this->getCountry($country_code);

        $zones = $this->getZonesState();
        $can_delete = $this->canDeleteState($state);

        $this->setData('state', $state);
        $this->setData('zones', $zones);
        $this->setData('country', $country);
        $this->setData('can_delete', $can_delete);

        $this->submitState($country, $state);

        $this->setTitleEditState($country, $state);
        $this->setBreadcrumbEditState($country);
        $this->outputEditState();
    }

    /**
     * Returns an array of active zones
     * @return array
     */
    protected function getZonesState()
    {
        return (array) $this->zone->getList(array('status' => 1));
    }

    /**
     * Whether the state can be deleted
     * @return boolean
     */
    protected function canDeleteState(array $state)
    {
        return (isset($state['state_id'])//
                && $this->state->canDelete($state['state_id'])//
                && $this->access('state_delete'));
    }

    /**
     * Returns a state
     * @param integer $state_id
     * @return array
     */
    protected function getState($state_id)
    {
        if (!is_numeric($state_id)) {
            return array();
        }

        $state = $this->state->get($state_id);

        if (empty($state)) {
            $this->outputError(404);
        }

        return $state;
    }

    /**
     * Saves a state
     * @param array $country
     * @param array $state
     * @return null|void
     */
    protected function submitState(array $country, array $state)
    {
        if ($this->isPosted('delete')) {
            $this->deleteState($country, $state);
        }

        if (!$this->isPosted('save')) {
            return null;
        }

        $this->setSubmitted('state');
        $this->validateState($country, $state);

        if ($this->hasErrors('state')) {
            return null;
        }

        if (isset($state['state_id'])) {
            return $this->updateState($country, $state);
        }

        return $this->addState($country);
    }

    /**
     * Deletes a state
     * @param array $country
     * @param array $state
     */
    protected function deleteState(array $country, array $state)
    {
        $this->controlAccess('state_delete');

        $deleted = $this->state->delete($state['state_id']);

        $url = "admin/settings/states/{$country['code']}";

        if ($deleted) {
            $message = $this->text('Country state has been deleted');
            $this->redirect($url, $message, 'success');
        }

        $message = $this->text('Unable to delete this country state');
        $this->redirect($url, $message, 'danger');
    }

    /**
     * Validates a state
     * @param array $country
     * @param array $state
     */
    protected function validateState(array $country, array $state)
    {
        $this->setSubmittedBool('status');
        $this->setSubmitted('update', $state);
        $this->setSubmitted('country', $country['code']);
        $this->validate('state');
    }

    /**
     * Updates a state with submitted values
     * @param array $country
     * @param array $state
     */
    protected function updateState(array $country, array $state)
    {
        $this->controlAccess('state_edit');

        $submitted = $this->getSubmitted();
        $this->state->update($state['state_id'], $submitted);

        $message = $this->text('Country state has been updated');
        $this->redirect("admin/settings/states/{$country['code']}", $message, 'success');
    }

    /**
     * Adds a new state using an array of submitted values
     * @param array $country
     */
    protected function addState(array $country)
    {
        $this->controlAccess('state_add');

        $submitted = $this->getSubmitted();
        $this->state->add($submitted);

        $message = $this->text('Country state has been added');
        $this->redirect("admin/settings/states/{$country['code']}", $message, 'success');
    }

    /**
     * Sets titles on the state edit page
     * @param array $country
     * @param array $state
     */
    protected function setTitleEditState(array $country, array $state)
    {
        if (isset($state['state_id'])) {
            $title = $this->text('Edit state %name', array(
                '%name' => $state['name']
            ));
        } else {
            $title = $this->text('Add state for %country', array(
                '%country' => $country['name']
            ));
        }

        $this->setTitle($title);
    }

    /**
     * Set breadcrumbs on the state edit page
     * @param array $country
     */
    protected function setBreadcrumbEditState(array $country)
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
                '%country' => $country['code']
            ))
        );

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Renders the state edit page
     */
    protected function outputEditState()
    {
        $this->output('settings/state/edit');
    }

}
