<?php

/**
 * @package GPL Cart core
 * @version $Id$
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers\admin;

use core\Controller;
use core\models\Queue as Q;

class Queue extends Controller
{

    /**
     * Queue model instance
     * @var \core\models\Queue $queue
     */
    protected $queue;

    /**
     * Constructor
     * @param Q $queue
     */
    public function __construct(Q $queue)
    {
        parent::__construct();

        $this->queue = $queue;
    }

    /**
     * Displays the queues overview page
     */
    public function queues()
    {
        $value = $this->request->post('value');
        $action = $this->request->post('action');
        $selected = $this->request->post('selected', array());

        if ($action) {
            $this->action($selected, $action, $value);
        }

        $this->data['queues'] = $this->queue->getList();

        $this->setTitleQueues();
        $this->setBreadcrumbQueues();
        $this->outputQueues();
    }

    /**
     * Applies an action to the queues
     */
    protected function action($selected, $action, $value)
    {
        $updated = $deleted = 0;
        foreach ($selected as $queue_id) {
            if ($action == 'status' && $this->access('queue_edit')) {
                $updated += (int) $this->queue->update($queue_id, array('status' => (int) $value));
            }

            if ($action == 'delete' && $this->access('queue_delete')) {
                $deleted += (int) $this->queue->delete($queue_id);
            }
        }

        if ($updated) {
            $this->session->setMessage($this->text('Updated %num queues', array('%num' => $updated)), 'success');
            return true;
        }

        if ($deleted) {
            $this->session->setMessage($this->text('Deleted %num queues', array('%num' => $deleted)), 'success');
            return true;
        }

        return false;
    }

    /**
     * Sets titles on the queues overview page
     */
    protected function setTitleQueues()
    {
        $this->setTitle($this->text('Queues'));
    }

    /**
     * Sets breadcrumbs on the queues overview page
     */
    protected function setBreadcrumbQueues()
    {
        $this->setBreadcrumb(array('text' => $this->text('Dashboard'), 'url' => $this->url('admin')));
    }

    /**
     * Renders the queues overview page
     */
    protected function outputQueues()
    {
        $this->output('report/queue');
    }
}
