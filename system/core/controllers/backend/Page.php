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
use gplcart\core\models\Page as PageModel;
use gplcart\core\models\TranslationEntity as TranslationEntityModel;
use gplcart\core\traits\Category as CategoryTrait;

/**
 * Handles incoming requests and outputs data related to pages
 */
class Page extends Controller
{

    use CategoryTrait;

    /**
     * Page model instance
     * @var \gplcart\core\models\Page $page
     */
    protected $page;

    /**
     * URL model instance
     * @var \gplcart\core\models\Alias $alias
     */
    protected $alias;

    /**
     * Category model instance
     * @var \gplcart\core\models\Category $category
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
     * Pager limit
     * @var array
     */
    protected $data_limit;

    /**
     * The current page
     * @var array
     */
    protected $data_page = array();

    /**
     * @param PageModel $page
     * @param CategoryModel $category
     * @param CategoryGroupModel $category_group
     * @param AliasModel $alias
     * @param TranslationEntityModel $translation_entity
     */
    public function __construct(PageModel $page, CategoryModel $category,
                                CategoryGroupModel $category_group, AliasModel $alias,
                                TranslationEntityModel $translation_entity)
    {
        parent::__construct();

        $this->page = $page;
        $this->alias = $alias;
        $this->category = $category;
        $this->category_group = $category_group;
        $this->translation_entity = $translation_entity;
    }

    /**
     * Displays the page overview
     */
    public function listPage()
    {
        $this->actionPage();
        $this->setTitleListPage();
        $this->setBreadcrumbListPage();
        $this->setFilterListPage();
        $this->setPagerListPage();

        $this->setData('pages', $this->getListPage());
        $this->outputListPage();
    }

    /**
     * Set filter on the page overview
     */
    protected function setFilterListPage()
    {
        $allowed = array('title', 'store_id', 'page_id',
            'status', 'created', 'email', 'blog_post');

        $this->setFilter($allowed);
    }

    /**
     * Applies an action to the selected pages
     */
    protected function actionPage()
    {
        list($selected, $action, $value) = $this->getPostedAction();

        $deleted = $updated = 0;

        foreach ($selected as $page_id) {

            if ($this->access('page_edit')) {
                if ($action === 'status') {
                    $updated += (int) $this->page->update($page_id, array('status' => $value));
                } else if ($action === 'blog_post') {
                    $updated += (int) $this->page->update($page_id, array('blog_post' => $value));
                }
            }

            if ($action === 'delete' && $this->access('page_delete')) {
                $deleted += (int) $this->page->delete($page_id);
            }
        }

        if ($updated > 0) {
            $this->setMessage($this->text('Updated %num item(s)', array('%num' => $updated)), 'success');
        }

        if ($deleted > 0) {
            $this->setMessage($this->text('Deleted %num item(s)', array('%num' => $deleted)), 'success');
        }
    }

    /**
     * Sets pager
     * @return array
     */
    protected function setPagerListPage()
    {
        $conditions = $this->query_filter;
        $conditions['count'] = true;

        $pager = array(
            'query' => $this->query_filter,
            'total' => (int) $this->page->getList($conditions)
        );

        return $this->data_limit = $this->setPager($pager);
    }

    /**
     * Returns an array of pages
     * @return array
     */
    protected function getListPage()
    {
        $conditions = $this->query_filter;
        $conditions['limit'] = $this->data_limit;

        $list = (array) $this->page->getList($conditions);
        $this->prepareListPage($list);
        return $list;
    }

    /**
     * Prepare an array of pages
     * @param array $list
     */
    protected function prepareListPage(array &$list)
    {
        foreach ($list as &$item) {
            $this->setItemUrlEntity($item, $this->store, 'page');
        }
    }

    /**
     * Sets titles on the page overview page
     */
    protected function setTitleListPage()
    {
        $this->setTitle($this->text('Pages'));
    }

    /**
     * Sets breadcrumbs on the page overview page
     */
    protected function setBreadcrumbListPage()
    {
        $breadcrumb = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')
        );

        $this->setBreadcrumb($breadcrumb);
    }

    /**
     * Render and output the page overview
     */
    protected function outputListPage()
    {
        $this->output('content/page/list');
    }

    /**
     * Displays the page edit form
     * @param integer|null $page_id
     */
    public function editPage($page_id = null)
    {
        $this->setPage($page_id);
        $this->setTitleEditPage();
        $this->setBreadcrumbEditPage();

        $this->setData('page', $this->data_page);
        $this->setData('languages', $this->language->getList(array('enabled' => true)));

        $this->submitEditPage();
        $this->setDataImagesEditPage();
        $this->setDataCategoriesEditPage();
        $this->outputEditPage();
    }

    /**
     * Set a page data
     * @param integer $page_id
     */
    protected function setPage($page_id)
    {
        $this->data_page = array();

        if (is_numeric($page_id)) {

            $conditions = array(
                'language' => 'und',
                'page_id' => $page_id
            );

            $this->data_page = $this->page->get($conditions);

            if (empty($this->data_page)) {
                $this->outputHttpStatus(404);
            }

            $this->preparePage($this->data_page);
        }
    }

    /**
     * Prepares an array of page data
     * @param array $page
     */
    protected function preparePage(array &$page)
    {
        $user = $this->user->get($page['user_id']);

        $this->setItemAlias($page, 'page', $this->alias);
        $this->setItemImages($page, 'page', $this->image);
        $this->setItemTranslation($page, 'page', $this->translation_entity);

        if (!empty($page['images'])) {
            foreach ($page['images'] as &$file) {
                $this->setItemTranslation($file, 'file', $this->translation_entity);
            }
        }

        $page['author'] = isset($user['email']) ? $user['email'] : $this->text('Unknown');
    }

    /**
     * Handles a submitted page
     */
    protected function submitEditPage()
    {
        if ($this->isPosted('delete')) {
            $this->deletePage();
        } else if ($this->isPosted('save') && $this->validateEditPage()) {
            $this->deleteImagesPage();
            if (isset($this->data_page['page_id'])) {
                $this->updatePage();
            } else {
                $this->addPage();
            }
        }
    }

    /**
     * Delete page images
     * @return boolean
     */
    protected function deleteImagesPage()
    {
        $this->controlAccess('page_edit');

        $file_ids = $this->getPosted('delete_images', array(), true, 'array');
        return $this->image->delete($file_ids);
    }

    /**
     * Validates a submitted page
     * @return bool
     */
    protected function validateEditPage()
    {
        $this->setSubmitted('page', null, false);
        $this->setSubmitted('form', true);
        $this->setSubmitted('update', $this->data_page);
        $this->setSubmittedBool('status');
        $this->setSubmittedBool('blog_post');

        if (empty($this->data_page['page_id'])) {
            $this->setSubmitted('user_id', $this->uid);
        }

        $this->validateComponent('page');

        return !$this->hasErrors();
    }

    /**
     * Deletes a page
     */
    protected function deletePage()
    {
        $this->controlAccess('page_delete');

        if ($this->page->delete($this->data_page['page_id'])) {
            $this->redirect('admin/content/page', $this->text('Page has been deleted'), 'success');
        }

        $this->redirect('', $this->text('Page has not been deleted'), 'warning');
    }

    /**
     * Updates a page
     */
    protected function updatePage()
    {
        $this->controlAccess('page_edit');

        if ($this->page->update($this->data_page['page_id'], $this->getSubmitted())) {
            $this->redirect('admin/content/page', $this->text('Page has been updated'), 'success');
        }

        $this->redirect('', $this->text('Page has not been updated'), 'warning');
    }

    /**
     * Adds a new page
     */
    protected function addPage()
    {
        $this->controlAccess('page_add');

        if ($this->page->add($this->getSubmitted())) {
            $this->redirect('admin/content/page', $this->text('Page has been added'), 'success');
        }

        $this->redirect('', $this->text('Page has not been added'), 'warning');
    }

    /**
     * Adds images on the page edit form
     */
    protected function setDataImagesEditPage()
    {
        $options = array(
            'entity' => 'page',
            'images' => $this->getData('page.images', array())
        );

        $this->setItemThumb($options, $this->image);
        $this->setData('attached_images', $this->getWidgetImages($this->language, $options));
    }

    /**
     * Adds list of categories on the page edit form
     */
    protected function setDataCategoriesEditPage()
    {
        $op = array('store_id' => $this->getData('page.store_id', $this->store->getDefault()));
        $categories = $this->getCategoryOptionsByStore($this->category, $this->category_group, $op);
        $this->setData('categories', $categories);
    }

    /**
     * Sets titles on the page edit
     */
    protected function setTitleEditPage()
    {
        if (isset($this->data_page['page_id'])) {
            $title = $this->text('Edit %name', array('%name' => $this->data_page['title']));
        } else {
            $title = $this->text('Add page');
        }

        $this->setTitle($title);
    }

    /**
     * Sets breadcrumbs on the page edit
     */
    protected function setBreadcrumbEditPage()
    {
        $breadcrumbs = array();

        $breadcrumbs[] = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')
        );

        $breadcrumbs[] = array(
            'text' => $this->text('Pages'),
            'url' => $this->url('admin/content/page')
        );

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Render and output the page edit
     */
    protected function outputEditPage()
    {
        $this->output('content/page/edit');
    }

}
