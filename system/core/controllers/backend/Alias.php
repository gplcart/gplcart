<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\backend;

use gplcart\core\models\Alias as AliasModel;
use gplcart\core\controllers\backend\Controller as BackendController;

/**
 * Handles incoming requests and outputs data related to the URL aliases
 */
class Alias extends BackendController
{

    /**
     * Url model instance
     * @var \gplcart\core\models\Alias $alias
     */
    protected $alias;

    /**
     * @param AliasModel $alias
     */
    public function __construct(AliasModel $alias)
    {
        parent::__construct();

        $this->alias = $alias;
    }

    /**
     * Displays the alias overview page
     */
    public function listAlias()
    {
        $this->actionListAlias();

        $this->setTitleListAlias();
        $this->setBreadcrumbListAlias();

        $this->setFilterListAlias();
        $this->setTotalListAlias();
        $this->setPagerLimit();

        $this->setData('id_keys', $this->alias->getIdKeys());
        $this->setData('aliases', $this->getListAlias());
        $this->outputListAlias();
    }

    /**
     * Sets the current filter parameters
     */
    protected function setFilterListAlias()
    {
        $allowed = array('id_value', 'id_key', 'alias', 'alias_id');
        $this->setFilter($allowed);
    }

    /**
     * Applies an action to the selected aliases
     */
    protected function actionListAlias()
    {
        $action = $this->getPosted('action', '', true, 'string');
        $selected = $this->getPosted('selected', array(), true, 'array');

        if (empty($action)) {
            return null;
        }

        $deleted = 0;
        foreach ($selected as $id) {
            if ($action === 'delete' && $this->access('alias_delete')) {
                $deleted += (int) $this->alias->delete($id);
            }
        }

        if ($deleted > 0) {
            $message = $this->text('Deleted %num items', array('%num' => $deleted));
            $this->setMessage($message, 'success', true);
        }
    }

    /**
     * Sets a total number of aliases found for the current filter conditions
     */
    protected function setTotalListAlias()
    {
        $query = $this->query_filter;
        $query['count'] = true;
        $this->total = (int) $this->alias->getList($query);
    }

    /**
     * Returns an array of aliases
     * @return array
     */
    protected function getListAlias()
    {
        $query = $this->query_filter;
        $query['limit'] = $this->limit;
        $aliases = (array) $this->alias->getList($query);

        foreach ($aliases as &$alias) {
            $entity = preg_replace('/_id$/', '', $alias['id_key']);
            $alias['entity'] = $this->text(ucfirst($entity));
        }
        return $aliases;
    }

    /**
     * Sets titles on the aliases overview page
     */
    protected function setTitleListAlias()
    {
        $this->setTitle($this->text('Aliases'));
    }

    /**
     * Sets breadcrumbs on the aliases overview page
     */
    protected function setBreadcrumbListAlias()
    {
        $this->setBreadcrumbBackend();
    }

    /**
     * Render and output the alias overview page
     */
    protected function outputListAlias()
    {
        $this->output('content/alias/list');
    }

}
