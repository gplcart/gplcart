<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers\admin;

use core\Controller;
use core\models\CategoryGroup as ModelsCategoryGroup;

/**
 * Handles incoming requests and outputs data related to category groups
 */
class CategoryGroup extends Controller
{

    /**
     * Category group model instance
     * @var \core\models\CategoryGroup $category_group
     */
    protected $category_group;

    /**
     * Constructor
     * @param ModelsCategoryGroup $category_group
     */
    public function __construct(ModelsCategoryGroup $category_group)
    {
        parent::__construct();

        $this->category_group = $category_group;
    }

    /**
     * Displays the category group overview page
     */
    public function listCategoryGroup()
    {
        $query = $this->getFilterQuery();
        $total = $this->getTotalCategoryGroup($query);
        $limit = $this->setPager($total, $query);

        $stores = $this->store->getNames();
        $groups = $this->getListCategoryGroup($limit, $query);

        $this->setData('groups', $groups);
        $this->setData('stores', $stores);

        $filters = array('title', 'store_id', 'type');
        $this->setFilter($filters, $query);

        $this->setTitleListCategoryGroup();
        $this->setBreadcrumbListCategoryGroup();
        $this->outputListCategoryGroup();
    }

    /**
     * Renders the category group overview page
     */
    protected function outputListCategoryGroup()
    {
        $this->output('content/category/group/list');
    }

    /**
     * Sets titles to the category group overview page
     */
    protected function setTitleListCategoryGroup()
    {
        $this->setTitle($this->text('Category groups'));
    }

    /**
     * Sets breadcrumbs to the category group overview page
     */
    protected function setBreadcrumbListCategoryGroup()
    {
        $breadcrumbs[] = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard'));

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Displays the add/edit category group page
     * @param integer|null $category_group_id
     */
    public function editCategoryGroup($category_group_id = null)
    {
        $stores = $this->store->getNames();
        $category_group = $this->getCategoryGroup($category_group_id);

        $can_delete = (isset($category_group['category_group_id']) && $this->category_group->canDelete($category_group_id) && $this->access('category_group_delete'));

        $this->setData('stores', $stores);
        $this->setData('can_delete', $can_delete);
        $this->setData('category_group', $category_group);

        $this->submitCategoryGroup($category_group);

        $this->setTitleEditCategoryGroup($category_group);
        $this->setBreadcrumbEditCategoryGroup();
        $this->outputEditCategoryGroup();
    }

    /**
     * Returns total number of category groups for pager
     * @param array $query
     * @return integer
     */
    protected function getTotalCategoryGroup(array $query)
    {
        $query['count'] = true;
        return $this->category_group->getList($query);
    }

    /**
     * Renders the category group edit page
     */
    protected function outputEditCategoryGroup()
    {
        $this->output('content/category/group/edit');
    }

    /**
     * Sets titles on the category group edit page
     * @param array $category_group
     */
    protected function setTitleEditCategoryGroup(array $category_group)
    {
        if (isset($category_group['category_group_id'])) {
            $title = $this->text('Edit category group %name', array(
                '%name' => $category_group['title']));
        } else {
            $title = $this->text('Add category group');
        }

        $this->setTitle($title);
    }

    /**
     * Sets breadcrumbs on the edit category group page
     */
    protected function setBreadcrumbEditCategoryGroup()
    {
        $breadcrumbs[] = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard'));

        $breadcrumbs[] = array(
            'url' => $this->url('admin/content/category-group'),
            'text' => $this->text('Category groups'));

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Returns an array of category group
     * @param integer $category_group_id
     * @return array
     */
    protected function getCategoryGroup($category_group_id)
    {
        if (!is_numeric($category_group_id)) {
            return array();
        }

        $category_group = $this->category_group->get($category_group_id);

        if (empty($category_group)) {
            $this->outputError(404);
        }

        return $category_group;
    }

    /**
     * Returns an array of category groups
     * @param array $limit
     * @param array $query
     * @return array
     */
    protected function getListCategoryGroup(array $limit, array $query)
    {
        $query['limit'] = $limit;
        return $this->category_group->getList($query);
    }

    /**
     * Deletes a category group
     * @param array $category_group
     */
    protected function deleteCategoryGroup(array $category_group)
    {
        $this->controlAccess('category_group_delete');

        $category_group_id = $category_group['category_group_id'];
        $deleted = $this->category_group->delete($category_group_id);

        if ($deleted) {
            $message = $this->text('Category group has been deleted');
            $this->redirect('admin/content/category-group', $message, 'success');
        }

        $message = $this->text('Unable to delete this category group');
        $this->redirect('', $message, 'danger');
    }

    /**
     * Saves a submitted category group
     * @param array $category_group
     * @return null
     */
    protected function submitCategoryGroup(array $category_group)
    {
        if ($this->isPosted('delete')) {
            return $this->deleteCategoryGroup($category_group);
        }

        if (!$this->isPosted('save')) {
            return;
        }

        $this->setSubmitted('category_group');
        $this->validateCategoryGroup($category_group);

        if ($this->hasErrors('category_group')) {
            return;
        }

        if (isset($category_group['category_group_id'])) {
            return $this->updateCategoryGroup($category_group);
        }

        $this->addCategoryGroup();
    }

    /**
     * Updates a category group
     * @param array $category_group
     */
    protected function updateCategoryGroup(array $category_group)
    {
        $this->controlAccess('category_group_edit');

        $values = $this->getSubmitted();
        $this->category_group->update($category_group['category_group_id'], $values);

        $message = $this->text('Category group has been updated');
        $this->redirect('admin/content/category-group', $message, 'success');
    }

    /**
     * Adds a new category group
     */
    protected function addCategoryGroup()
    {
        $this->controlAccess('category_group_add');

        $values = $this->getSubmitted();
        $this->category_group->add($values);

        $message = $this->text('Category group has been added');
        $this->redirect('admin/content/category-group', $message, 'success');
    }

    /**
     * Performs validation checks on the given category group
     * @param array $category_group
     */
    protected function validateCategoryGroup(array $category_group)
    {
        $this->addValidator('title', array(
            'length' => array('min' => 1, 'max' => 255)
        ));

        $this->addValidator('translation', array(
            'translation' => array()
        ));

        $category_group_id = null;
        if (isset($category_group['category_group_id'])) {
            $category_group_id = $category_group['category_group_id'];
        }

        $this->addValidator('type', array(
            'required' => array(),
            'category_group_type_unique' => array(
                'store_id' => $this->getSubmitted('store_id'),
                'category_group_id' => $category_group_id)
        ));

        $this->setValidators($category_group);
    }

}
