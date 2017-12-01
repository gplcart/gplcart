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
     * @param AliasModel $alias
     */
    public function __construct(PageModel $page, CategoryModel $category, AliasModel $alias)
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
            $message = $this->text('Updated %num item(s)', array('%num' => $updated));
            $this->setMessage($message, 'success');
        }

        if ($deleted > 0) {
            $message = $this->text('Deleted %num item(s)', array('%num' => $deleted));
            $this->setMessage($message, 'success');
        }
    }

    /**
     * Sets pager
     * @return array
     */
    protected function setPagerListPage()
    {
        $options = $this->query_filter;
        $options['count'] = true;

        $pager = array(
            'query' => $this->query_filter,
            'total' => (int) $this->page->getList($options)
        );

        return $this->data_limit = $this->setPager($pager);
    }

    /**
     * Returns an array of pages
     * @return array
     */
    protected function getListPage()
    {
        $options = $this->query_filter;
        $options['limit'] = $this->data_limit;

        $list = (array) $this->page->getList($options);
        return $this->prepareListPage($list);
    }

    /**
     * Prepare an array of pages
     * @param array $items
     * @return array
     */
    protected function prepareListPage(array $items)
    {
        foreach ($items as &$item) {
            $this->setItemEntityUrl($item, $this->store, 'page');
        }

        return $items;
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
        $this->setData('languages', $this->language->getList(false, true));

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
        $page['alias'] = $this->alias->get('page', $page['page_id']);

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
        $file_ids = $this->getPosted('delete_images', array(), true, 'array');

        if (empty($file_ids)) {
            return false;
        }

        $options = array(
            'file_id' => $file_ids,
            'file_type' => 'image',
            'entity' => 'page',
            'entity_id' => $this->data_page['page_id']
        );

        return $this->image->deleteMultiple($options);
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
