<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers\admin;

use core\Controller;
use core\models\Alias as ModelsAlias;
use core\models\Image as ModelsImage;
use core\models\Category as ModelsCategory;
use core\models\CategoryGroup as ModelsCategoryGroup;

/**
 * Handles incoming requests and outputs data related to categories
 */
class Category extends Controller
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
    public function __construct(ModelsCategory $category, ModelsAlias $alias,
            ModelsImage $image, ModelsCategoryGroup $category_group)
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
    public function categories($category_group_id)
    {
        $category_group = $this->getCategoryGroup($category_group_id);

        if ($this->isSubmitted('action')) {
            $this->action();
        }

        $this->setData('category_group_id', $category_group_id);
        $this->setData('categories', $this->getCategories($category_group));

        $this->setBreadcrumbCategories();
        $this->setTitleCategories($category_group);
        $this->outputCategories();
    }

    /**
     * Displays the add/edit category form
     * @param integer $category_group_id
     * @param integer|null $category_id
     */
    public function edit($category_group_id, $category_id = null)
    {
        $category = $this->get($category_id);
        $category_group = $this->getCategoryGroup($category_group_id);

        $this->setData('category', $category);
        $this->setData('category_group', $category_group);
        $this->setData('categories', $this->category->getOptionList($category_group_id, 0));
        $this->setData('parent_id', (int) $this->request->get('parent_id'));

        $can_delete = (isset($category['category_id']) && $this->category->canDelete($category_id));
        $this->setData('can_delete', $can_delete);

        $this->setCategories();

        if ($this->isSubmitted('delete')) {
            $this->delete($category_group, $category);
        }

        if ($this->isSubmitted('save')) {
            $this->submit($category_group, $category);
        }

        $this->setImages();

        $this->setTitleCategory($category_group, $category);
        $this->setBreadcrumbCategory($category_group);
        $this->outputCategory();
    }

    /**
     * Returns an array of categories for a given group
     * @param array $category_group
     * @return array
     */
    protected function getCategories(array $category_group)
    {
        $store = $this->store->get($category_group['store_id']);
        $categories = $this->category->getTree(array('category_group_id' => $category_group['category_group_id']));

        $category_ids = array();
        foreach ($categories as &$category) {
            $category_ids[] = $category['category_id'];
            $category['indentation'] = str_repeat('— ', $category['depth']);
            $category['url'] = rtrim("{$this->scheme}{$store['domain']}/{$store['basepath']}", "/") . "/category/{$category['category_id']}";
        }

        $aliases = $this->alias->getMultiple('category_id', $category_ids);

        if (empty($aliases)) {
            return $categories;
        }

        foreach ($categories as &$category) {
            $category['alias'] = '';
            if (!empty($aliases[$category['category_id']])) {
                $category['alias'] = $aliases[$category['category_id']];
                $category['url'] = rtrim("{$this->scheme}{$store['domain']}/{$store['basepath']}", "/") . "/{$category['alias']}";
            }
        }

        return $categories;
    }

    /**
     * Renders the category overview page
     */
    protected function outputCategories()
    {
        $this->output('content/category/list');
    }

    /**
     * Sets breadcrumbs to the category overview page
     */
    protected function setBreadcrumbCategories()
    {
        $this->setBreadcrumb(array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')));

        $this->setBreadcrumb(array(
            'url' => $this->url('admin/content/category/group'),
            'text' => $this->text('Category groups')));
    }

    /**
     * Sets titles to the category overview page
     * @param array $category_group
     */
    protected function setTitleCategories(array $category_group)
    {
        $this->setTitle($this->text('Categories of group %name', array(
                    '%name' => $category_group['title'])));
    }

    /**
     * Renders the category edit page
     */
    protected function outputCategory()
    {
        $this->output('content/category/edit');
    }

    /**
     * Adds category images
     * @return null
     */
    protected function setImages()
    {
        $images = $this->getData('category.images');

        if (empty($images)) {
            return;
        }

        $preset = $this->config->get('admin_image_preset', 2);

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

    /**
     * Modifies categories
     * @return null
     */
    protected function setCategories()
    {

        $category_id = $this->getData('category.category_id');

        if (!isset($category_id)) {
            return;
        }

        $categories = $this->getData('categories');
        $category_group_id = $this->getData('category_group.category_group_id');

        $children = $this->category->getTree(array(
            'parent_id' => $category_id,
            'category_group_id' => $category_group_id
        ));

        $exclude = array($category_id);
        foreach ($children as $child) {
            $exclude[] = $child['category_id'];
        }

        $modified = array_diff_key($categories, array_flip($exclude));
        $this->setData('categories', $modified);
    }

    /**
     * Sets titles on the category edit page
     * @param array $category_group
     * @param array $category
     */
    protected function setTitleCategory(array $category_group, array $category)
    {
        if (isset($category['category_id'])) {
            $title = $this->text('Edit category %name', array('%name' => $category['title']));
        } else {
            $title = $this->text('Add category to %group', array('%group' => $category_group['title']));
        }

        $this->setTitle($title);
    }

    /**
     * Sets breadcrumbs on the category edit page
     * @param array $category_group
     */
    protected function setBreadcrumbCategory(array $category_group)
    {
        $this->setBreadcrumb(array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')));

        $this->setBreadcrumb(array(
            'url' => $this->url('admin/content/category/group'),
            'text' => $this->text('Category groups')));

        $this->setBreadcrumb(array(
            'url' => $this->url("admin/content/category/{$category_group['category_group_id']}"),
            'text' => $this->text('Categories')));
    }

    /**
     * Returns an array of category data
     * @param integer $category_id
     * @return array
     */
    protected function get($category_id)
    {
        if (!is_numeric($category_id)) {
            return array();
        }

        $category = $this->category->get($category_id);

        if (!empty($category)) {
            $category['alias'] = $this->alias->get('category_id', $category['category_id']);
            return $category;
        }

        $this->outputError(404);
    }

    /**
     * Deletes a category
     * @param array $category_group
     * @param array $category
     */
    protected function delete(array $category_group, array $category)
    {
        $this->controlAccess('category_delete');

        $deleted = $this->category->delete($category['category_id']);

        if ($deleted) {
            $this->redirect("admin/content/category/{$category_group['category_group_id']}", $this->text('Category has been deleted'), 'success');
        }

        $this->redirect('', $this->text('Unable to delete this category. The most probable reason - it is used by one or more products'), 'danger');
    }

    /**
     * Returns an array of category group data
     * @param integer $category_group_id
     * @return array
     */
    protected function getCategoryGroup($category_group_id)
    {
        $category_group = $this->category_group->get($category_group_id);

        if (!empty($category_group)) {
            return $category_group;
        }

        $this->outputError(404);
    }

    /**
     * Applies an action to selected categories
     * @return boolean
     */
    protected function action()
    {

        $action = $this->request->post('action');
        $value = $this->request->post('value');
        $selected = (array) $this->request->post('selected', array());

        if ($action === 'weight' && $this->access('category_edit')) {

            foreach ($selected as $category_id => $weight) {
                $this->category->update($category_id, array('weight' => $weight));
            }

            $this->response->json(array('success' => $this->text('Categories have been reordered')));
        }

        $updated = $deleted = 0;
        foreach ($selected as $category_id) {

            if ($action === 'status' && $this->access('category_edit')) {
                $updated += (int) $this->category->update($category_id, array('status' => (int) $value));
            }

            if ($action === 'delete' && $this->access('category_delete')) {
                $deleted += (int) $this->category->delete($category_id);
            }
        }

        if ($updated > 0) {
            $this->session->setMessage($this->text('Categories have been updated'), 'success');
            return true;
        }

        if ($deleted > 0) {
            $this->session->setMessage($this->text('Categories have been deleted'), 'success');
            return true;
        }

        return false;
    }

    /**
     * Saves a submitted category
     * @param array $category_group
     * @param array $category
     * @return null
     */
    protected function submit(array $category_group, array $category)
    {
        $this->setSubmitted('category', null, false);
        $this->validate($category);

        if ($this->hasErrors('category')) {
            return;
        }

        if (isset($category['category_id'])) {
            $this->controlAccess('category_edit');
            $this->category->update($category['category_id'], $this->submitted);
            $this->redirect("admin/content/category/{$category_group['category_group_id']}", $this->text('Category has been updated'), 'success');
        }

        $this->controlAccess('category_add');
        $this->category->add($this->submitted);
        $this->redirect("admin/content/category/{$category_group['category_group_id']}", $this->text('Category has been added'), 'success');
    }

    /**
     * Performs validation checks on the given category
     * @param array $category
     */
    protected function validate(array $category)
    {
        // Fix checkbox
        $this->setSubmittedBool('status');

        // Validate fields
        $this->addValidator('title', array('length' => array('min' => 1, 'max' => 255)));
        $this->addValidator('meta_title', array('length' => array('max' => 255)));
        $this->addValidator('meta_description', array('length' => array('max' => 255)));
        $this->addValidator('translation', array('translation' => array()));

        $alias = $this->getSubmitted('alias');
        if (empty($alias) && isset($category['category_id'])) {
            // Generate an alias for existing category if the field was empty
            $this->setSubmitted('alias', $this->category->createAlias($this->getSubmitted()));
        }

        $this->addValidator('alias', array(
            'regexp' => array('pattern' => '/^[A-Za-z0-9_.-]+$/'),
            'alias' => array()));

        $this->addValidator('images', array('images' => array()));
        $this->setValidators($category);
        
        $images = $this->getValidatorResult('images');
        $this->setData('images', $images);
    }

}
