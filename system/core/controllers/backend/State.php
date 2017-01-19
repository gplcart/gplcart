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
     * The current country
     * @var array
     */
    protected $data_country = array();

    /**
     * The current state
     * @var array
     */
    protected $data_state = array();

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
        $this->setCountry($code);

        $this->actionState();

        $this->setTitleListState();
        $this->setBreadcrumbListState();

        $query = $this->getFilterQuery();

        $filters = array('name', 'code', 'status', 'state_id');
        $this->setFilter($filters, $query);

        $total = $this->getTotalState($code, $query);
        $limit = $this->setPager($total, $query);

        $this->setData('country', $this->data_country);
        $this->setData('states', $this->getListState($limit, $query, $code));

        $this->outputListState();
    }

    /**
     * Returns a country
     * @param string $code
     * @return array
     */
    protected function setCountry($code)
    {
        $country = $this->country->get($code);

        if (empty($country)) {
            $this->outputHttpStatus(404);
        }

        return $this->data_country = $country;
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
    }

    /**
     * Returns a total number of states for a given country
     * @param string $code
     * @param array $query
     * @return integer
     */
    protected function getTotalState($code, array $query)
    {
        $options = array('count' => true, 'country' => $code);
        return (int) $this->state->getList($options + $query);
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
        $options = array('limit' => $limit, 'country' => $country);
        return (array) $this->state->getList($options + $query);
    }

    /**
     * Sets titles on the states overview page
     */
    protected function setTitleListState()
    {
        $vars = array('%country' => $this->data_country['name']);
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
        $this->setState($state_id);
        $this->setCountry($country_code);

        $this->setTitleEditState();
        $this->setBreadcrumbEditState();

        $this->setData('state', $this->data_state);
        $this->setData('country', $this->data_country);
        $this->setData('zones', $this->getZonesState());
        $this->setData('can_delete', $this->canDeleteState());

        $this->submitState();

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
    protected function canDeleteState()
    {
        return isset($this->data_state['state_id'])//
                && $this->state->canDelete($this->data_state['state_id'])//
                && $this->access('state_delete');
    }

    /**
     * Returns a state
     * @param integer $state_id
     * @return array
     */
    protected function setState($state_id)
    {
        if (!is_numeric($state_id)) {
            return array();
        }

        $state = $this->state->get($state_id);

        if (empty($state)) {
            $this->outputHttpStatus(404);
        }

        return $this->data_state = $state;
    }

    /**
     * Saves a state
     * @return null
     */
    protected function submitState()
    {
        if ($this->isPosted('delete')) {
            $this->deleteState();
            return null;
        }

        if (!$this->isPosted('save') || !$this->validateState()) {
            return null;
        }

        if (isset($this->data_state['state_id'])) {
            $this->updateState();
        } else {
            $this->addState();
        }
    }

    /**
     * Validates a state
     * @return bool
     */
    protected function validateState()
    {
        $this->setSubmitted('state');

        $this->setSubmittedBool('status');
        $this->setSubmitted('update', $this->data_state);
        $this->setSubmitted('country', $this->data_country['code']);
        $this->validate('state');

        return !$this->hasErrors('state');
    }

    /**
     * Deletes a state
     */
    protected function deleteState()
    {
        $this->controlAccess('state_delete');

        $deleted = $this->state->delete($this->data_state['state_id']);

        $url = "admin/settings/states/{$this->data_country['code']}";

        if ($deleted) {
            $message = $this->text('Country state has been deleted');
            $this->redirect($url, $message, 'success');
        }

        $message = $this->text('Unable to delete this country state');
        $this->redirect($url, $message, 'danger');
    }

    /**
     * Updates a state with submitted values
     */
    protected function updateState()
    {
        $this->controlAccess('state_edit');

        $submitted = $this->getSubmitted();
        $this->state->update($this->data_state['state_id'], $submitted);

        $message = $this->text('Country state has been updated');
        $this->redirect("admin/settings/states/{$this->data_country['code']}", $message, 'success');
    }

    /**
     * Adds a new state using an array of submitted values
     */
    protected function addState()
    {
        $this->controlAccess('state_add');

        $submitted = $this->getSubmitted();
        $this->state->add($submitted);

        $message = $this->text('Country state has been added');
        $this->redirect("admin/settings/states/{$this->data_country['code']}", $message, 'success');
    }

    /**
     * Sets titles on the state edit page
     */
    protected function setTitleEditState()
    {
        $vars = array('%country' => $this->data_country['name']);
        $title = $this->text('Add state for %country', $vars);

        if (isset($this->data_state['state_id'])) {
            $vars = array('%name' => $this->data_state['name']);
            $title = $this->text('Edit state %name', $vars);
        }

        $this->setTitle($title);
    }

    /**
     * Set breadcrumbs on the state edit page
     */
    protected function setBreadcrumbEditState()
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
            'text' => $this->text('States of %country', array(
                '%country' => $this->data_country['code']
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
