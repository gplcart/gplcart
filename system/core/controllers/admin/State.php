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
    public function states($code)
    {
        $country = $this->getCountry($code);

        $action = $this->request->post('action');
        $value = $this->request->post('value');
        $selected = $this->request->post('selected', array());

        if (!empty($action)) {
            $this->action($selected, $action, $value);
        }

        $query = $this->getFilterQuery();
        $total = $this->setPager($this->getTotalStates($code, $query), $query);

        $this->data['country'] = $country;
        $this->data['states'] = $this->getStates($total, $query, $code);

        $this->setFilter(array('name', 'code', 'status', 'state_id'), $query);

        $this->setTitleStates($country);
        $this->setBreadcrumbStates();
        $this->outputStates();
    }

    /**
     * Displays the state edit page
     * @param string $country_code
     * @param integer|null $state_id
     */
    public function edit($country_code, $state_id = null)
    {
        $country = $this->getCountry($country_code);
        $state = $this->get($state_id);

        $this->data['state'] = $state;
        $this->data['country'] = $country;

        if ($this->request->post('save')) {
            $this->submit($country, $state);
        }

        if ($this->request->post('delete')) {
            $this->delete($country, $state);
        }

        $this->setTitleEdit($country, $state);
        $this->setBreadcrumbEdit($country);
        $this->outputEdit();
    }

    /**
     * Returns a total number of states for a given country
     * @param string $code
     * @param array $query
     * @return integer
     */
    protected function getTotalStates($code, array $query)
    {
        return $this->state->getList(array('count' => true, 'country' => $code) + $query);
    }

    /**
     * Returns an array of states for a given country
     * @param array $limit
     * @param array $query
     * @param string $country
     * @return array
     */
    protected function getStates(array $limit, array $query, $country)
    {
        return $this->state->getList(array('country' => $country, 'limit' => $limit) + $query);
    }

    /**
     * Renders the state overview page
     */
    protected function outputStates()
    {
        $this->output('settings/state/list');
    }

    /**
     * Sets titles on the states overview page
     * @param array $country
     */
    protected function setTitleStates(array $country)
    {
        $this->setTitle($this->text('States of %country', array('%country' => $country['name'])));
    }

    /**
     * Sets breadcrumbs on the states overview page
     */
    protected function setBreadcrumbStates()
    {
        $this->setBreadcrumb(array('url' => $this->url('admin'), 'text' => $this->text('Dashboard')));
        $this->setBreadcrumb(array('url' => $this->url('admin/settings/country'), 'text' => $this->text('Countries')));
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
    protected function outputEdit()
    {
        $this->output('settings/state/edit');
    }

    /**
     * Set breadcrumbs on the state edit page
     * @param array $country
     */
    protected function setBreadcrumbEdit(array $country)
    {
        $this->setBreadcrumb(array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')));

        $this->setBreadcrumb(array(
            'url' => $this->url('admin/settings/country'),
            'text' => $this->text('Countries')));

        $this->setBreadcrumb(array(
            'url' => $this->url("admin/settings/states/{$country['code']}"),
            'text' => $this->text('States of %country', array('%country' => $country['code']))));
    }

    /**
     * Sets titles on the state edit page
     * @param array $country
     * @param array $state
     */
    protected function setTitleEdit(array $country, array $state)
    {
        if (isset($state['state_id'])) {
            $title = $this->text('Edit state %name', array('%name' => $state['name']));
        } else {
            $title = $this->text('Add state for %country', array('%country' => $country['name']));
        }

        $this->setTitle($title);
    }

    /**
     * Deletes a state
     * @param array $country
     * @param array $state
     * @return null
     */
    protected function delete(array $country, array $state)
    {
        if (empty($state['state_id'])) {
            return;
        }

        $this->controlAccess('state_delete');
        $this->state->delete($state['state_id']);
        $this->redirect("admin/settings/states/{$country['code']}", $this->text('Country state has been deleted'), 'success');
    }

    /**
     * Applies an action to the selected country states
     * @param array $selected
     * @param string $action
     * @param string $value
     * @return boolean
     */
    protected function action(array $selected, $action, $value)
    {
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
            $this->session->setMessage($this->text('Updated %num country states', array('%num' => $updated)), 'success');
            return true;
        }

        if ($deleted > 0) {
            $this->session->setMessage($this->text('Deleted %num country states', array('%num' => $deleted)), 'success');
            return true;
        }

        return false;
    }

    /**
     * Saves a state
     * @param array $country
     * @param array $state
     * @return null
     */
    protected function submit(array $country, array $state)
    {
        $this->submitted = $this->request->post('state');

        $this->validate($country, $state);

        $errors = $this->formErrors();

        if (!empty($errors)) {
            $this->data['state'] = $this->submitted;
            return;
        }

        if (isset($state['state_id'])) {
            $this->controlAccess('state_edit');
            $this->state->update($state['state_id'], $this->submitted);
            $this->redirect("admin/settings/states/{$country['code']}", $this->text('Country state has been updated'), 'success');
        }

        $this->controlAccess('state_add');
        $this->state->add($this->submitted);
        $this->redirect('', $this->text('Country state has been added'), 'success');
    }

    /**
     * Returns a state
     * @param integer $state_id
     * @return array
     */
    protected function get($state_id)
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
    protected function validate(array $country, array $state)
    {
        $this->validateName();
        $this->validateCode($country, $state);

        $this->submitted['status'] = !empty($this->submitted['status']);
        $this->submitted['country'] = $country['code'];
    }

    /**
     * Validates a state name
     * @return boolean
     */
    protected function validateName()
    {
        if (empty($this->submitted['name']) || mb_strlen($this->submitted['name']) > 255) {
            $this->data['form_errors']['name'] = $this->text('Content must be %min - %max characters long', array('%min' => 1, '%max' => 255));
            return false;
        }

        return true;
    }

    /**
     * Validates a state code
     * @param array $country
     * @param array $state
     * @return boolean
     */
    protected function validateCode(array $country, array $state)
    {
        $check = true;
        if (isset($state['code'])) {
            $check = ($state['code'] !== $this->submitted['code']);
        }

        if ($check && $this->state->getByCode($this->submitted['code'], $country['code'])) {
            $this->data['form_errors']['code'] = $this->text('This state code already exists for this country');
            return false;
        }

        return true;
    }
}
