<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers\admin;

use core\Controller;
use core\models\State as ModelsState;
use core\models\Country as ModelsCountry;

/**
 * Handles incoming requests and outputs data related to country states
 */
class State extends Controller
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
     * Constructor
     * @param ModelsCountry $country
     * @param ModelsState $state
     */
    public function __construct(ModelsCountry $country, ModelsState $state)
    {
        parent::__construct();

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
     * Displays the state edit page
     * @param string $country_code
     * @param integer|null $state_id
     */
    public function editState($country_code, $state_id = null)
    {
        $state = $this->getState($state_id);
        $country = $this->getCountry($country_code);

        $this->setData('state', $state);
        $this->setData('country', $country);

        $this->submitState($country, $state);

        $this->setTitleEditState($country, $state);
        $this->setBreadcrumbEditState($country);
        $this->outputEditState();
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
            'country' => $code);

        $options += $query;
        return $this->state->getList($options);
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
        return $this->state->getList($options);
    }

    /**
     * Renders the state overview page
     */
    protected function outputListState()
    {
        $this->output('settings/state/list');
    }

    /**
     * Sets titles on the states overview page
     * @param array $country
     */
    protected function setTitleListState(array $country)
    {
        $text = $this->text('States of %country', array(
            '%country' => $country['name']));

        $this->setTitle($text);
    }

    /**
     * Sets breadcrumbs on the states overview page
     */
    protected function setBreadcrumbListState()
    {
        $breadcrumbs[] = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard'));

        $breadcrumbs[] = array(
            'url' => $this->url('admin/settings/country'),
            'text' => $this->text('Countries'));

        $this->setBreadcrumbs($breadcrumbs);
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
     * Renders the state edit page
     */
    protected function outputEditState()
    {
        $this->output('settings/state/edit');
    }

    /**
     * Set breadcrumbs on the state edit page
     * @param array $country
     */
    protected function setBreadcrumbEditState(array $country)
    {
        $breadcrumbs[] = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard'));

        $breadcrumbs[] = array(
            'url' => $this->url('admin/settings/country'),
            'text' => $this->text('Countries'));

        $breadcrumbs[] = array(
            'url' => $this->url("admin/settings/states/{$country['code']}"),
            'text' => $this->text('States of %country', array(
                '%country' => $country['code'])));

        $this->setBreadcrumbs($breadcrumbs);
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
                '%name' => $state['name']));
        } else {
            $title = $this->text('Add state for %country', array(
                '%country' => $country['name']));
        }

        $this->setTitle($title);
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
     * Applies an action to the selected country states
     */
    protected function actionState()
    {
        $action = (string) $this->request->post('action');

        if (empty($action)) {
            return;
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
            $text = $this->text('Updated %num country states', array(
                '%num' => $updated));
            $this->setMessage($text, 'success', true);
        }

        if ($deleted > 0) {
            $text = $this->text('Deleted %num country states', array(
                '%num' => $deleted));
            $this->setMessage($text, 'success', true);
        }
    }

    /**
     * Saves a state
     * @param array $country
     * @param array $state
     * @return null
     */
    protected function submitState(array $country, array $state)
    {
        if ($this->isPosted('delete')) {
            $this->deleteState($country, $state);
        }

        if (!$this->isPosted('save')) {
            return;
        }

        $this->setSubmitted('state');

        $this->validateState($country, $state);

        if ($this->hasErrors('state')) {
            return;
        }

        if (isset($state['state_id'])) {
            return $this->updateState($country, $state);
        }

        $this->addState($country);
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
     * Validates a state
     * @param array $country
     * @param array $state
     */
    protected function validateState(array $country, array $state)
    {
        $this->setSubmittedBool('status');
        $this->setSubmitted('country', $country['code']);

        $this->addValidator('country', array(
            'required' => array()
        ));

        $this->addValidator('name', array(
            'length' => array('min' => 1, 'max' => 255)
        ));

        $this->addValidator('code', array(
            'length' => array('min' => 1, 'max' => 255),
            'state_code_unique' => array()
        ));

        $this->setValidators(array('country' => $country, 'state' => $state));
    }

}
