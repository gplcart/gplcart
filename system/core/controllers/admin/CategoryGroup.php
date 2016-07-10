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
    public function groups()
    {
        $query = $this->getFilterQuery();
        $limit = $this->setPager($this->getTotalGroups($query), $query);

        $this->data['groups'] = $this->getGroups($limit, $query);
        $this->data['stores'] = $this->store->getNames();

        $filters = array('title', 'store_id', 'type');
        $this->setFilter($filters, $query);

        $this->setTitleGroups();
        $this->setBreadcrumbGroups();
        $this->outputGroups();
    }

    /**
     * Displays the add/edit category group page
     * @param integer|null $category_group_id
     */
    public function edit($category_group_id = null)
    {
        $category_group = $this->get($category_group_id);
        $this->data['category_group'] = $category_group;
        $this->data['stores'] = $this->store->getNames();
        $this->data['can_delete'] = (isset($category_group['category_group_id']) && $this->category_group->canDelete($category_group_id));

        if ($this->request->post('delete')) {
            $this->delete($category_group);
        }

        if ($this->request->post('save')) {
            $this->submit($category_group);
        }

        $this->setTitleEdit($category_group);
        $this->setBreadcrumbEdit();
        $this->outputEdit();
    }

    /**
     * Returns total number of category groups for pager
     * @param array $query
     * @return integer
     */
    protected function getTotalGroups(array $query)
    {
        return $this->category_group->getList(array('count' => true) + $query);
    }

    /**
     * Renders the category group overview page
     */
    protected function outputGroups()
    {
        $this->output('content/category/group/list');
    }

    /**
     * Sets titles to the category group overview page
     */
    protected function setTitleGroups()
    {
        $this->setTitle($this->text('Category groups'));
    }

    /**
     * Sets breadcrumbs to the category group overview page
     */
    protected function setBreadcrumbGroups()
    {
        $this->setBreadcrumb(array('url' => $this->url('admin'), 'text' => $this->text('Dashboard')));
    }

    /**
     * Renders the category group edit page
     */
    protected function outputEdit()
    {
        $this->output('content/category/group/edit');
    }

    /**
     * Sets titles on the category group edit page
     * @param array $category_group
     */
    protected function setTitleEdit(array $category_group)
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
    protected function setBreadcrumbEdit()
    {
        $this->setBreadcrumb(array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')));

        $this->setBreadcrumb(array(
            'url' => $this->url('admin/content/category/group'),
            'text' => $this->text('Category groups')));
    }

    /**
     * Returns an array of category group
     * @param integer $category_group_id
     * @return array
     */
    protected function get($category_group_id)
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
    protected function getGroups(array $limit, array $query)
    {
        return $this->category_group->getList(array('limit' => $limit) + $query);
    }

    /**
     * Deletes a category group
     * @param array $category_group
     */
    protected function delete(array $category_group)
    {
        $category_group_id = $category_group['category_group_id'];

        $this->controlAccess('category_group_delete');

        $deleted = $this->category_group->delete($category_group_id);

        if ($deleted) {
            $this->redirect('admin/content/category/group', $this->text('Category group has been deleted'), 'success');
        }

        $this->redirect('', $this->text('Unable to delete this category group'), 'danger');
    }

    /**
     * Saves a submitted category group
     * @param array $category_group
     * @return null
     */
    protected function submit(array $category_group)
    {
        $this->submitted = $this->request->post('category_group', array());

        $this->validate();

        $errors = $this->formErrors();

        if (!empty($errors)) {
            $this->data['category_group'] = $this->submitted + $category_group;
            return;
        }

        if (isset($category_group['category_group_id'])) {
            $this->controlAccess('category_group_edit');
            $this->category_group->update($category_group['category_group_id'], $this->submitted);
            $this->redirect('admin/content/category/group', $this->text('Category group has been updated'), 'success');
        }

        $this->controlAccess('category_group_add');
        $this->category_group->add($this->submitted);
        $this->redirect('admin/content/category/group', $this->text('Category group has been added'), 'success');
    }

    /**
     * Performs validation checks on the given category group
     */
    protected function validate()
    {
        $this->validateCategoryGroup();
        $this->validateTitle();
        $this->validateTranslations();
    }

    /**
     * Validates a category group
     * @return boolean
     */
    protected function validateCategoryGroup()
    {
        if (isset($this->submitted['category_group_id']) && $this->category_group->exists($this->submitted['type'], $this->submitted['store_id'], $this->submitted['category_group_id'])) {
            $this->data['form_errors']['type'] = $this->text('Wrong category group type');
            return false;
        }
        return true;
    }

    /**
     * Validates a category group title
     * @return boolean
     */
    protected function validateTitle()
    {
        if (empty($this->submitted['title']) || mb_strlen($this->submitted['title']) > 255) {
            $this->data['form_errors']['title'] = $this->text('Content must be %min - %max characters long', array('%min' => 1, '%max' => 255));
            return false;
        }
        return true;
    }

    /**
     * Validates category group translations
     * @return boolean
     */
    protected function validateTranslations()
    {
        if (empty($this->submitted['translation'])) {
            return true;
        }

        $has_errors = false;
        foreach ($this->submitted['translation'] as $language => $translation) {
            if (mb_strlen($translation['title']) > 255) {
                $this->data['form_errors']['translation'][$language]['title'] = $this->text('Content must not exceed %s characters', array('%s' => 255));
                $has_errors = true;
            }
        }

        return !$has_errors;
    }
}
