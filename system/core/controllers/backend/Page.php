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
     * URL model instance
     * @var \gplcart\core\models\Alias $alias
     */
    protected $alias;

    /**
     * The current page
     * @var array
     */
    protected $data_page = array();

    /**
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
     * Displays the page overview
     */
    public function listPage()
    {
        $this->actionPage();

        $this->setTitleListPage();
        $this->setBreadcrumbListPage();

        $this->setFilterListPage();
        $this->setTotalListPage();
        $this->setPagerLimit();

        $this->setData('pages', $this->getListPage());

        $this->outputListPage();
    }

    /**
     * Set filter on the page overview
     */
    protected function setFilterListPage()
    {
        $allowed = array('title', 'store_id', 'page_id',
            'status', 'created', 'email');

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

            if ($action == 'status' && $this->access('page_edit')) {
                $updated += (int) $this->page->update($page_id, array('status' => $value));
            }

            if ($action == 'delete' && $this->access('page_delete')) {
                $deleted += (int) $this->page->delete($page_id);
            }
        }

        if ($updated > 0) {
            $message = $this->text('Updated %num items', array('%num' => $updated));
            $this->setMessage($message, 'success');
        }

        if ($deleted > 0) {
            $message = $this->text('Deleted %num items', array('%num' => $deleted));
            $this->setMessage($message, 'success');
        }
    }

    /**
     * Sets a total number of pages for the current filter parameters
     */
    protected function setTotalListPage()
    {
        $query = $this->query_filter;
        $query['count'] = true;
        $this->total = (int) $this->page->getList($query);
    }

    /**
     * Returns an array of pages
     * @return array
     */
    protected function getListPage()
    {
        $query = $this->query_filter;
        $query['limit'] = $this->limit;
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
        $this->setBreadcrumbHome();
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
        if (is_numeric($page_id)) {
            $page = $this->page->get($page_id);
            if (empty($page)) {
                $this->outputHttpStatus(404);
            }
            $this->data_page = $this->preparePage($page);
        }
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
     * Handles a submitted page
     */
    protected function submitEditPage()
    {
        if ($this->isPosted('delete')) {
            $this->deletePage();
        } else if ($this->isPosted('save') && $this->validateEditPage()) {

            $this->deleteImages($this->data_page, 'page');

            if (isset($this->data_page['page_id'])) {
                $this->updatePage();
            } else {
                $this->addPage();
            }
        }
    }

    /**
     * Validates a submitted page
     * @return bool
     */
    protected function validateEditPage()
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
        $this->redirect('admin/content/page', $this->text('Page has been deleted'), 'success');
    }

    /**
     * Updates a page
     */
    protected function updatePage()
    {
        $this->controlAccess('page_edit');
        $this->page->update($this->data_page['page_id'], $this->getSubmitted());
        $this->redirect('admin/content/page', $this->text('Page has been updated'), 'success');
    }

    /**
     * Adds a new page
     */
    protected function addPage()
    {
        $this->controlAccess('page_add');
        $this->page->add($this->getSubmitted());
        $this->redirect('admin/content/page', $this->text('Page has been added'), 'success');
    }

    /**
     * Adds images on the page edit form
     */
    protected function setDataImagesEditPage()
    {
        $images = $this->getData('page.images', array());
        $this->attachThumbs($images);
        $this->setDataAttachedImages($images, 'page');
    }

    /**
     * Adds list of categories on the page edit form
     */
    protected function setDataCategoriesEditPage()
    {
        $default = $this->store->getDefault();
        $store_id = $this->getData('page.store_id', $default);
        $categories = $this->category->getOptionListByStore($store_id);

        $this->setData('categories', $categories);
    }

    /**
     * Sets titles on the page edit
     */
    protected function setTitleEditPage()
    {
        if (isset($this->data_page['page_id'])) {
            $vars = array('%name' => $this->data_page['title']);
            $title = $this->text('Edit %name', $vars);
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
        $this->setBreadcrumbHome();

        $breadcrumb = array(
            'text' => $this->text('Pages'),
            'url' => $this->url('admin/content/page')
        );

        $this->setBreadcrumb($breadcrumb);
    }

    /**
     * Render and output the page edit
     */
    protected function outputEditPage()
    {
        $this->output('content/page/edit');
    }

}
