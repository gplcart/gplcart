<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\backend;

use gplcart\core\controllers\backend\Controller as BackendController;
use gplcart\core\models\Currency as CurrencyModel;
use gplcart\core\models\Price as PriceModel;
use gplcart\core\models\PriceRule as PriceRuleModel;
use gplcart\core\models\Trigger as TriggerModel;

/**
 * Handles incoming requests and outputs data related to price rules
 */
class PriceRule extends BackendController
{

    /**
     * Trigger model instance
     * @var \gplcart\core\models\Trigger $trigger
     */
    protected $trigger;

    /**
     * Price rule model instance
     * @var \gplcart\core\models\PriceRule $rule
     */
    protected $price_rule;

    /**
     * Currency model instance
     * @var \gplcart\core\models\Currency $currency
     */
    protected $currency;

    /**
     * Price model instance
     * @var \gplcart\core\models\Price $price
     */
    protected $price;

    /**
     * Pager limit
     * @var array
     */
    protected $data_limit;

    /**
     * An array of price rule data
     * @var array
     */
    protected $data_rule = array();

    /**
     * @param PriceRuleModel $rule
     * @param CurrencyModel $currency
     * @param PriceModel $price
     * @param TriggerModel $trigger
     */
    public function __construct(PriceRuleModel $rule, CurrencyModel $currency, PriceModel $price, TriggerModel $trigger)
    {
        parent::__construct();

        $this->price = $price;
        $this->price_rule = $rule;
        $this->trigger = $trigger;
        $this->currency = $currency;
    }

    /**
     * Displays the price rule overview page
     */
    public function listPriceRule()
    {
        $this->actionListPriceRule();

        $this->setTitleListPriceRule();
        $this->setBreadcrumbListPriceRule();
        $this->setFilterListPriceRule();
        $this->setPagerListPriceRule();

        $this->setData('stores', $this->store->getList());
        $this->setData('price_rules', $this->getListPriceRule());

        $this->outputListPriceRule();
    }

    /**
     * Sets pager
     * @return array
     */
    protected function setPagerListPriceRule()
    {
        $conditions = $this->query_filter;
        $conditions['count'] = true;

        $pager = array(
            'query' => $this->query_filter,
            'total' => (int) $this->price_rule->getList($conditions)
        );

        return $this->data_limit = $this->setPager($pager);
    }

    /**
     * Set filter on the price rule overview page
     */
    protected function setFilterListPriceRule()
    {
        $allowed = array('name', 'code', 'value', 'price_rule_id',
            'value_type', 'weight', 'status', 'store_id', 'trigger_id');

        $this->setFilter($allowed);
    }

    /**
     * Applies an action to the selected price rules
     */
    protected function actionListPriceRule()
    {
        list($selected, $action, $value) = $this->getPostedAction();

        $deleted = $updated = 0;
        foreach ($selected as $rule_id) {

            if ($action === 'status' && $this->access('price_rule_edit')) {
                $updated += (int) $this->price_rule->update($rule_id, array('status' => $value));
            }

            if ($action === 'delete' && $this->access('price_rule_delete')) {
                $deleted += (int) $this->price_rule->delete($rule_id);
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
     * Returns an array of price rules
     * @return array
     */
    protected function getListPriceRule()
    {
        $conditions = $this->query_filter;
        $conditions['limit'] = $this->data_limit;

        return (array) $this->price_rule->getList($conditions);
    }

    /**
     * Sets titles on the price rule overview page
     */
    protected function setTitleListPriceRule()
    {
        $this->setTitle($this->text('Price rules'));
    }

    /**
     * Sets breadcrumbs on the price rule overview page
     */
    protected function setBreadcrumbListPriceRule()
    {
        $breadcrumb = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')
        );

        $this->setBreadcrumb($breadcrumb);
    }

    /**
     * Render and output the price rule overview page
     */
    protected function outputListPriceRule()
    {
        $this->output('sale/price/list');
    }

    /**
     * Displays the price rule edit form
     * @param null|integer $rule_id
     */
    public function editPriceRule($rule_id = null)
    {
        $this->setPriceRule($rule_id);
        $this->setTitleEditPriceRule();
        $this->setBreadcrumbEditPriceRule();

        $this->setData('price_rule', $this->data_rule);
        $this->setData('types', $this->price_rule->getTypes());
        $this->setData('triggers', $this->getTriggersPriceRule());
        $this->setData('currencies', $this->getCurrenciesPriceRule());

        $this->submitEditPriceRule();
        $this->outputEditPriceRule();
    }

    /**
     * Returns an array of enabled triggers
     * @return array
     */
    protected function getTriggersPriceRule()
    {
        $list = $this->trigger->getList();

        foreach ($list as &$item) {
            if (empty($item['status'])) {
                $item['name'] .= ' (' . $this->lower($this->text('Disabled')) . ')';
            }
        }

        return $list;
    }

    /**
     * Returns an array of enabled currencies
     * @return array
     */
    protected function getCurrenciesPriceRule()
    {
        return $this->currency->getList(array('enabled' => true));
    }

    /**
     * Returns an array of price rule data
     * @param null|integer $rule_id
     */
    protected function setPriceRule($rule_id)
    {
        if (is_numeric($rule_id)) {
            $this->data_rule = $this->price_rule->get($rule_id);
            if (empty($this->data_rule)) {
                $this->outputHttpStatus(404);
            }
        }
    }

    /**
     * Handles a submitted price rule
     */
    protected function submitEditPriceRule()
    {
        if ($this->isPosted('delete')) {
            $this->deletePriceRule();
        } else if ($this->isPosted('save') && $this->validateEditPriceRule()) {
            if (isset($this->data_rule['price_rule_id'])) {
                $this->updatePriceRule();
            } else {
                $this->addPriceRule();
            }
        }
    }

    /**
     * Validates a submitted price rule
     * @return bool
     */
    protected function validateEditPriceRule()
    {
        $this->setSubmitted('price_rule', null, false);
        $this->setSubmittedBool('status');
        $this->setSubmitted('update', $this->data_rule);

        $this->validateComponent('price_rule');

        return !$this->hasErrors();
    }

    /**
     * Deletes a price rule
     */
    protected function deletePriceRule()
    {
        $this->controlAccess('price_rule_delete');

        if ($this->price_rule->delete($this->data_rule['price_rule_id'])) {
            $this->redirect('admin/sale/price', $this->text('Price rule has been deleted'), 'success');
        }

        $this->redirect('', $this->text('Price rule has not been deleted'), 'warning');
    }

    /**
     * Updates a price rule
     */
    protected function updatePriceRule()
    {
        $this->controlAccess('price_rule_edit');

        if ($this->price_rule->update($this->data_rule['price_rule_id'], $this->getSubmitted())) {
            $this->redirect('admin/sale/price', $this->text('Price rule has been updated'), 'success');
        }

        $this->redirect('', $this->text('Price rule has not been updated'), 'warning');
    }

    /**
     * Adds a new price rule
     */
    protected function addPriceRule()
    {
        $this->controlAccess('price_rule_add');

        if ($this->price_rule->add($this->getSubmitted())) {
            $this->redirect('admin/sale/price', $this->text('Price rule has been added'), 'success');
        }

        $this->redirect('', $this->text('Price rule has not been added'), 'warning');
    }

    /**
     * Sets title on the edit price rule page
     */
    protected function setTitleEditPriceRule()
    {
        if (isset($this->data_rule['price_rule_id'])) {
            $title = $this->text('Edit %name', array('%name' => $this->data_rule['name']));
        } else {
            $title = $this->text('Add price rule');
        }

        $this->setTitle($title);
    }

    /**
     * Sets breadcrumbs on the edit price rule page
     */
    protected function setBreadcrumbEditPriceRule()
    {
        $breadcrumbs = array();

        $breadcrumbs[] = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')
        );

        $breadcrumbs[] = array(
            'text' => $this->text('Price rules'),
            'url' => $this->url('admin/sale/price')
        );

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Render and output the edit price rule page
     */
    protected function outputEditPriceRule()
    {
        $this->output('sale/price/edit');
    }

}
