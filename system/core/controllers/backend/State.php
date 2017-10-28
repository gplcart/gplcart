<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\backend;

use gplcart\core\models\Zone as ZoneModel,
    gplcart\core\models\State as StateModel,
    gplcart\core\models\Country as CountryModel;
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
     * Pager limit
     * @var array
     */
    protected $data_limit;

    /**
     * An array of country data
     * @var array
     */
    protected $data_country = array();

    /**
     * An array of country state data
     * @var array
     */
    protected $data_state = array();

    /**
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
     * Displays the country state overview page
     * @param string $code
     */
    public function listState($code)
    {
        $this->setCountry($code);
        $this->actionListState();

        $this->setTitleListState();
        $this->setBreadcrumbListState();

        $this->setFilterListState();
        $this->setPagerListState();

        $this->setData('country', $this->data_country);
        $this->setData('states', $this->getListState());

        $this->outputListState();
    }

    /**
     * Set filter on the country state overview page
     */
    protected function setFilterListState()
    {
        $this->setFilter(array('name', 'code', 'status', 'state_id'));
    }

    /**
     * Sets a country data
     * @param string $code
     */
    protected function setCountry($code)
    {
        $this->data_country = $this->country->get($code);

        if (empty($this->data_country)) {
            $this->outputHttpStatus(404);
        }
    }

    /**
     * Sets a country state data
     * @param integer $state_id
     */
    protected function setState($state_id)
    {
        if (is_numeric($state_id)) {
            $this->data_state = $this->state->get($state_id);
            if (empty($this->data_state)) {
                $this->outputHttpStatus(404);
            }
        }
    }

    /**
     * Applies an action to the selected country states
     */
    protected function actionListState()
    {
        list($selected, $action, $value) = $this->getPostedAction();

        $deleted = $updated = 0;
        foreach ($selected as $id) {

            if ($action === 'status' && $this->access('state_edit')) {
                $updated += (int) $this->state->update($id, array('status' => $value));
            }

            if ($action === 'delete' && $this->access('state_delete')) {
                $deleted += (int) $this->state->delete($id);
            }
        }

        if ($updated > 0) {
            $text = $this->text('Updated %num item(s)', array('%num' => $updated));
            $this->setMessage($text, 'success');
        }

        if ($deleted > 0) {
            $text = $this->text('Deleted %num item(s)', array('%num' => $deleted));
            $this->setMessage($text, 'success');
        }
    }

    /**
     * Set pager
     * @return array
     */
    protected function setPagerListState()
    {
        $options = $this->query_filter;
        $options['count'] = true;
        $options['country'] = $this->data_country['code'];

        $pager = array(
            'query' => $this->query_filter,
            'total' => (int) $this->state->getList($options)
        );

        return $this->data_limit = $this->setPager($pager);
    }

    /**
     * Returns an array of country states for the country and filter conditions
     * @return array
     */
    protected function getListState()
    {
        $options = $this->query_filter;
        $options['limit'] = $this->data_limit;
        $options['country'] = $this->data_country['code'];

        return (array) $this->state->getList($options);
    }

    /**
     * Sets titles on the country state overview page
     */
    protected function setTitleListState()
    {
        $vars = array('%name' => $this->data_country['name']);
        $this->setTitle($this->text('States of %name', $vars));
    }

    /**
     * Sets breadcrumbs on the country state overview page
     */
    protected function setBreadcrumbListState()
    {
        $this->setBreadcrumbHome();

        $breadcrumb = array(
            'url' => $this->url('admin/settings/country'),
            'text' => $this->text('Countries')
        );

        $this->setBreadcrumb($breadcrumb);
    }

    /**
     * Render and output the country state overview page
     */
    protected function outputListState()
    {
        $this->output('settings/state/list');
    }

    /**
     * Displays the edit country state page
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

        $this->submitEditState();
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
     * Whether the country state can be deleted
     * @return boolean
     */
    protected function canDeleteState()
    {
        return isset($this->data_state['state_id'])//
                && $this->state->canDelete($this->data_state['state_id'])//
                && $this->access('state_delete');
    }

    /**
     * Handles a submitted country state data
     */
    protected function submitEditState()
    {
        if ($this->isPosted('delete')) {
            $this->deleteState();
        } else if ($this->isPosted('save') && $this->validateEditState()) {
            if (isset($this->data_state['state_id'])) {
                $this->updateState();
            } else {
                $this->addState();
            }
        }
    }

    /**
     * Validates a submitted country state data
     * @return boolean
     */
    protected function validateEditState()
    {
        $this->setSubmitted('state');
        $this->setSubmittedBool('status');
        $this->setSubmitted('update', $this->data_state);
        $this->setSubmitted('country', $this->data_country['code']);

        $this->validateComponent('state');

        return !$this->hasErrors();
    }

    /**
     * Deletes a country state
     */
    protected function deleteState()
    {
        $this->controlAccess('state_delete');
        $url = "admin/settings/states/{$this->data_country['code']}";

        if ($this->state->delete($this->data_state['state_id'])) {
            $this->redirect($url, $this->text('Country state has been deleted'), 'success');
        }

        $this->redirect($url, $this->text('Unable to delete'), 'danger');
    }

    /**
     * Updates a country state
     */
    protected function updateState()
    {
        $this->controlAccess('state_edit');
        $this->state->update($this->data_state['state_id'], $this->getSubmitted());

        $url = "admin/settings/states/{$this->data_country['code']}";
        $this->redirect($url, $this->text('Country state has been updated'), 'success');
    }

    /**
     * Adds a new country state
     */
    protected function addState()
    {
        $this->controlAccess('state_add');
        $this->state->add($this->getSubmitted());

        $url = "admin/settings/states/{$this->data_country['code']}";
        $this->redirect($url, $this->text('Country state has been added'), 'success');
    }

    /**
     * Sets titles on the edit country state page
     */
    protected function setTitleEditState()
    {
        if (isset($this->data_state['state_id'])) {
            $vars = array('%name' => $this->data_state['name']);
            $title = $this->text('Edit %name', $vars);
        } else {
            $vars = array('%name' => $this->data_country['name']);
            $title = $this->text('Add country state for %name', $vars);
        }

        $this->setTitle($title);
    }

    /**
     * Set breadcrumbs on the edit country state page
     */
    protected function setBreadcrumbEditState()
    {
        $this->setBreadcrumbHome();

        $breadcrumbs = array();

        $breadcrumbs[] = array(
            'url' => $this->url('admin/settings/country'),
            'text' => $this->text('Countries')
        );

        $breadcrumbs[] = array(
            'url' => $this->url("admin/settings/states/{$this->data_country['code']}"),
            'text' => $this->text('States of %name', array('%name' => $this->data_country['code']))
        );

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Render and output the edit country state page
     */
    protected function outputEditState()
    {
        $this->output('settings/state/edit');
    }

}
