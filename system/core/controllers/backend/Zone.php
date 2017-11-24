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
     * Pager limit
     * @var array
     */
    protected $data_limit;

    /**
     * An array of zone data
     * @var array
     */
    protected $data_zone = array();

    /**
     * @param ZoneModel $zone
     */
    public function __construct(ZoneModel $zone)
    {
        parent::__construct();

        $this->zone = $zone;
    }

    /**
     * Displays the zone overview page
     */
    public function listZone()
    {
        $this->actionListZone();

        $this->setTitleListZone();
        $this->setBreadcrumbListZone();

        $this->setFilterListZone();
        $this->setPagerListZone();

        $this->setData('zones', $this->getListZone());
        $this->outputListZone();
    }

    /**
     * Sets filter on the zone overview page
     */
    protected function setFilterListZone()
    {
        $this->setFilter(array('title', 'status'));
    }

    /**
     * Sets pager
     * @return array
     */
    public function setPagerListZone()
    {
        $options = $this->query_filter;
        $options['count'] = true;

        $pager = array(
            'query' => $this->query_filter,
            'total' => (int) $this->zone->getList($options)
        );

        return $this->data_limit = $this->setPager($pager);
    }

    /**
     * Applies an action to the selected zones
     */
    protected function actionListZone()
    {
        list($selected, $action, $value) = $this->getPostedAction();

        $updated = $deleted = 0;
        foreach ($selected as $id) {

            if ($action === 'status' && $this->access('zone_edit')) {
                $updated += (int) $this->zone->update($id, array('status' => $value));
            }

            if ($action === 'delete' && $this->access('zone_delete')) {
                $deleted += (int) $this->zone->delete($id);
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
     * Returns an array of zones
     * @return array
     */
    protected function getListZone()
    {
        $options = $this->query_filter;
        $options['limit'] = $this->data_limit;

        return $this->zone->getList($options);
    }

    /**
     * Sets title on the zones overview page
     */
    protected function setTitleListZone()
    {
        $this->setTitle($this->text('Zones'));
    }

    /**
     * Sets breadcrumbs on the zone overview page
     */
    protected function setBreadcrumbListZone()
    {
        $breadcrumb = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')
        );

        $this->setBreadcrumb($breadcrumb);
    }

    /**
     * Render and output the zone overview page
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

        $this->submitEditZone();
        $this->outputEditZone();
    }

    /**
     * Whether the zone can be deleted
     * @return bool
     */
    protected function canDeleteZone()
    {
        return isset($this->data_zone['zone_id'])//
                && $this->zone->canDelete($this->data_zone['zone_id'])//
                && $this->access('zone_delete');
    }

    /**
     * Sets a zone data
     * @param integer $zone_id
     */
    protected function setZone($zone_id)
    {
        if (is_numeric($zone_id)) {
            $this->data_zone = $this->zone->get($zone_id);
            if (empty($this->data_zone)) {
                $this->outputHttpStatus(404);
            }
        }
    }

    /**
     * Handles a submitted zone data
     */
    protected function submitEditZone()
    {
        if ($this->isPosted('delete')) {
            $this->deleteZone();
        } else if ($this->isPosted('save') && $this->validateEditZone()) {
            if (isset($this->data_zone['zone_id'])) {
                $this->updateZone();
            } else {
                $this->addZone();
            }
        }
    }

    /**
     * Validates a submitted zone
     * @return bool
     */
    protected function validateEditZone()
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

        if ($this->zone->delete($this->data_zone['zone_id'])) {
            $this->redirect('admin/settings/zone', $this->text('Zone has been deleted'), 'success');
        }

        $this->redirect('', $this->text('Unable to delete'), 'danger');
    }

    /**
     * Updates a zone
     */
    protected function updateZone()
    {
        $this->controlAccess('zone_edit');

        $this->zone->update($this->data_zone['zone_id'], $this->getSubmitted());
        $this->redirect('admin/settings/zone', $this->text('Zone has been updated'), 'success');
    }

    /**
     * Adds a new zone
     */
    protected function addZone()
    {
        $this->controlAccess('zone_add');

        $this->zone->add($this->getSubmitted());
        $this->redirect('admin/settings/zone', $this->text('Zone has been added'), 'success');
    }

    /**
     * Sets titles on the edit zone page
     */
    protected function setTitleEditZone()
    {
        if (isset($this->data_zone['zone_id'])) {
            $title = $this->text('Edit %name', array('%name' => $this->data_zone['title']));
        } else {
            $title = $this->text('Add zone');
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
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')
        );

        $breadcrumbs[] = array(
            'text' => $this->text('Zones'),
            'url' => $this->url('admin/settings/zone')
        );

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Render and output the edit zone page
     */
    protected function outputEditZone()
    {
        $this->output('settings/zone/edit');
    }

}
