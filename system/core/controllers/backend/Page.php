<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\backend;

use gplcart\core\models\Page as PageModel,
    gplcart\core\models\Alias as AliasModel,
    gplcart\core\models\Category as CategoryModel;
use gplcart\core\controllers\backend\Controller as BackendController;

/**
 * Handles incoming requests and outputs data related to pages
 */
class Page extends BackendController
{

    /**
     * Page model instance
     * @var \gplcart\core\models\Page $page
     */
    protected $page;

    /**
     * Category model instance
     * @var \gplcart\core\models\Category $category
     */
    protected $category;

    /**
     * Url model instance
     * @var \gplcart\core\models\Alias $alias
     */
    protected $alias;

    /**
     * The current page
     * @var array
     */
    protected $data_page = array();

    /**
     * Constructor
     * @param PageModel $page
     * @param CategoryModel $category
     * @param AliasModel $alias
     */
    public function __construct(PageModel $page, CategoryModel $category,
            AliasModel $alias)
    {
        parent::__construct();

        $this->page = $page;
        $this->alias = $alias;
        $this->category = $category;
    }

    /**
     * Displays the page overview page
     */
    public function listPage()
    {
        $this->actionPage();

        $this->setTitleListPage();
        $this->setBreadcrumbListPage();

        $query = $this->getFilterQuery();

        $filters = array('title', 'store_id', 'page_id',
            'status', 'created', 'email');

        $this->setFilter($filters, $query);

        $total = $this->getTotalPage($query);
        $limit = $this->setPager($total, $query);

        $this->setData('stores', $this->store->getNames());
        $this->setData('pages', $this->getListPage($limit, $query));

        $this->outputListPage();
    }

    /**
     * Applies an action to the selected pages
     */
    protected function actionPage()
    {
        $action = (string) $this->getPosted('action');

        if (empty($action)) {
            return null;
        }

        $value = (int) $this->getPosted('value');
        $selected = (array) $this->getPosted('selected', array());

        if ($action === 'categories') {
            $this->outputCategoriesPage();
        }

        $deleted = $updated = 0;
        foreach ($selected as $page_id) {

            if ($action == 'status' && $this->access('page_edit')) {
                $updated += (int) $this->page->update($page_id, array('status' => $value));
            }

            if ($action == 'delete' && $this->access('page_delete')) {
                $deleted += (int) $this->page->delete($page_id);
            }
        }

        if ($updated > 0) {
            $this->setMessage($this->text('Pages have been updated'), 'success', true);
        }

        if ($deleted > 0) {
            $this->setMessage($this->text('Pages have been deleted'), 'success', true);
        }
    }

    /**
     * Outputs JSON string with categories
     */
    protected function outputCategoriesPage()
    {
        $default = $this->store->getDefault();
        $store_id = (int) $this->getPosted('store_id', $default);

        $categories = $this->category->getOptionListByStore($store_id);
        $this->response->json($categories);
    }

    /**
     * Returns number of total pages for pager
     * @param array $query
     * @return integer
     */
    protected function getTotalPage(array $query)
    {
        $query['count'] = true;
        return (int) $this->page->getList($query);
    }

    /**
     * Returns an array of pages
     * @param array $limit
     * @param array $query
     * @return array
     */
    protected function getListPage(array $limit, array $query)
    {
        $query['limit'] = $limit;
        $pages = (array) $this->page->getList($query);

        $this->attachEntityUrl($pages, 'page');
        return $pages;
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
            'text' => $this->text('Dashboard'),
            'url' => $this->url('admin')
        );

        $this->setBreadcrumb($breadcrumb);
    }

    /**
     * Renders templates for the pages overview page
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

        $this->actionPage();

        $this->setTitleEditPage();
        $this->setBreadcrumbEditPage();

        $this->setData('page', $this->data_page);
        $this->setData('stores', $this->store->getNames());

        $this->submitPage();

        $this->setDataImagesPage();
        $this->setDataCategoriesPage();

        $this->outputEditPage();
    }

    /**
     * Returns a page
     * @param integer $page_id
     * @return array
     */
    protected function setPage($page_id)
    {
        if (!is_numeric($page_id)) {
            return array();
        }

        $page = $this->page->get($page_id);

        if (empty($page)) {
            $this->outputHttpStatus(404);
        }

        return $this->data_page = $this->preparePage($page);
    }

    /**
     * Prepares an array of page data
     * @param array $page
     * @return array
     */
    protected function preparePage(array $page)
    {
        $user = $this->user->get($page['user_id']);
        $page['author'] = isset($user['email']) ? $user['email'] : $this->text('Unknown');
        $page['alias'] = $this->alias->get('page_id', $page['page_id']);
        return $page;
    }

    /**
     * Saves a submitted page
     * @return null
     */
    protected function submitPage()
    {
        if ($this->isPosted('delete')) {
            $this->deletePage();
            return null;
        }

        if (!$this->isPosted('save') || !$this->validatePage()) {
            return null;
        }

        $this->deleteImages($this->data_page, 'page');

        if (isset($this->data_page['page_id'])) {
            $this->updatePage();
        } else {
            $this->addPage();
        }
    }

    /**
     * Validates a single page
     * @return bool
     */
    protected function validatePage()
    {
        $this->setSubmitted('page', null, false);

        $this->setSubmittedBool('form');
        $this->setSubmittedBool('status');
        $this->setSubmitted('update', $this->data_page);

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

        $this->page->delete($this->data_page['page_id']);

        $message = $this->text('Page has been deleted');
        $this->redirect('admin/content/page', $message, 'success');
    }

    /**
     * Updates a page with submitted values
     */
    protected function updatePage()
    {
        $this->controlAccess('page_edit');

        $submitted = $this->getSubmitted();
        $this->page->update($this->data_page['page_id'], $submitted);

        $message = $this->text('Page has been updated');
        $this->redirect('admin/content/page', $message, 'success');
    }

    /**
     * Adds a new page using an array of submitted values
     */
    protected function addPage()
    {
        $this->controlAccess('page_add');

        $this->page->add($this->getSubmitted());

        $message = $this->text('Page has been added');
        $this->redirect('admin/content/page', $message, 'success');
    }

    /**
     * Adds images on the page edit form
     */
    protected function setDataImagesPage()
    {
        $images = $this->getData('page.images', array());
        $this->attachThumbs($images);
        $this->setDataAttachedImages($images, 'page');
    }

    /**
     * Adds list of categories on the page edit form
     */
    protected function setDataCategoriesPage()
    {
        $default = $this->store->getDefault();
        $store_id = $this->getData('page.store_id', $default);
        $categories = $this->category->getOptionListByStore($store_id);

        $this->setData('categories', $categories);
    }

    /**
     * Sets titles on the page edit page
     */
    protected function setTitleEditPage()
    {
        $title = $this->text('Add page');

        if (isset($this->data_page['page_id'])) {
            $vars = array('%name' => $this->data_page['title']);
            $title = $this->text('Edit page %name', $vars);
        }

        $this->setTitle($title);
    }

    /**
     * Sets breadcrumbs on the page edit page
     */
    protected function setBreadcrumbEditPage()
    {
        $breadcrumbs = array();

        $breadcrumbs[] = array(
            'text' => $this->text('Dashboard'),
            'url' => $this->url('admin')
        );

        $breadcrumbs[] = array(
            'text' => $this->text('Pages'),
            'url' => $this->url('admin/content/page')
        );

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Renders the page edit templates
     */
    protected function outputEditPage()
    {
        $this->output('content/page/edit');
    }

}
