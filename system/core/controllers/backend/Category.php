<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\backend;

use gplcart\core\models\Image as ImageModel;
use gplcart\core\models\Alias as AliasModel;
use gplcart\core\models\Category as CategoryModel;
use gplcart\core\models\CategoryGroup as CategoryGroupModel;
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
     * Image model instance
     * @var \gplcart\core\models\Image $image
     */
    protected $image;

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
     * @param ImageModel $image
     * @param CategoryGroupModel $category_group
     */
    public function __construct(CategoryModel $category, AliasModel $alias,
            ImageModel $image, CategoryGroupModel $category_group)
    {
        parent::__construct();

        $this->alias = $alias;
        $this->image = $image;
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

        $this->setTitleListCategory();
        $this->setBreadcrumbListCategory();

        $this->actionCategory();

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
            return $this->updateWeight($categories);
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
    protected function updateWeight(array $categories)
    {
        foreach ($categories as $category_id => $weight) {
            $this->category->update($category_id, array('weight' => $weight));
        }

        $message = $this->text('Categories have been reordered');
        $this->response->json(array('success' => $message));
    }

    /**
     * Returns an array of categories for a given group
     * @return array
     */
    protected function getListCategory()
    {
        $store = $this->store->get($this->data_category_group['store_id']);
        $url = $this->store->url($store);

        $options = array('category_group_id' => $this->data_category_group['category_group_id']);
        $categories = $this->category->getTree($options);

        $category_ids = array();
        foreach ($categories as $id => &$category) {
            $category_ids[] = $id;
            $category['url'] = "$url/category/$id";
            $category['indentation'] = str_repeat('— ', $category['depth']);
        }

        $aliases = $this->alias->getMultiple('category_id', $category_ids);

        if (empty($aliases)) {
            return $categories;
        }

        foreach ($categories as $id => &$category) {
            if (!empty($aliases[$id])) {
                $category['alias'] = $aliases[$id];
                $category['url'] = "$url/{$category['alias']}";
            }
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

        $this->setDataEditCategory();
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
        return (isset($this->data_category['category_id'])//
                && $this->category->canDelete($this->data_category['category_id'])//
                && $this->access('category_delete'));
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

        $category['alias'] = $this->alias->get('category_id', $category_id);

        $this->data_category = $category;
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

        if (!$this->isPosted('save')) {
            return null;
        }

        $this->setSubmitted('category', null, false);
        $this->validateCategory();

        if ($this->hasErrors('category')) {
            return null;
        }

        if (isset($this->data_category['category_id'])) {
            $this->updateCategory();
            return null;
        }

        $this->addCategory();
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
     * Performs validation checks on the given category
     */
    protected function validateCategory()
    {
        $this->setSubmittedBool('status');
        $this->setSubmitted('update', $this->data_category);

        if (empty($this->data_category['category_id'])) {
            $this->setSubmitted('user_id', $this->uid);
        }

        $this->validate('category');
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
     * Modifies categories
     * @return null
     */
    protected function setDataEditCategory()
    {
        $category_id = $this->getData('category.category_id');

        if (!isset($category_id)) {
            return null;
        }

        $categories = $this->getData('categories');
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

        $modified = array_diff_key($categories, array_flip($exclude));
        $this->setData('categories', $modified);

        $this->setDataImagesCategory();
    }

    /**
     * Sets data with attached images
     * @return null
     */
    protected function setDataImagesCategory()
    {
        $images = $this->getData('category.images');

        if (empty($images)) {
            return null;
        }

        $imagestyle = $this->config('image_style_admin', 2);

        foreach ($images as &$image) {
            $image['thumb'] = $this->image->url($imagestyle, $image['path']);
            $image['uploaded'] = filemtime(GC_FILE_DIR . '/' . $image['path']);
        }

        $attached = $this->render('common/image/attache', array(
            'images' => $images,
            'name_prefix' => 'category',
            'languages' => $this->languages,
        ));

        $this->setData('attached_images', $attached);
    }

    /**
     * Sets titles on the category edit page
     */
    protected function setTitleEditCategory()
    {
        $vars = array('%group' => $this->data_category_group['title']);
        $title = $this->text('Add category to %group', $vars);

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
            'text' => $this->text('Categories')
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
