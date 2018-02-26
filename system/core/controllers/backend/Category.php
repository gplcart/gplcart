<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\backend;

use gplcart\core\models\Alias as AliasModel;
use gplcart\core\models\Category as CategoryModel;
use gplcart\core\models\CategoryGroup as CategoryGroupModel;
use gplcart\core\models\TranslationEntity as TranslationEntityModel;
use gplcart\core\traits\Category as CategoryTrait;

/**
 * Handles incoming requests and outputs data related to categories
 */
class Category extends Controller
{

    use CategoryTrait;

    /**
     * URL model instance
     * @var \gplcart\core\models\Alias $alias
     */
    protected $alias;

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
     * Entity translation model instance
     * @var \gplcart\core\models\TranslationEntity $translation_entity
     */
    protected $translation_entity;

    /**
     * Pager limits
     * @var array
     */
    protected $data_limit;

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
     * @param CategoryModel $category
     * @param AliasModel $alias
     * @param CategoryGroupModel $category_group
     * @param TranslationEntityModel $translation_entity
     */
    public function __construct(CategoryModel $category, AliasModel $alias, CategoryGroupModel $category_group,
                                TranslationEntityModel $translation_entity)
    {
        parent::__construct();

        $this->alias = $alias;
        $this->category = $category;
        $this->category_group = $category_group;
        $this->translation_entity = $translation_entity;
    }

    /**
     * Displays the category overview page
     * @param integer $category_group_id
     */
    public function listCategory($category_group_id)
    {
        $this->setCategoryGroup($category_group_id);
        $this->actionListCategory();

        $this->setTitleListCategory();
        $this->setBreadcrumbListCategory();
        $this->setFilterListCategory();
        $this->setPagerListCategory();

        $this->setData('categories', $this->getListCategory());
        $this->setData('category_group_id', $category_group_id);

        $this->outputListCategory();
    }

    /**
     * Sets the current filter parameters
     */
    protected function setFilterListCategory()
    {
        $allowed = array('title', 'status', 'weight', 'category_id');
        $this->setFilter($allowed);
    }

    /**
     * Sets pager
     * @return array
     */
    protected function setPagerListCategory()
    {
        $options = $this->query_filter;
        $options['count'] = true;

        $pager = array(
            'query' => $this->query_filter,
            'total' => (int) $this->category->getList($options)
        );

        return $this->data_limit = $this->setPager($pager);
    }

    /**
     * Sets an array of category group data
     * @param integer $category_group_id
     */
    protected function setCategoryGroup($category_group_id)
    {
        $this->data_category_group = $this->category_group->get($category_group_id);

        if (empty($this->data_category_group)) {
            $this->outputHttpStatus(404);
        }
    }

    /**
     * Applies an action to the selected categories
     */
    protected function actionListCategory()
    {
        list($selected, $action, $value) = $this->getPostedAction();

        $updated = $deleted = 0;

        foreach ($selected as $category_id) {

            if ($action === 'status' && $this->access('category_edit')) {
                $updated += (int) $this->category->update($category_id, array('status' => $value));
            }

            if ($action === 'delete' && $this->access('category_delete')) {
                $deleted += (int) $this->category->delete($category_id);
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
     * Returns an array of categories for a given group
     * @return array
     */
    protected function getListCategory()
    {
        $conditions = $this->query_filter;
        $conditions['category_group_id'] = $this->data_category_group['category_group_id'];
        $list = $this->category->getTree($conditions);

        $this->prepareListCategory($list);
        return $list;
    }

    /**
     * Adds extra data to an array of categories
     * @param array $categories
     */
    protected function prepareListCategory(array &$categories)
    {
        foreach ($categories as &$category) {
            $this->setItemIndentation($category, '— ');
            $this->setItemUrlEntity($category, $this->store, 'category');
        }
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
        $this->setTitle($this->text('Categories of group %name', array(
            '%name' => $this->data_category_group['title'])));
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
        $this->setData('parent_id', $this->getQuery('parent_id'));
        $this->setData('categories', $this->getOptionsCategory($category_group_id));
        $this->setData('languages', $this->language->getList(array('in_database' => true)));

        $this->submitEditCategory();

        $this->setDataImagesEditCategory();
        $this->setDataCategoriesEditCategory();
        $this->outputEditCategory();
    }

    /**
     * Returns an array of category options
     * @param integer $category_group_id
     * @return array
     */
    protected function getOptionsCategory($category_group_id)
    {
        $options = array(
            'category_group_id' => $category_group_id
        );

        return $this->getCategoryOptions($this->category, $options);
    }

    /**
     * Whether the category can be deleted
     * @return boolean
     */
    protected function canDeleteCategory()
    {
        return isset($this->data_category['category_id'])
            && $this->category->canDelete($this->data_category['category_id'])
            && $this->access('category_delete');
    }

    /**
     * Sets an array of category data
     * @param integer $category_id
     */
    protected function setCategory($category_id)
    {
        $this->data_category = array();

        if (is_numeric($category_id)) {

            //Set unexisting language code to get original titles.
            //Otherwise it will be translated to the current language
            $conditions = array(
                'language' => 'und',
                'category_id' => $category_id
            );

            $this->data_category = $this->category->get($conditions);

            if (empty($this->data_category)) {
                $this->outputHttpStatus(404);
            }

            $this->prepareCategory($this->data_category);
        }
    }

    /**
     * Prepares an array of category data
     * @param array $category
     */
    protected function prepareCategory(array &$category)
    {
        $this->setItemAlias($category, 'category', $this->alias);
        $this->setItemImages($category, 'category', $this->image);
        $this->setItemTranslation($category, 'category', $this->translation_entity);

        if (!empty($category['images'])) {
            foreach ($category['images'] as &$file) {
                $this->setItemTranslation($file, 'file', $this->translation_entity);
            }
        }
    }

    /**
     * Saves a submitted category
     */
    protected function submitEditCategory()
    {
        if ($this->isPosted('delete')) {
            $this->deleteCategory();
        } else if ($this->isPosted('save') && $this->validateEditCategory()) {
            $this->deleteImagesCategory();
            if (isset($this->data_category['category_id'])) {
                $this->updateCategory();
            } else {
                $this->addCategory();
            }
        }
    }

    /**
     * Delete category images
     * @return boolean
     */
    protected function deleteImagesCategory()
    {
        $this->controlAccess('category_edit');

        $file_ids = $this->getPosted('delete_images', array(), true, 'array');
        return $this->image->delete($file_ids);
    }

    /**
     * Validates a submitted category
     * @return bool
     */
    protected function validateEditCategory()
    {
        $this->setSubmitted('category', null, false);
        $this->setSubmitted('form', true);
        $this->setSubmittedBool('status');
        $this->setSubmitted('update', $this->data_category);
        $this->setSubmitted('category_group_id', $this->data_category_group['category_group_id']);

        $this->validateComponent('category');

        return !$this->hasErrors();
    }

    /**
     * Deletes a category
     */
    protected function deleteCategory()
    {
        $this->controlAccess('category_delete');

        if ($this->category->delete($this->data_category['category_id'])) {
            $url = "admin/content/category/{$this->data_category_group['category_group_id']}";
            $this->redirect($url, $this->text('Category has been deleted'), 'success');
        }

        $this->redirect('', $this->text('Category has not been deleted'), 'warning');
    }

    /**
     * Updates a category
     */
    protected function updateCategory()
    {
        $this->controlAccess('category_edit');

        if ($this->category->update($this->data_category['category_id'], $this->getSubmitted())) {
            $url = "admin/content/category/{$this->data_category_group['category_group_id']}";
            $this->redirect($url, $this->text('Category has been updated'), 'success');
        }

        $this->redirect('', $this->text('Category has not been updated'), 'warning');
    }

    /**
     * Adds a new category
     */
    protected function addCategory()
    {
        $this->controlAccess('category_add');

        if ($this->category->add($this->getSubmitted())) {
            $url = "admin/content/category/{$this->data_category_group['category_group_id']}";
            $this->redirect($url, $this->text('Category has been added'), 'success');
        }

        $this->redirect('', $this->text('Category has not been added'), 'warning');
    }

    /**
     * Adds list of categories on the edit category page
     */
    protected function setDataCategoriesEditCategory()
    {
        $category_id = $this->getData('category.category_id');

        if (isset($category_id)) {

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
    }

    /**
     * Adds images on the edit category page
     */
    protected function setDataImagesEditCategory()
    {
        $options = array(
            'entity' => 'category',
            'images' => $this->getData('category.images', array())
        );

        $this->setItemThumb($options, $this->image);
        $this->setData('attached_images', $this->getWidgetImages($this->language, $options));
    }

    /**
     * Sets titles on the category edit page
     */
    protected function setTitleEditCategory()
    {
        if (isset($this->data_category['category_id'])) {
            $title = $this->text('Edit %name', array('%name' => $this->data_category['title']));
        } else {

            $parent_category_id = $this->getQuery('parent_id');

            $parent_category = array();
            if (!empty($parent_category_id)) {
                $parent_category = $this->category->get($parent_category_id);
            }

            $vars = array('%name' => $this->data_category_group['title']);

            if (isset($parent_category['title'])) {
                $vars['%category'] = $parent_category['title'];
                $title = $this->text('Add sub-category to %name / %category', $vars);
            } else {
                $title = $this->text('Add category to %name', $vars);
            }
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
     * Render and output the category edit page
     */
    protected function outputEditCategory()
    {
        $this->output('content/category/edit');
    }

}
