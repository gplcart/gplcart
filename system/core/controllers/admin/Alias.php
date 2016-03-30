<?php

/**
 * @package GPL Cart core
 * @version $Id$
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace core\controllers\admin;

use core\Controller;
use core\models\Alias as A;

class Alias extends Controller
{

    /**
     * Url model instance
     * @var \core\models\Alias $alias
     */
    protected $alias;

    /**
     * Constructor
     * @param U $alias
     */
    public function __construct(A $alias)
    {
        parent::__construct();

        $this->alias = $alias;
    }

    /**
     * Displays the aliases overview page
     */
    public function aliases()
    {
        $query = $this->getFilterQuery();
        $limit = $this->setPager($this->alias->getList(array('count' => true) + $query), $query);

        $this->data['aliases'] = $this->getAliases($limit, $query);
        $this->data['id_keys'] = $this->alias->getIdKeys();

        $filters = array('id_value', 'id_key', 'alias');
        $this->setFilter($filters, $query);

        $action = $this->request->post('action');
        $selected = $this->request->post('selected', array());

        if ($action) {
            $this->action($selected, $action);
        }

        $this->setTitleAliases();
        $this->setBreadcrumbAliases();
        $this->outputAliases();
    }

    /**
     * Sets titles on the aliases overview page
     */
    protected function setTitleAliases()
    {
        $this->setTitle($this->text('Aliases'));
    }

    /**
     * Sets breadcrumbs on the aliases overview page
     */
    protected function setBreadcrumbAliases()
    {
        $this->setBreadcrumb(array('text' => $this->text('Dashboard'), 'url' => $this->url('admin')));
    }

    /**
     * Renders the aliases overview page
     */
    protected function outputAliases()
    {
        $this->output('content/alias/list');
    }

    /**
     * Returns an array of aliases
     */
    protected function getAliases($limit, $query)
    {
        return $this->alias->getList(array('limit' => $limit) + $query);
    }

    /**
     * Applies an action to the selected aliases
     * @param array $selected
     * @param string $action
     * @return boolean
     */
    protected function action($selected, $action)
    {

        $deleted = 0;
        foreach ($selected as $id) {

            $alias = $this->alias->get($id);

            if (!$alias) {
                continue;
            }

            $entityname = preg_replace('/_id$/', '', $alias['id_key']);

            if (!$this->access("{$entityname}_edit")) {
                continue;
            }

            if ($action == 'delete') {
                $deleted += (int) $this->alias->delete($id);
            }
        }

        if ($deleted) {
            $this->session->setMessage($this->text('Deleted %num aliases', array('%num' => $deleted)), 'success');
            return true;
        }

        return false;
    }

}
