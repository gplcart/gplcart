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

        $query = $this->getFilterQuery();
        $total = $this->getTotalZone($query);
        $limit = $this->setPager($total, $query);
        $zones = $this->getListZone($limit, $query);

        $allowed = array('title', 'status');
        $this->setFilter($allowed, $query);

        $this->setData('zones', $zones);

        $this->setTitleListZone();
        $this->setBreadcrumbListZone();
        $this->outputListZone();
    }

    /**
     * Applies an action to the selected zones
     * @return null
     */
    protected function actionZone()
    {
        $action = (string) $this->request->post('action');

        if (empty($action)) {
            return null;
        }

        $value = (int) $this->request->post('value');
        $selected = (array) $this->request->post('selected', array());

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

        return null;
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
        $zone = $this->getZone($zone_id);

        $can_delete = (!empty($zone)//
                && $this->zone->canDelete($zone_id)//
                && $this->access('zone_delete'));

        $this->setData('zone', $zone);
        $this->setData('can_delete', $can_delete);

        $this->submitZone($zone);

        $this->setTitleEditZone($zone);
        $this->setBreadcrumbEditZone();
        $this->outputEditZone();
    }

    /**
     * Returns a zone
     * @param integer $zone_id
     * @return array
     */
    protected function getZone($zone_id)
    {
        if (!is_numeric($zone_id)) {
            return array();
        }

        $zone = $this->zone->get($zone_id);

        if (empty($zone)) {
            $this->outputHttpStatus(404);
        }

        return $zone;
    }

    /**
     * Saves a submitted zone
     * @param array $zone
     * @return null|void
     */
    protected function submitZone(array $zone)
    {
        if ($this->isPosted('delete')) {
            return $this->deleteZone($zone);
        }

        if (!$this->isPosted('save')) {
            return null;
        }

        $this->setSubmitted('zone');
        $this->validateZone($zone);

        if ($this->hasErrors('zone')) {
            return null;
        }

        if (isset($zone['zone_id'])) {
            return $this->updateZone($zone);
        }

        return $this->addZone();
    }

    /**
     * Deletes a zone
     * @param array $zone
     */
    protected function deleteZone(array $zone)
    {
        $this->controlAccess('zone_delete');

        $deleted = $this->zone->delete($zone['zone_id']);

        if ($deleted) {
            $message = $this->text('Zone has been deleted');
            $this->redirect('admin/settings/zone', $message, 'success');
        }

        $message = $this->text('Unable to delete this zone');
        $this->redirect('', $message, 'danger');
    }

    /**
     * Validates a zone
     * @param array $zone
     */
    protected function validateZone(array $zone)
    {
        $this->setSubmittedBool('status');
        $this->setSubmitted('update', $zone);
        $this->validate('zone');
    }

    /**
     * Updates a zone with submitted values
     * @param array $zone
     */
    protected function updateZone(array $zone)
    {
        $this->controlAccess('zone_edit');

        $values = $this->getSubmitted();
        $this->zone->update($zone['zone_id'], $values);

        $message = $this->text('Zone has been updated');
        $this->redirect('admin/settings/zone', $message, 'success');
    }

    /**
     * Adds a new zone using an array of submitted values
     */
    protected function addZone()
    {
        $this->controlAccess('zone_add');

        $values = $this->getSubmitted();
        $this->zone->add($values);

        $message = $this->text('Zone has been added');
        $this->redirect('admin/settings/zone', $message, 'success');
    }

    /**
     * Sets titles on the edit zone page
     * @param array $zone
     */
    protected function setTitleEditZone(array $zone)
    {
        $title = $this->text('Add zone');

        if (isset($zone['zone_id'])) {
            $title = $this->text('Edit zone %name', array(
                '%name' => $zone['title']
            ));
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
