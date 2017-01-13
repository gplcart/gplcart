<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\backend;

use gplcart\core\models\CategoryGroup as CategoryGroupModel;
use gplcart\core\controllers\backend\Controller as BackendController;

/**
 * Handles incoming requests and outputs data related to category groups
 */
class CategoryGroup extends BackendController
{
    /**
     * Category group model instance
     * @var \gplcart\core\models\CategoryGroup $category_group
     */
    protected $category_group;

    /**
     * The current category group
     * @var array
     */
    protected $data_category_group = array();

    /**
     * Constructor
     * @param CategoryGroupModel $category_group
     */
    public function __construct(CategoryGroupModel $category_group)
    {
        parent::__construct();

        $this->category_group = $category_group;
    }

    /**
     * Displays the category group overview page
     */
    public function listCategoryGroup()
    {
        $this->setTitleListCategoryGroup();
        $this->setBreadcrumbListCategoryGroup();

        $query = $this->getFilterQuery();

        $filters = array('title', 'store_id', 'type', 'category_group_id');
        $this->setFilter($filters, $query);

        $total = $this->getTotalCategoryGroup($query);
        $limit = $this->setPager($total, $query);

        $this->setData('stores', $this->store->getNames());
        $this->setData('groups', $this->getListCategoryGroup($limit, $query));

        $this->outputListCategoryGroup();
    }

    /**
     * Returns total number of category groups for pager
     * @param array $query
     * @return integer
     */
    protected function getTotalCategoryGroup(array $query)
    {
        $query['count'] = true;
        return (int) $this->category_group->getList($query);
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
        $breadcrumb = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')
        );

        $this->setBreadcrumb($breadcrumb);
    }

    /**
     * Renders the category group overview page
     */
    protected function outputListCategoryGroup()
    {
        $this->output('content/category/group/list');
    }

    /**
     * Displays the add/edit category group page
     * @param integer|null $category_group_id
     */
    public function editCategoryGroup($category_group_id = null)
    {
        $this->setCategoryGroup($category_group_id);

        $this->setTitleEditCategoryGroup();
        $this->setBreadcrumbEditCategoryGroup();

        $this->setData('stores', $this->store->getNames());
        $this->setData('types', $this->category_group->getTypes());
        $this->setData('category_group', $this->data_category_group);
        $this->setData('can_delete', $this->canDeleteCategoryGroup());

        $this->submitCategoryGroup();
        $this->outputEditCategoryGroup();
    }

    /**
     * Whether the category group can be deleted
     * @return boolean
     */
    protected function canDeleteCategoryGroup()
    {
        return (isset($this->data_category_group['category_group_id'])//
                && $this->category_group->canDelete($this->data_category_group['category_group_id'])//
                && $this->access('category_group_delete'));
    }

    /**
     * Returns an array of category group
     * @param integer $category_group_id
     * @return array
     */
    protected function setCategoryGroup($category_group_id)
    {
        if (!is_numeric($category_group_id)) {
            return array();
        }

        $category_group = $this->category_group->get($category_group_id);

        if (empty($category_group)) {
            $this->outputHttpStatus(404);
        }

        $this->data_category_group = $category_group;
        return $category_group;
    }

    /**
     * Saves a submitted category group
     * @return mixed
     */
    protected function submitCategoryGroup()
    {
        if ($this->isPosted('delete')) {
            return $this->deleteCategoryGroup();
        }

        if (!$this->isPosted('save')) {
            return null;
        }

        $this->setSubmitted('category_group');
        $this->validateCategoryGroup();

        if ($this->hasErrors('category_group')) {
            return null;
        }

        if (isset($this->data_category_group['category_group_id'])) {
            $this->updateCategoryGroup();
            return null;
        }

        $this->addCategoryGroup();
    }

    /**
     * Deletes a category group
     */
    protected function deleteCategoryGroup()
    {
        $this->controlAccess('category_group_delete');

        $deleted = $this->category_group->delete($this->data_category_group['category_group_id']);

        if ($deleted) {
            $message = $this->text('Category group has been deleted');
            $this->redirect('admin/content/category-group', $message, 'success');
        }

        $message = $this->text('Unable to delete this category group');
        $this->redirect('', $message, 'danger');
    }

    /**
     * Performs validation checks on the given category group
     */
    protected function validateCategoryGroup()
    {
        $this->setSubmitted('update', $this->data_category_group);
        $this->validate('category_group');
    }

    /**
     * Updates a category group
     */
    protected function updateCategoryGroup()
    {
        $this->controlAccess('category_group_edit');

        $values = $this->getSubmitted();
        $this->category_group->update($this->data_category_group['category_group_id'], $values);

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
     * Sets titles on the category group edit page
     */
    protected function setTitleEditCategoryGroup()
    {
        $title = $this->text('Add category group');

        if (isset($this->data_category_group['category_group_id'])) {
            $vars = array('%name' => $this->data_category_group['title']);
            $title = $this->text('Edit category group %name', $vars);
        }

        $this->setTitle($title);
    }

    /**
     * Sets breadcrumbs on the edit category group page
     */
    protected function setBreadcrumbEditCategoryGroup()
    {
        $breadcrumbs = array();

        $breadcrumbs[] = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')
        );

        $breadcrumbs[] = array(
            'url' => $this->url('admin/content/category-group'),
            'text' => $this->text('Category groups')
        );

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Renders the category group edit page
     */
    protected function outputEditCategoryGroup()
    {
        $this->output('content/category/group/edit');
    }

}
