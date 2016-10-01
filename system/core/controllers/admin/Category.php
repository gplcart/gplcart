<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers\admin;

use core\controllers\admin\Controller as BackendController;
use core\models\Alias as ModelsAlias;
use core\models\Category as ModelsCategory;
use core\models\CategoryGroup as ModelsCategoryGroup;
use core\models\Image as ModelsImage;

/**
 * Handles incoming requests and outputs data related to categories
 */
class Category extends BackendController
{

    /**
     * Category model instance
     * @var  \core\models\Category $category
     */
    protected $category;

    /**
     * Category group model instance
     * @var \core\models\CategoryGroup $category_group
     */
    protected $category_group;

    /**
     * Url model instance
     * @var \core\models\Alias $alias
     */
    protected $alias;

    /**
     * Image model instance
     * @var \core\models\Image $image
     */
    protected $image;

    /**
     * Constructor
     * @param ModelsCategory $category
     * @param ModelsAlias $alias
     * @param ModelsImage $image
     * @param ModelsCategoryGroup $category_group
     */
    public function __construct(
        ModelsCategory $category,
        ModelsAlias $alias,
        ModelsImage $image,
        ModelsCategoryGroup $category_group
    ) {
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
        $category_group = $this->getCategoryGroup($category_group_id);

        $this->actionCategory();

        $categories = $this->getListCategory($category_group);

        $this->setData('categories', $categories);
        $this->setData('category_group_id', $category_group_id);

        $this->setBreadcrumbListCategory();
        $this->setTitleListCategory($category_group);
        $this->outputListCategory();
    }

    /**
     * Returns an array of category group data
     * @param integer $category_group_id
     * @return array
     */
    protected function getCategoryGroup($category_group_id)
    {
        $category_group = $this->category_group->get($category_group_id);

        if (empty($category_group)) {
            $this->outputError(404);
        }

        return $category_group;
    }

    /**
     * Applies an action to selected categories
     */
    protected function actionCategory()
    {
        $action = (string)$this->request->post('action');

        if (empty($action)) {
            return;
        }

        $value = (int)$this->request->post('value');
        $selected = (array)$this->request->post('selected', array());

        if ($action === 'weight' && $this->access('category_edit')) {

            foreach ($selected as $category_id => $weight) {
                $this->category->update($category_id, array('weight' => $weight));
            }

            $message = $this->text('Categories have been reordered');
            $this->response->json(array('success' => $message));
        }

        $updated = $deleted = 0;
        foreach ($selected as $category_id) {

            if ($action === 'status' && $this->access('category_edit')) {
                $updated += (int)$this->category->update($category_id, array(
                    'status' => $value
                ));
            }

            if ($action === 'delete' && $this->access('category_delete')) {
                $deleted += (int)$this->category->delete($category_id);
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
     * Returns an array of categories for a given group
     * @param array $category_group
     * @return array
     */
    protected function getListCategory(array $category_group)
    {
        $store = $this->store->get($category_group['store_id']);

        $categories = $this->category->getTree(array(
            'category_group_id' => $category_group['category_group_id']
        ));

        $category_ids = array();
        foreach ($categories as &$category) {
            $category_ids[] = $category['category_id'];
            $category['indentation'] = str_repeat('— ', $category['depth']);
            $category['url'] = rtrim("{$this->scheme}{$store['domain']}/{$store['basepath']}", "/")
                . "/category/{$category['category_id']}";
        }

        $aliases = $this->alias->getMultiple('category_id', $category_ids);

        if (empty($aliases)) {
            return $categories;
        }

        foreach ($categories as &$category) {
            $category['alias'] = '';
            if (!empty($aliases[$category['category_id']])) {
                $category['alias'] = $aliases[$category['category_id']];
                $category['url'] = rtrim("{$this->scheme}{$store['domain']}/{$store['basepath']}", "/")
                    . "/{$category['alias']}";
            }
        }

        return $categories;
    }

    /**
     * Sets breadcrumbs to the category overview page
     */
    protected function setBreadcrumbListCategory()
    {
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
     * @param array $category_group
     */
    protected function setTitleListCategory(array $category_group)
    {
        $text = $this->text('Categories of group %name', array(
            '%name' => $category_group['title']
        ));

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
        $category = $this->getCategory($category_id);
        $category_group = $this->getCategoryGroup($category_group_id);
        $categories = $this->category->getOptionList($category_group_id, 0);

        $parent_category = (int)$this->request->get('parent_id');
        $can_delete = (isset($category['category_id']) && $this->category->canDelete($category_id));

        $this->setData('category', $category);
        $this->setData('can_delete', $can_delete);
        $this->setData('categories', $categories);
        $this->setData('parent_id', $parent_category);
        $this->setData('category_group', $category_group);

        $this->submitCategory($category_group, $category);

        $this->setDataEditCategory();

        $this->setTitleEditCategory($category_group, $category);
        $this->setBreadcrumbEditCategory($category_group);
        $this->outputEditCategory();
    }

    /**
     * Returns an array of category data
     * @param integer $category_id
     * @return array
     */
    protected function getCategory($category_id)
    {
        if (!is_numeric($category_id)) {
            return array();
        }

        $category = $this->category->get($category_id);

        if (!empty($category)) {
            $category['alias'] = $this->alias->get('category_id', $category_id);
            return $category;
        }

        return $this->outputError(404);
    }

    /**
     * Saves a submitted category
     * @param array $category_group
     * @param array $category
     * @return mixed
     */
    protected function submitCategory(array $category_group, array $category)
    {
        if ($this->isPosted('delete')) {
            return $this->deleteCategory($category_group, $category);
        }

        if (!$this->isPosted('save')) {
            return null;
        }

        $this->setSubmitted('category', null, false);
        $this->validateCategory($category);

        if ($this->hasErrors('category')) {
            return null;
        }

        if (isset($category['category_id'])) {
            return $this->updateCategory($category_group, $category);
        }

        return $this->addCategory($category_group);
    }

    /**
     * Deletes a category
     * @param array $category_group
     * @param array $category
     */
    protected function deleteCategory(array $category_group, array $category)
    {
        $this->controlAccess('category_delete');

        $deleted = $this->category->delete($category['category_id']);

        if ($deleted) {
            $message = $this->text('Category has been deleted');
            $url = "admin/content/category/{$category_group['category_group_id']}";
            $this->redirect($url, $message, 'success');
        }

        $message = $this->text('Unable to delete this category.'
            . 'The most probable reason - it is used by one or more products');

        $this->redirect('', $message, 'danger');
    }

    /**
     * Performs validation checks on the given category
     * @param array $category
     */
    protected function validateCategory(array $category)
    {
        $this->setSubmittedBool('status');

        $this->addValidator('category_group_id', array(
            'required' => array()
        ));

        $this->addValidator('title', array(
            'length' => array('min' => 1, 'max' => 255)
        ));

        $this->addValidator('meta_title', array(
            'length' => array('max' => 255)
        ));

        $this->addValidator('meta_description', array(
            'length' => array('max' => 255)
        ));

        $this->addValidator('description_1', array(
            'length' => array('max' => 65535)
        ));

        $this->addValidator('description_2', array(
            'length' => array('max' => 65535)
        ));

        $this->addValidator('translation', array(
            'translation' => array()
        ));

        $alias = $this->getSubmitted('alias');

        if (empty($alias) && isset($category['category_id'])) {
            $submitted = $this->getSubmitted();
            $alias = $this->category->createAlias($submitted);
            $this->setSubmitted('alias', $alias);
        }

        $this->addValidator('alias', array(
            'length' => array('max' => 255),
            'regexp' => array('pattern' => '/^[A-Za-z0-9_.-]+$/'),
            'alias_unique' => array()
        ));

        $this->addValidator('images', array(
            'images' => array()
        ));

        $this->setValidators($category);

        $images = $this->getValidatorResult('images');
        $this->setSubmitted('images', $images);
    }

    /**
     * Updates a category
     * @param array $category_group
     * @param array $category
     */
    protected function updateCategory(array $category_group, array $category)
    {
        $this->controlAccess('category_edit');

        $submitted = $this->getSubmitted();
        $this->category->update($category['category_id'], $submitted);

        $message = $this->text('Category has been updated');
        $url = "admin/content/category/{$category_group['category_group_id']}";

        $this->redirect($url, $message, 'success');
    }

    /**
     * Adds a new category
     * @param array $category_group
     */
    protected function addCategory(array $category_group)
    {
        $this->controlAccess('category_add');

        $submitted = $this->getSubmitted();
        $this->category->add($submitted);

        $message = $this->text('Category has been added');
        $url = "admin/content/category/{$category_group['category_group_id']}";

        $this->redirect($url, $message, 'success');
    }

    /**
     * Modifies categories
     * @return null
     */
    protected function setDataEditCategory()
    {
        $category_id = $this->getData('category.category_id');

        if (isset($category_id)) {

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
        }

        $images = $this->getData('category.images');

        if (!empty($images)) {

            $preset = $this->config('admin_image_preset', 2);

            foreach ($images as &$image) {
                $image['thumb'] = $this->image->url($preset, $image['path']);
                $image['uploaded'] = filemtime(GC_FILE_DIR . '/' . $image['path']);
            }

            $attached = $this->render('common/image/attache', array(
                'images' => $images,
                'name_prefix' => 'category',
                'languages' => $this->languages,
            ));

            $this->setData('attached_images', $attached);
        }
    }

    /**
     * Sets titles on the category edit page
     * @param array $group
     * @param array $category
     */
    protected function setTitleEditCategory(array $group, array $category)
    {
        if (isset($category['category_id'])) {
            $title = $this->text('Edit category %name', array(
                '%name' => $category['title']
            ));
        } else {
            $title = $this->text('Add category to %group', array(
                '%group' => $group['title']
            ));
        }

        $this->setTitle($title);
    }

    /**
     * Sets breadcrumbs on the category edit page
     * @param array $category_group
     */
    protected function setBreadcrumbEditCategory(array $category_group)
    {
        $breadcrumbs[] = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')
        );

        $breadcrumbs[] = array(
            'url' => $this->url('admin/content/category-group'),
            'text' => $this->text('Category groups')
        );


        $breadcrumbs[] = array(
            'url' => $this->url("admin/content/category/{$category_group['category_group_id']}"),
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
