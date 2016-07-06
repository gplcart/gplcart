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
use core\models\Reset as R;

class Reset extends Controller
{

    /**
     * Reset model instance
     * @var \core\models\Reset $reset
     */
    protected $reset;

    /**
     * Constructor
     * @param R $reset
     */
    public function __construct(R $reset)
    {
        parent::__construct();

        $this->reset = $reset;
    }

    /**
     * Displays the reset form page
     */
    public function reset()
    {
        if (!$this->config->get('system_reset', 1)) {
            $this->outputError(403);
        }

        // Only superuser (i.e UID 1) is allowed here
        if (!$this->isSuperadmin()) {
            $this->outputError(403);
        }

        if ($this->request->post('reset')) {
            $this->submit();
        }

        $this->setTitle('Full system reset');
        $this->setBreadcrumb(array('text' => $this->text('Dashboard'), 'url' => $this->url('admin')));
        $this->output('tool/reset');
    }

    /**
     * Reset the system
     */
    protected function submit()
    {
        $this->submitted = $this->request->post();
        $this->validate();

        if (!$this->formErrors()) {
            $this->reset->reset();
            $this->redirect('', $this->text('The system has been reset to its original state'), 'success');
        }
    }

    /**
     * Validates reset form
     */
    protected function validate()
    {
        if (empty($this->submitted['confirmation']) || $this->submitted['confirmation'] !== 'DELETE ALL') {
            $this->data['form_errors']['confirmation'] = $this->text('Wrong confirmation code');
        }
    }
}
