<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\backend;

use gplcart\core\models\Country as CountryModel;
use gplcart\core\models\CountryState as CountryStateModel;
use gplcart\core\models\Zone as ZoneModel;

/**
 * Handles incoming requests and outputs data related to country states
 */
class CountryState extends Controller
{

    /**
     * Zone model instance
     * @var \gplcart\core\models\Zone $zone
     */
    protected $zone;

    /**
     * Country state model instance
     * @var \gplcart\core\models\CountryState $state
     */
    protected $state;

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
     * @param CountryStateModel $state
     * @param ZoneModel $zone
     */
    public function __construct(CountryModel $country, CountryStateModel $state, ZoneModel $zone)
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
    public function listCountryState($code)
    {
        $this->setCountry($code);
        $this->actionListCountryState();
        $this->setTitleListCountryState();
        $this->setBreadcrumbListCountryState();
        $this->setFilterListCountryState();
        $this->setPagerListCountryState();

        $this->setData('country', $this->data_country);
        $this->setData('states', $this->getListCountryState());

        $this->outputListCountryState();
    }

    /**
     * Set filter on the country state overview page
     */
    protected function setFilterListCountryState()
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
    protected function setCountryState($state_id)
    {
        $this->data_state = array();

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
    protected function actionListCountryState()
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
    protected function setPagerListCountryState()
    {
        $conditions = $this->query_filter;
        $conditions['count'] = true;
        $conditions['country'] = $this->data_country['code'];

        $pager = array(
            'query' => $this->query_filter,
            'total' => (int) $this->state->getList($conditions)
        );

        return $this->data_limit = $this->setPager($pager);
    }

    /**
     * Returns an array of country states for the country and filter conditions
     * @return array
     */
    protected function getListCountryState()
    {
        $conditions = $this->query_filter;
        $conditions['limit'] = $this->data_limit;
        $conditions['country'] = $this->data_country['code'];

        return (array) $this->state->getList($conditions);
    }

    /**
     * Sets titles on the country state overview page
     */
    protected function setTitleListCountryState()
    {
        $text = $this->text('Country states of %name', array('%name' => $this->data_country['name']));
        $this->setTitle($text);
    }

    /**
     * Sets breadcrumbs on the country state overview page
     */
    protected function setBreadcrumbListCountryState()
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
     * Render and output the country state overview page
     */
    protected function outputListCountryState()
    {
        $this->output('settings/state/list');
    }

    /**
     * Displays the edit country state page
     * @param string $country_code
     * @param integer|null $state_id
     */
    public function editCountryState($country_code, $state_id = null)
    {
        $this->setCountryState($state_id);
        $this->setCountry($country_code);
        $this->setTitleEditCountryState();
        $this->setBreadcrumbEditCountryState();

        $this->setData('state', $this->data_state);
        $this->setData('country', $this->data_country);
        $this->setData('zones', $this->getZonesCountryState());
        $this->setData('can_delete', $this->canDeleteCountryState());

        $this->submitEditCountryState();
        $this->outputEditCountryState();
    }

    /**
     * Returns an array of active zones
     * @return array
     */
    protected function getZonesCountryState()
    {
        return (array) $this->zone->getList(array('status' => 1));
    }

    /**
     * Whether the country state can be deleted
     * @return boolean
     */
    protected function canDeleteCountryState()
    {
        return isset($this->data_state['state_id'])
            && $this->state->canDelete($this->data_state['state_id'])
            && $this->access('state_delete');
    }

    /**
     * Handles a submitted country state data
     */
    protected function submitEditCountryState()
    {
        if ($this->isPosted('delete')) {
            $this->deleteCountryState();
        } else if ($this->isPosted('save') && $this->validateEditCountryState()) {
            if (isset($this->data_state['state_id'])) {
                $this->updateCountryState();
            } else {
                $this->addCountryState();
            }
        }
    }

    /**
     * Validates a submitted country state data
     * @return boolean
     */
    protected function validateEditCountryState()
    {
        $this->setSubmitted('state');
        $this->setSubmittedBool('status');
        $this->setSubmitted('update', $this->data_state);
        $this->setSubmitted('country', $this->data_country['code']);

        $this->validateComponent('country_state');

        return !$this->hasErrors();
    }

    /**
     * Deletes a country state
     */
    protected function deleteCountryState()
    {
        $this->controlAccess('state_delete');

        if ($this->state->delete($this->data_state['state_id'])) {
            $url = "admin/settings/states/{$this->data_country['code']}";
            $this->redirect($url, $this->text('Country state has been deleted'), 'success');
        }

        $this->redirect('', $this->text('Country state has not been deleted'), 'warning');
    }

    /**
     * Updates a country state
     */
    protected function updateCountryState()
    {
        $this->controlAccess('state_edit');

        if ($this->state->update($this->data_state['state_id'], $this->getSubmitted())) {
            $url = "admin/settings/states/{$this->data_country['code']}";
            $this->redirect($url, $this->text('Country state has been updated'), 'success');
        }

        $this->redirect('', $this->text('Country state has not been updated'), 'warning');
    }

    /**
     * Adds a new country state
     */
    protected function addCountryState()
    {
        $this->controlAccess('state_add');

        if ($this->state->add($this->getSubmitted())) {
            $url = "admin/settings/states/{$this->data_country['code']}";
            $this->redirect($url, $this->text('Country state has been added'), 'success');
        }

        $this->redirect('', $this->text('Country state has not been added'), 'warning');
    }

    /**
     * Sets titles on the edit country state page
     */
    protected function setTitleEditCountryState()
    {
        if (isset($this->data_state['state_id'])) {
            $title = $this->text('Edit %name', array('%name' => $this->data_state['name']));
        } else {
            $title = $this->text('Add country state for %name', array('%name' => $this->data_country['name']));
        }

        $this->setTitle($title);
    }

    /**
     * Set breadcrumbs on the edit country state page
     */
    protected function setBreadcrumbEditCountryState()
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
            'text' => $this->text('Country states of %name', array('%name' => $this->data_country['code']))
        );

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Render and output the edit country state page
     */
    protected function outputEditCountryState()
    {
        $this->output('settings/state/edit');
    }

}
