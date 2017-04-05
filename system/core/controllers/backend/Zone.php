<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\backend;

use gplcart\core\models\Zone as ZoneModel;
use gplcart\core\controllers\backend\Controller as BackendController;

/**
 * Handles incoming requests and outputs data related to geo zones
 */
class Zone extends BackendController
{

    /**
     * Zone model instance
     * @var \gplcart\core\models\Zone $zone
     */
    protected $zone;

    /**
     * The current zone
     * @var array
     */
    protected $data_zone = array();

    /**
     * Constructor
     * @param ZoneModel $zone
     */
    public function __construct(ZoneModel $zone)
    {
        parent::__construct();

        $this->zone = $zone;
    }

    /**
     * Returns the zone overview page
     */
    public function listZone()
    {
        $this->actionZone();

        $this->setTitleListZone();
        $this->setBreadcrumbListZone();

        $query = $this->getFilterQuery();

        $allowed = array('title', 'status');
        $this->setFilter($allowed, $query);

        $total = $this->getTotalZone($query);
        $limit = $this->setPager($total, $query);

        $this->setData('zones', $this->getListZone($limit, $query));
        $this->outputListZone();
    }

    /**
     * Applies an action to the selected zones
     * @return null
     */
    protected function actionZone()
    {
        $action = (string) $this->getPosted('action');

        if (empty($action)) {
            return null;
        }

        $value = (int) $this->getPosted('value');
        $selected = (array) $this->getPosted('selected', array());

        $updated = $deleted = 0;
        foreach ($selected as $id) {

            if ($action == 'status' && $this->access('zone_edit')) {
                $updated += (int) $this->zone->update($id, array('status' => $value));
            }

            if ($action == 'delete' && $this->access('zone_delete')) {
                $deleted += (int) $this->zone->delete($id);
            }
        }

        if ($updated > 0) {
            $vars = array('%num' => $updated);
            $message = $this->text('Updated %num zones', $vars);
            $this->setMessage($message, 'success', true);
        }

        if ($deleted > 0) {
            $vars = array('%num' => $deleted);
            $message = $this->text('Deleted %num zones', $vars);
            $this->setMessage($message, 'success', true);
        }
    }

    /**
     * Returns a number of total zones
     * @param array $query
     * @return integer
     */
    public function getTotalZone(array $query)
    {
        $query['count'] = true;
        return (int) $this->zone->getList($query);
    }

    /**
     * Returns an array of zones
     * @param array $limit
     * @param array $query
     * @return array
     */
    protected function getListZone(array $limit, array $query)
    {
        $query['limit'] = $limit;
        return $this->zone->getList($query);
    }

    /**
     * Sets titles on the zones overview page
     */
    protected function setTitleListZone()
    {
        $this->setTitle($this->text('Zones'));
    }

    /**
     * Sets breadcrumbs on the zones overview page
     */
    protected function setBreadcrumbListZone()
    {
        $breadcrumb = array(
            'text' => $this->text('Dashboard'),
            'url' => $this->url('admin')
        );

        $this->setBreadcrumb($breadcrumb);
    }

    /**
     * Renders the zone overview page
     */
    protected function outputListZone()
    {
        $this->output('settings/zone/list');
    }

    /**
     * Displays the zone edit page
     * @param null|integer $zone_id
     */
    public function editZone($zone_id = null)
    {
        $this->setZone($zone_id);

        $this->setTitleEditZone();
        $this->setBreadcrumbEditZone();

        $this->setData('zone', $this->data_zone);
        $this->setData('can_delete', $this->canDeleteZone());

        $this->submitZone();
        $this->outputEditZone();
    }

    /**
     * Wheter the current zone can be deleted
     * @return bool
     */
    protected function canDeleteZone()
    {
        return isset($this->data_zone['zone_id'])//
                && $this->zone->canDelete($this->data_zone['zone_id'])//
                && $this->access('zone_delete');
    }

    /**
     * Returns a zone
     * @param integer $zone_id
     * @return array
     */
    protected function setZone($zone_id)
    {
        if (!is_numeric($zone_id)) {
            return array();
        }

        $zone = $this->zone->get($zone_id);

        if (empty($zone)) {
            $this->outputHttpStatus(404);
        }

        return $this->data_zone = $zone;
    }

    /**
     * Saves a submitted zone
     * @return null
     */
    protected function submitZone()
    {
        if ($this->isPosted('delete')) {
            $this->deleteZone();
            return null;
        }

        if (!$this->isPosted('save') || !$this->validateZone()) {
            return null;
        }

        if (isset($this->data_zone['zone_id'])) {
            $this->updateZone();
        } else {
            $this->addZone();
        }
    }

    /**
     * Validates a zone
     * @return bool
     */
    protected function validateZone()
    {
        $this->setSubmitted('zone');
        $this->setSubmittedBool('status');
        $this->setSubmitted('update', $this->data_zone);

        $this->validateComponent('zone');

        return !$this->hasErrors();
    }

    /**
     * Deletes a zone
     */
    protected function deleteZone()
    {
        $this->controlAccess('zone_delete');

        $deleted = $this->zone->delete($this->data_zone['zone_id']);

        if ($deleted) {
            $message = $this->text('Zone has been deleted');
            $this->redirect('admin/settings/zone', $message, 'success');
        }

        $message = $this->text('Unable to delete this zone');
        $this->redirect('', $message, 'danger');
    }

    /**
     * Updates a zone with submitted values
     */
    protected function updateZone()
    {
        $this->controlAccess('zone_edit');

        $values = $this->getSubmitted();
        $this->zone->update($this->data_zone['zone_id'], $values);

        $message = $this->text('Zone has been updated');
        $this->redirect('admin/settings/zone', $message, 'success');
    }

    /**
     * Adds a new zone using an array of submitted values
     */
    protected function addZone()
    {
        $this->controlAccess('zone_add');
        $this->zone->add($this->getSubmitted());

        $message = $this->text('Zone has been added');
        $this->redirect('admin/settings/zone', $message, 'success');
    }

    /**
     * Sets titles on the edit zone page
     */
    protected function setTitleEditZone()
    {
        $title = $this->text('Add zone');

        if (isset($this->data_zone['zone_id'])) {
            $vars = array('%name' => $this->data_zone['title']);
            $title = $this->text('Edit zone %name', $vars);
        }

        $this->setTitle($title);
    }

    /**
     * Sets breadcrumbs on the edit zone page
     */
    protected function setBreadcrumbEditZone()
    {
        $breadcrumbs = array();

        $breadcrumbs[] = array(
            'text' => $this->text('Dashboard'),
            'url' => $this->url('admin')
        );

        $breadcrumbs[] = array(
            'text' => $this->text('Zones'),
            'url' => $this->url('admin/settings/zone')
        );

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Renders the edit zone page
     */
    protected function outputEditZone()
    {
        $this->output('settings/zone/edit');
    }

}
