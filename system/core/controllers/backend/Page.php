<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\backend;

use gplcart\core\models\Page as PageModel;
use gplcart\core\models\Image as ImageModel;
use gplcart\core\models\Alias as AliasModel;
use gplcart\core\models\Category as CategoryModel;
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
     * Image model instance
     * @var \gplcart\core\models\Image $image
     */
    protected $image;

    /**
     * Constructor
     * @param PageModel $page
     * @param CategoryModel $category
     * @param AliasModel $alias
     * @param ImageModel $image
     */
    public function __construct(PageModel $page, CategoryModel $category,
            AliasModel $alias, ImageModel $image)
    {
        parent::__construct();

        $this->page = $page;
        $this->alias = $alias;
        $this->image = $image;
        $this->category = $category;
    }

    /**
     * Displays the page overview page
     */
    public function listPage()
    {
        $this->actionPage();

        $query = $this->getFilterQuery();
        $total = $this->getTotalPage($query);

        $limit = $this->setPager($total, $query);
        $pages = $this->getListPage($limit, $query);
        $stores = $this->store->getNames();

        $this->setData('pages', $pages);
        $this->setData('stores', $stores);

        $filters = array('title', 'store_id', 'page_id',
            'status', 'created', 'email');

        $this->setFilter($filters, $query);

        $this->setTitleListPage();
        $this->setBreadcrumbListPage();
        $this->outputListPage();
    }

    /**
     * Applies an action to the selected pages
     */
    protected function actionPage()
    {
        $action = (string) $this->request->post('action');

        if (empty($action)) {
            return null;
        }

        $value = (int) $this->request->post('value');
        $selected = (array) $this->request->post('selected', array());

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

        return null;
    }

    /**
     * Outputs JSON string with categories
     */
    protected function outputCategoriesPage()
    {
        $default = $this->store->getDefault();
        $store_id = (int) $this->request->post('store_id', $default);

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
        $stores = $this->store->getList();

        $query['limit'] = $limit;
        $pages = (array) $this->page->getList($query);

        foreach ($pages as &$page) {

            $page['url'] = '';
            if (isset($stores[$page['store_id']])) {
                $store = $stores[$page['store_id']];
                $page['url'] = rtrim("{$this->scheme}{$store['domain']}/{$store['basepath']}", "/")
                        . "/page/{$page['page_id']}";
            }
        }

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
        $page = $this->getPage($page_id);

        $this->actionPage();

        $stores = $this->store->getNames();

        $this->setData('page', $page);
        $this->setData('stores', $stores);

        $this->submitPage($page);
        $this->setDataEditPage();

        $this->setTitleEditPage($page);
        $this->setBreadcrumbEditPage();
        $this->outputEditPage();
    }

    /**
     * Returns a page
     * @param integer $page_id
     * @return array
     */
    protected function getPage($page_id)
    {
        if (!is_numeric($page_id)) {
            return array();
        }

        $page = $this->page->get($page_id);

        if (empty($page)) {
            $this->outputError(404);
        }

        return $this->preparePage($page);
    }

    /**
     * Prepares an array of page data
     * @param array $page
     * @return array
     */
    protected function preparePage(array $page)
    {
        $user = $this->user->get($page['user_id']);

        $page['author'] = $user['email'];
        $page['alias'] = $this->alias->get('page_id', $page['page_id']);

        if (empty($page['images'])) {
            return $page;
        }

        foreach ($page['images'] as &$image) {
            $image['translation'] = $this->image->getTranslation($image['file_id']);
        }

        return $page;
    }

    /**
     * Saves a submitted page
     * @param array $page
     * @return null|void
     */
    protected function submitPage(array $page = array())
    {
        if ($this->isPosted('delete')) {
            return $this->deletePage($page);
        }

        if (!$this->isPosted('save')) {
            return null;
        }

        $this->setSubmitted('page', null, false);
        $this->validatePage($page);

        if ($this->hasErrors('page')) {
            return null;
        }

        $this->deleteImagesPage();

        if (isset($page['page_id'])) {
            return $this->updatePage($page);
        }

        return $this->addPage();
    }

    /**
     * Deletes a page
     * @param array $page
     */
    protected function deletePage(array $page)
    {
        $this->controlAccess('page_delete');
        $this->page->delete($page['page_id']);

        $message = $this->text('Page has been deleted');
        $this->redirect('admin/content/page', $message, 'success');
    }

    /**
     * Validates a single page
     * @param array $page
     */
    protected function validatePage(array $page = array())
    {
        $this->setSubmittedBool('status');
        $this->setSubmitted('update', $page);

        if (empty($page['page_id'])) {
            $this->setSubmitted('user_id', $this->uid);
        }

        $this->validate('page');
    }

    /**
     * Deletes an array of submitted images
     */
    protected function deleteImagesPage()
    {
        $images = (array) $this->request->post('delete_image');
        $has_access = ($this->access('page_add') || $this->access('page_edit'));

        if (!$has_access || empty($images)) {
            return;
        }

        foreach ($images as $file_id) {
            $this->image->delete($file_id);
        }
    }

    /**
     * Updates a page with submitted values
     * @param array $page
     */
    protected function updatePage(array $page)
    {
        $this->controlAccess('page_edit');

        $submitted = $this->getSubmitted();
        $this->page->update($page['page_id'], $submitted);

        $message = $this->text('Page has been updated');
        $this->redirect('admin/content/page', $message, 'success');
    }

    /**
     * Adds a new page using an array of submitted values
     */
    protected function addPage()
    {
        $this->controlAccess('page_add');

        $submitted = $this->getSubmitted();
        $this->page->add($submitted);

        $message = $this->text('Page has been added');
        $this->redirect('admin/content/page', $message, 'success');
    }

    /**
     * Modifies page data before sending to templates
     */
    protected function setDataEditPage()
    {
        $default = $this->store->getDefault();
        $store_id = $this->getData('page.store_id', $default);

        $categories = $this->category->getOptionListByStore($store_id);
        $this->setData('categories', $categories);

        $images = $this->getData('page.images');

        if (!empty($images)) {

            $preset = $this->config('admin_image_preset', 2);

            foreach ($images as &$image) {
                $image['thumb'] = $this->image->url($preset, $image['path']);
                $image['uploaded'] = filemtime(GC_FILE_DIR . "/{$image['path']}");
            }

            $this->setData('page.images', $images);

            $options = array(
                'images' => $images,
                'name_prefix' => 'page',
                'languages' => $this->languages
            );

            $attached = $this->render('common/image/attache', $options);
            $this->setData('attached_images', $attached);
        }
    }

    /**
     * Sets titles on the page edit page
     * @param $page
     */
    protected function setTitleEditPage($page)
    {
        if (isset($page['page_id'])) {
            $title = $this->text('Edit page %title', array(
                '%title' => $page['title']
            ));
        } else {
            $title = $this->text('Add page');
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
