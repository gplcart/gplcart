<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers\admin;

use core\Controller;
use core\models\Page as ModelsPage;
use core\models\Alias as ModelsAlias;
use core\models\Image as ModelsImage;
use core\models\Category as ModelsCategory;

/**
 * Handles incoming requests and outputs data related to pages
 */
class Page extends Controller
{

    /**
     * Page model instance
     * @var \core\models\Page $page
     */
    protected $page;

    /**
     * Category model instance
     * @var \core\models\Category $category
     */
    protected $category;

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
     * @param ModelsPage $page
     * @param ModelsCategory $category
     * @param ModelsAlias $alias
     * @param ModelsImage $image
     */
    public function __construct(ModelsPage $page, ModelsCategory $category,
            ModelsAlias $alias, ModelsImage $image)
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
    public function pages()
    {
        if ($this->isSubmitted('action')) {
            $this->action();
        }

        $query = $this->getFilterQuery();
        $total = $this->getTotalPages($query);
        $limit = $this->setPager($total, $query);
        $pages = $this->getPages($limit, $query);
        $stores = $this->store->getNames();

        $this->setData('pages', $pages);
        $this->setData('stores', $stores);

        $filters = array('title', 'store_id',
            'status', 'created', 'email', 'front');

        $this->setFilter($filters, $query);

        if ($this->isSubmitted('save')) {
            $this->submit();
        }

        $this->setTitlePages();
        $this->setBreadcrumbPages();
        $this->outputPages();
    }

    /**
     * Displays the page edit form
     * @param integer|null $page_id
     */
    public function edit($page_id = null)
    {
        $page = $this->get($page_id);

        if ($this->isSubmitted('action')) {
            $this->action();
        }

        if ($this->isSubmitted('delete')) {
            $this->delete($page);
        }

        $stores = $this->store->getNames();

        $this->setData('page', $page);
        $this->setData('stores', $stores);

        if ($this->isSubmitted('save')) {
            $this->submit($page);
        }

        $this->setDataPage();

        $this->setTitleEdit($page);
        $this->setBreadcrumbEdit();
        $this->outputEdit();
    }

    /**
     * Returns number of total pages for pager
     * @param array $query
     * @return integer
     */
    protected function getTotalPages(array $query)
    {
        $options = array('count' => true);
        $options += $query;

        return $this->page->getList($options);
    }

    /**
     * Sets titles on the page overview page
     */
    protected function setTitlePages()
    {
        $this->setTitle($this->text('Pages'));
    }

    /**
     * Sets breadcrumbs on the page overview page
     */
    protected function setBreadcrumbPages()
    {
        $this->setBreadcrumb(array(
            'text' => $this->text('Dashboard'),
            'url' => $this->url('admin')));
    }

    /**
     * Renders templates for the pages overview page
     */
    protected function outputPages()
    {
        $this->output('content/page/list');
    }

    /**
     * Applies an action to the selected pages
     * @return boolean
     */
    protected function action()
    {
        $value = (int) $this->request->post('value');
        $action = (string) $this->request->post('action');
        $selected = (array) $this->request->post('selected', array());

        if ($action === 'categories') {
            $store_id = (int) $this->request->post('store_id', $this->store->getDefault());
            $this->response->json($this->category->getOptionListByStore($store_id));
        }

        $deleted = $updated = 0;
        foreach ($selected as $page_id) {

            if ($action == 'status' && $this->access('page_edit')) {
                $updated += (int) $this->page->update($page_id, array('status' => $value));
            }

            if ($action == 'front' && $this->access('page_edit')) {
                $updated += (int) $this->page->update($page_id, array('front' => $value));
            }

            if ($action == 'delete' && $this->access('page_delete')) {
                $deleted += (int) $this->page->delete($page_id);
            }
        }

        if ($updated > 0) {
            $this->session->setMessage($this->text('Pages have been updated'), 'success');
            return true;
        }

        if ($deleted > 0) {
            $this->session->setMessage($this->text('Pages have been deleted'), 'success');
            return true;
        }

        return false;
    }

    /**
     * Sets titles on the page edit page
     */
    protected function setTitleEdit($page)
    {
        $title = $this->text('Add page');

        if (isset($page['page_id'])) {
            $title = $this->text('Edit page %title', array('%title' => $page['title']));
        }

        $this->setTitle($title);
    }

    /**
     * Sets breadcrumbs on the page edit page
     */
    protected function setBreadcrumbEdit()
    {
        $this->setBreadcrumb(array(
            'text' => $this->text('Dashboard'),
            'url' => $this->url('admin')));

        $this->setBreadcrumb(array(
            'text' => $this->text('Pages'),
            'url' => $this->url('admin/content/page')));
    }

    /**
     * Renders the page edit templates
     */
    protected function outputEdit()
    {
        $this->output('content/page/edit');
    }

    /**
     * Modifies page data before sending to templates
     */
    protected function setDataPage()
    {
        $default = $this->store->getDefault();
        $store_id = $this->getData('page.store_id', $default);

        $categories = $this->category->getOptionListByStore($store_id);
        $this->setdata('categories', $categories);

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
     * Returns a page
     * @param integer $page_id
     * @return array
     */
    protected function get($page_id)
    {
        if (!is_numeric($page_id)) {
            return array();
        }

        $page = $this->page->get($page_id);

        if (empty($page)) {
            $this->outputError(404);
        }

        $user = $this->user->get($page['user_id']);
        $page['author'] = $user['email'];
        $page['alias'] = $this->alias->get('page_id', $page['page_id']);

        if (empty($page['images'])) {
            return $page;
        }

        foreach ($page['images'] as &$image) {
            $image['translation'] = $this->image->getTranslations($image['file_id']);
        }

        return $page;
    }

    /**
     * Returns an array of pages
     * @param array $limit
     * @param array $query
     * @return array
     */
    protected function getPages(array $limit, array $query)
    {
        $stores = $this->store->getList();

        $options = array('limit' => $limit);
        $options += $query;
        $pages = $this->page->getList($options);

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
     * Deletes a page
     * @param array $page
     */
    protected function delete(array $page)
    {
        $this->controlAccess('page_delete');
        $this->page->delete($page['page_id']);
        $this->redirect('admin/content/page', $this->text('Page has been deleted'), 'success');
    }

    /**
     * Saves a submitted page
     * @param array $page
     * @return null
     */
    protected function submit(array $page = array())
    {
        $this->setSubmitted('page', null, false);
        $this->validate($page);

        if ($this->hasErrors('page')) {
            return;
        }

        $this->deleteImages();

        if (isset($page['page_id'])) {
            $this->controlAccess('page_edit');
            $this->page->update($page['page_id'], $this->getSubmitted());
            $this->redirect('admin/content/page', $this->text('Page has been updated'), 'success');
        }

        $this->controlAccess('page_add');
        $this->page->add($this->getSubmitted());
        $this->redirect('admin/content/page', $this->text('Page has been added'), 'success');
    }

    /**
     * Deletes an array of submitted images
     * @return int
     */
    protected function deleteImages()
    {

        $images = (array) $this->request->post('delete_image');
        $has_access = ($this->access('page_add') || $this->access('page_edit'));

        if (!$has_access || empty($images)) {
            return 0;
        }

        $deleted = 0;
        foreach ($images as $file_id) {
            $deleted += (int) $this->image->delete($file_id);
        }

        return $deleted;
    }

    /**
     * Validates a single page
     * @param array $page
     */
    protected function validate(array $page = array())
    {
        // Fix checkbox
        $this->setSubmittedBool('status');

        if (empty($page['page_id'])) {
            $this->setSubmitted('user_id', $this->uid);
        }

        // Validate fields
        $this->addValidator('title', array('length' => array('min' => 1, 'max' => 255)));
        $this->addValidator('description', array('length' => array('min' => 1)));
        $this->addValidator('meta_title', array('length' => array('max' => 255)));
        $this->addValidator('meta_description', array('length' => array('max' => 255)));
        $this->addValidator('translation', array('translation' => array()));

        $alias = $this->getSubmitted('alias');
        if (empty($alias) && isset($page['page_id'])) {
            $this->setSubmitted('alias', $this->page->createAlias($this->getSubmitted()));
        }

        $this->addValidator('alias', array(
            'regexp' => array('pattern' => '/^[A-Za-z0-9_.-]+$/'),
            'alias_unique' => array()));

        $this->addValidator('images', array('images' => array()));
        $this->setValidators($page);

        $images = $this->getValidatorResult('images');
        $this->setSubmitted('images', $images);
    }

}
