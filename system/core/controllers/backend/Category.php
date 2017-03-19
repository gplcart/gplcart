<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\backend;

use gplcart\core\models\Alias as AliasModel,
    gplcart\core\models\Category as CategoryModel,
    gplcart\core\models\CategoryGroup as CategoryGroupModel;
use gplcart\core\controllers\backend\Controller as BackendController;

/**
 * Handles incoming requests and outputs data related to categories
 */
class Category extends BackendController
{

    /**
     * Category model instance
     * @var  \gplcart\core\models\Category $category
     */
    protected $category;

    /**
     * Category group model instance
     * @var \gplcart\core\models\CategoryGroup $category_group
     */
    protected $category_group;

    /**
     * Url model instance
     * @var \gplcart\core\models\Alias $alias
     */
    protected $alias;

    /**
     * The current category data
     * @var array
     */
    protected $data_category = array();

    /**
     * The current category group data
     * @var array
     */
    protected $data_category_group = array();

    /**
     * Constructor
     * @param CategoryModel $category
     * @param AliasModel $alias
     * @param CategoryGroupModel $category_group
     */
    public function __construct(CategoryModel $category, AliasModel $alias,
            CategoryGroupModel $category_group)
    {
        parent::__construct();

        $this->alias = $alias;
        $this->category = $category;
        $this->category_group = $category_group;
    }

    /**
     * Displays the category overview page
     * @param integer $category_group_id
     */
    public function listCategory($category_group_id)
    {
        $this->setCategoryGroup($category_group_id);

        $this->actionCategory();

        $this->setTitleListCategory();
        $this->setBreadcrumbListCategory();

        $this->setData('categories', $this->getListCategory());
        $this->setData('category_group_id', $category_group_id);

        $this->outputListCategory();
    }

    /**
     * Returns an array of category group data
     * @param integer $category_group_id
     * @return array
     */
    protected function setCategoryGroup($category_group_id)
    {
        $category_group = $this->category_group->get($category_group_id);

        if (empty($category_group)) {
            $this->outputHttpStatus(404);
        }

        $this->data_category_group = $category_group;
        return $category_group;
    }

    /**
     * Applies an action to selected categories
     * @return null
     */
    protected function actionCategory()
    {
        $action = (string) $this->request->post('action');

        if (empty($action)) {
            return null;
        }

        $value = (int) $this->request->post('value');
        $categories = (array) $this->request->post('selected', array());

        if ($action === 'weight' && $this->access('category_edit')) {
            return $this->updateWeightCategory($categories);
        }

        $updated = $deleted = 0;
        foreach ($categories as $category_id) {

            if ($action === 'status' && $this->access('category_edit')) {
                $updated += (int) $this->category->update($category_id, array(
                            'status' => $value));
            }

            if ($action === 'delete' && $this->access('category_delete')) {
                $deleted += (int) $this->category->delete($category_id);
            }
        }

        if ($updated > 0) {
            $message = $this->text('Categories have been updated');
            $this->setMessage($message, 'success', true);
        }

        if ($deleted > 0) {
            $message = $this->text('Categories have been deleted');
            $this->setMessage($message, 'success', true);
        }
    }

    /**
     * Updates weigth for an array of categories
     * @param array $categories
     */
    protected function updateWeightCategory(array $categories)
    {
        foreach ($categories as $category_id => $weight) {
            $this->category->update($category_id, array('weight' => $weight));
        }

        $message = $this->text('Items have been reordered');
        $this->response->json(array('success' => $message));
    }

    /**
     * Returns an array of categories for a given group
     * @return array
     */
    protected function getListCategory()
    {
        $options = array(
            'category_group_id' => $this->data_category_group['category_group_id']);

        $categories = $this->category->getTree($options);
        return $this->prepareListCategory($categories);
    }

    /**
     * Adds extra data to an array of categories
     * @param array $categories
     * @return array
     */
    protected function prepareListCategory(array $categories)
    {
        $this->attachEntityUrl($categories, 'category');

        foreach ($categories as &$category) {
            $category['indentation'] = str_repeat('— ', $category['depth']);
        }
        return $categories;
    }

    /**
     * Sets breadcrumbs to the category overview page
     */
    protected function setBreadcrumbListCategory()
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
     * Sets titles to the category overview page
     */
    protected function setTitleListCategory()
    {
        $vars = array('%name' => $this->data_category_group['title']);
        $text = $this->text('Categories of group %name', $vars);
        $this->setTitle($text);
    }

    /**
     * Renders the category overview page
     */
    protected function outputListCategory()
    {
        $this->output('content/category/list');
    }

    /**
     * Displays the add/edit category form
     * @param integer $category_group_id
     * @param integer|null $category_id
     */
    public function editCategory($category_group_id, $category_id = null)
    {
        $this->setCategory($category_id);
        $this->setCategoryGroup($category_group_id);

        $this->setTitleEditCategory();
        $this->setBreadcrumbEditCategory();

        $this->setData('category', $this->data_category);
        $this->setData('can_delete', $this->canDeleteCategory());
        $this->setData('category_group', $this->data_category_group);
        $this->setData('parent_id', (int) $this->request->get('parent_id'));
        $this->setData('categories', $this->getOptionsCategory($category_group_id));

        $this->submitCategory();

        $this->setDataImagesCategory();
        $this->setDataCategoriesCategory();
        $this->outputEditCategory();
    }

    /**
     * Returns an array of category options
     * @param integer $category_group_id
     * @return array
     */
    protected function getOptionsCategory($category_group_id)
    {
        return $this->category->getOptionList($category_group_id, 0);
    }

    /**
     * Whether the category can be deleted
     * @return boolean
     */
    protected function canDeleteCategory()
    {
        return isset($this->data_category['category_id'])//
                && $this->category->canDelete($this->data_category['category_id'])//
                && $this->access('category_delete');
    }

    /**
     * Returns an array of category data
     * @param integer $category_id
     * @return array
     */
    protected function setCategory($category_id)
    {
        if (!is_numeric($category_id)) {
            return array();
        }

        $category = $this->category->get($category_id);

        if (empty($category)) {
            $this->outputHttpStatus(404);
        }

        $prepared = $this->prepareCategory($category);
        return $this->data_category = $prepared;
    }

    /**
     * Prepares a category data
     * @param array $category
     */
    protected function prepareCategory(array $category)
    {
        $category['alias'] = $this->alias->get('category_id', $category['category_id']);
        return $category;
    }

    /**
     * Saves a submitted category
     * @return null
     */
    protected function submitCategory()
    {
        if ($this->isPosted('delete')) {
            $this->deleteCategory();
            return null;
        }

        if (!$this->isPosted('save') || !$this->validateCategory()) {
            return null;
        }

        $this->deleteImages($this->data_category, 'category');

        if (isset($this->data_category['category_id'])) {
            $this->updateCategory();
        } else {
            $this->addCategory();
        }
    }

    /**
     * Validates a submitted category
     * @return bool
     */
    protected function validateCategory()
    {
        $this->setSubmitted('category', null, false);

        $this->setSubmitted('form', true);
        $this->setSubmittedBool('status');
        $this->setSubmitted('update', $this->data_category);

        if (empty($this->data_category['category_id'])) {
            $this->setSubmitted('user_id', $this->uid);
        }

        $this->validate('category');

        return !$this->hasErrors('category');
    }

    /**
     * Deletes a category
     */
    protected function deleteCategory()
    {
        $this->controlAccess('category_delete');

        $deleted = $this->category->delete($this->data_category['category_id']);

        if ($deleted) {
            $message = $this->text('Category has been deleted');
            $url = "admin/content/category/{$this->data_category_group['category_group_id']}";
            $this->redirect($url, $message, 'success');
        }

        $message = $this->text('Unable to delete this category');
        $this->redirect('', $message, 'danger');
    }

    /**
     * Updates a category
     */
    protected function updateCategory()
    {
        $this->controlAccess('category_edit');

        $submitted = $this->getSubmitted();

        $this->category->update($this->data_category['category_id'], $submitted);

        $message = $this->text('Category has been updated');
        $url = "admin/content/category/{$this->data_category_group['category_group_id']}";

        $this->redirect($url, $message, 'success');
    }

    /**
     * Adds a new category
     */
    protected function addCategory()
    {
        $this->controlAccess('category_add');

        $this->category->add($this->getSubmitted());

        $message = $this->text('Category has been added');
        $url = "admin/content/category/{$this->data_category_group['category_group_id']}";

        $this->redirect($url, $message, 'success');
    }

    /**
     * Adds list of categories on the edit category page
     * @return null
     */
    protected function setDataCategoriesCategory()
    {
        $category_id = $this->getData('category.category_id');

        if (!isset($category_id)) {
            return null;
        }

        $category_group_id = $this->getData('category_group.category_group_id');

        $options = array(
            'parent_id' => $category_id,
            'category_group_id' => $category_group_id
        );

        $children = $this->category->getTree($options);

        $exclude = array($category_id);
        foreach ($children as $child) {
            $exclude[] = $child['category_id'];
        }

        $categories = $this->getData('categories');
        $modified = array_diff_key($categories, array_flip($exclude));
        $this->setData('categories', $modified);
    }

    /**
     * Adds images on the edit category page
     */
    protected function setDataImagesCategory()
    {
        $images = $this->getData('category.images', array());
        $this->attachThumbs($images);
        $this->setDataAttachedImages($images, 'category');
    }

    /**
     * Sets titles on the category edit page
     */
    protected function setTitleEditCategory()
    {
        $vars = array('%name' => $this->data_category_group['title']);
        $title = $this->text('Add category to %name', $vars);

        if (isset($this->data_category['category_id'])) {
            $vars = array('%name' => $this->data_category['title']);
            $title = $this->text('Edit category %name', $vars);
        }

        $this->setTitle($title);
    }

    /**
     * Sets breadcrumbs on the category edit page
     */
    protected function setBreadcrumbEditCategory()
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

        $breadcrumbs[] = array(
            'url' => $this->url("admin/content/category/{$this->data_category_group['category_group_id']}"),
            'text' => $this->text('Categories of group %name', array('%name' => $this->data_category_group['title']))
        );

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Renders the category edit page
     */
    protected function outputEditCategory()
    {
        $this->output('content/category/edit');
    }

}
