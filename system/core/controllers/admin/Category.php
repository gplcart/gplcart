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
use core\models\Alias;
use core\models\Image;
use core\models\CategoryGroup;
use core\models\Category as C;

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
     * @param C $category
     * @param Alias $alias
     * @param Image $image
     * @param CategoryGroup $category_group
     */
    public function __construct(C $category, Alias $alias, Image $image,
                                CategoryGroup $category_group)
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

        $action = $this->request->post('action');
        $value = $this->request->post('value');
        $selected = $this->request->post('selected', array());

        if ($action) {
            $this->action($selected, $action, $value);
        }

        $this->data['category_group_id'] = $category_group_id;
        $this->data['categories'] = $this->getCategories($category_group);

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
        $category_group = $this->getCategoryGroup($category_group_id);
        $category = $this->get($category_id);

        $this->data['category'] = $category;
        $this->data['category_group'] = $category_group;
        $this->data['categories'] = $this->category->getOptionList($category_group_id, 0);
        $this->data['parent_id'] = (int) $this->request->get('parent_id');
        $this->data['can_delete'] = (isset($category['category_id']) && $this->category->canDelete($category_id));

        $this->setCategories();

        if ($this->request->post('delete')) {
            $this->delete($category_group, $category);
        }

        if ($this->request->post('save')) {
            $this->submit($category_group, $category);
        }

        $this->setImages();

        $this->setTitleCategory($category_group, $category);
        $this->setBreadcrumbCategory($category_group);
        $this->outputCategory();
    }

    protected function getCategories($category_group)
    {
        $store = $this->store->get($category_group['store_id']);
        $categories = $this->category->getTree(array('category_group_id' => $category_group['category_group_id']));

        $category_ids = array();
        foreach ($categories as &$category) {
            $category_ids[] = $category['category_id'];
            $category['indentation'] = str_repeat('— ', $category['depth']);
            $category['url'] = rtrim("{$store['scheme']}{$store['domain']}/{$store['basepath']}", "/") . "/category/{$category['category_id']}";
        }

        $aliases = $this->alias->getMultiple('category_id', $category_ids);

        if (empty($aliases)) {
            return $categories;
        }

        foreach ($categories as &$category) {
            $category['alias'] = '';
            if (!empty($aliases[$category['category_id']])) {
                $category['alias'] = $aliases[$category['category_id']];
                $category['url'] = rtrim("{$store['scheme']}{$store['domain']}/{$store['basepath']}", "/") . "/{$category['alias']}";
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
    protected function setTitleCategories($category_group)
    {
        $this->setTitle($this->text('Categories of group %name', array('%name' => $category_group['title'])));
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
        if (empty($this->data['category']['images'])) {
            return;
        }

        foreach ($this->data['category']['images'] as &$image) {
            $image['thumb'] = $this->image->url($this->config->get('admin_image_preset', 2), $image['path']);
            $image['uploaded'] = filemtime(GC_FILE_DIR . '/' . $image['path']);
        }

        $this->data['attached_images'] = $this->render('common/image/attache', array(
            'name_prefix' => 'category',
            'languages' => $this->languages,
            'images' => $this->data['category']['images'])
        );
    }

    /**
     * Modifies categories
     * @return null
     */
    protected function setCategories()
    {
        if (!isset($this->data['category']['category_id'])) {
            return;
        }

        $category_id = $this->data['category']['category_id'];
        $category_group_id = $this->data['category_group']['category_group_id'];
        $children = $this->category->getTree(array('category_group_id' => $category_group_id, 'parent_id' => $category_id));

        $exclude = array($category_id);
        foreach ($children as $child) {
            $exclude[] = $child['category_id'];
        }

        $this->data['categories'] = array_diff_key($this->data['categories'], array_flip($exclude));
    }

    /**
     * Sets titles on the category edit page
     * @param array $category_group
     * @param array $category
     */
    protected function setTitleCategory($category_group, $category)
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
     * @param array $category
     */
    protected function setBreadcrumbCategory($category_group)
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
    protected function delete($category_group, $category)
    {
        $this->controlAccess('category_delete');

        if ($this->category->delete($category['category_id'])) {
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
     * @param array $selected
     * @param string $action
     * @param string $value
     * @return boolean
     */
    protected function action($selected, $action, $value)
    {
        if ($action == 'weight' && $this->access('category_edit')) {
            foreach ($selected as $category_id => $weight) {
                $this->category->update($category_id, array('weight' => $weight));
            }

            $this->response->json(array('success' => $this->text('Categories have been reordered')));
        }

        $updated = $deleted = 0;
        foreach ($selected as $category_id) {
            if ($action == 'status' && $this->access('category_edit')) {
                $updated += (int) $this->category->update($category_id, array('status' => (int) $value));
            }

            if ($action == 'delete' && $this->access('category_delete')) {
                $deleted += (int) $this->category->delete($category_id);
            }
        }

        if ($updated) {
            $this->session->setMessage($this->text('Categories have been updated'), 'success');
            return true;
        }

        if ($deleted) {
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
    protected function submit($category_group, $category)
    {
        $this->submitted = $this->request->post('category', array(), false);
        $this->validate($category);

        if ($this->formErrors()) {
            $this->data['category'] = $this->submitted;
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
    protected function validate($category)
    {
        $this->submitted['parent_id'] = isset($this->submitted['parent_id']) ? (int) $this->submitted['parent_id'] : 0;
        $this->submitted['status'] = !empty($this->submitted['status']);

        $this->validateAlias($category);
        $this->validateTitle($category);
        $this->validateMetaTitle($category);
        $this->validateMetaDescription($category);
        $this->validateTranslation($category);
        $this->validateImages();
    }

    /**
     * Validates category images
     * @return boolean
     */
    protected function validateImages()
    {
        if (empty($this->submitted['images'])) {
            return true;
        }

        foreach ($this->submitted['images'] as &$image) {
            if (empty($image['title']) && isset($this->submitted['title'])) {
                $image['title'] = $this->submitted['title'];
            }

            if (empty($image['description']) && isset($this->submitted['title'])) {
                $image['description'] = $this->submitted['title'];
            }

            $image['title'] = $this->truncate($image['title'], 255);

            if (empty($image['translation'])) {
                continue;
            }

            foreach ($image['translation'] as &$translation) {
                $translation['title'] = $this->truncate($translation['title'], 255);
            }
        }
    }

    /**
     * Validates / creates a URL alis
     * @param array $category
     * @return boolean
     */
    protected function validateAlias($category)
    {
        if (empty($this->submitted['alias'])) {
            if (isset($category['category_id'])) {
                $this->submitted['alias'] = $this->category->createAlias($this->submitted);
            }
            return true;
        }

        $check_alias = (isset($category['alias']) && ($category['alias'] !== $this->submitted['alias']));
        if ($check_alias && $this->alias->exists($this->submitted['alias'])) {
            $this->data['form_errors']['alias'] = $this->text('URL alias already exists');
            return false;
        }

        return true;
    }

    /**
     * Validates title
     * @param array $category
     */
    protected function validateTitle($category)
    {
        if (empty($this->submitted['title']) || (mb_strlen($this->submitted['title']) > 255)) {
            $this->data['form_errors']['title'] = $this->text('Content must be %min - %max characters long', array(
                '%min' => 1, '%max' => 255));
            return false;
        }

        return true;
    }

    /**
     * Validates meta title
     * @param array $category
     */
    protected function validateMetaTitle($category)
    {
        if (mb_strlen($this->submitted['meta_title']) > 255) {
            $this->data['form_errors']['meta_title'] = $this->text('Content must not exceed %s characters', array('%s' => 255));
            return false;
        }

        return true;
    }

    /**
     * Validates meta description
     * @param array $category
     */
    protected function validateMetaDescription($category)
    {
        if (mb_strlen($this->submitted['meta_description']) > 255) {
            $this->data['form_errors']['meta_description'] = $this->text('Content must not exceed %s characters', array(
                '%s' => 255));
            return false;
        }

        return true;
    }

    /**
     * Validates category translations
     * @param array $category
     */
    protected function validateTranslation($category)
    {
        if (empty($this->submitted['translation'])) {
            return true;
        }

        $has_errors = false;
        foreach ($this->submitted['translation'] as $lang => $translation) {
            if (empty($translation['title']) || (mb_strlen($translation['title']) > 255)) {
                $this->data['form_errors']['translation'][$lang]['title'] = $this->text('Content must be %min - %max characters long', array(
                    '%min' => 1, '%max' => 255));
                $has_errors = true;
            }

            if (mb_strlen($translation['meta_title']) > 255) {
                $this->data['form_errors']['translation'][$lang]['meta_title'] = $this->text('Content must not exceed %s characters', array('%s' => 255));
                $has_errors = true;
            }

            if (mb_strlen($translation['meta_description']) > 255) {
                $this->data['form_errors']['translation'][$lang]['meta_description'] = $this->text('Content must not exceed %s characters', array('%s' => 255));
                $has_errors = true;
            }
        }

        return !$has_errors;
    }
}
