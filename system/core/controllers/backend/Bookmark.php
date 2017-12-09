<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\backend;

use gplcart\core\models\Bookmark as BookmarkModel;
use gplcart\core\controllers\backend\Controller as BackendController;

/**
 * Handles incoming requests and outputs data related to bookmarks
 */
class Bookmark extends BackendController
{

    /**
     * Bookmark model instance
     * @var \gplcart\core\models\Bookmark $bookmark
     */
    protected $bookmark;

    /**
     * Pager limit
     * @var array
     */
    protected $data_limit;

    /**
     * Bookmark title
     * @var string
     */
    protected $data_title;

    /**
     * Bookmark path
     * @var string
     */
    protected $data_path;

    /**
     * Redirect path
     * @var string
     */
    protected $data_target;

    /**
     * @param BookmarkModel $bookmark
     */
    public function __construct(BookmarkModel $bookmark)
    {
        parent::__construct();

        $this->bookmark = $bookmark;
    }

    /**
     * Page callback to add a bookmark
     */
    public function addBookmark()
    {
        $this->setBookmark();
        $this->controlAccess('bookmark_add');

        $options = array(
            'user_id' => $this->uid,
            'title' => $this->data_title
        );

        $this->bookmark->set($this->data_path, $options);
        $this->redirect($this->data_path);
    }

    /**
     * Page callback to delete a bookmark
     */
    public function deleteBookmark()
    {
        $this->setBookmark();
        $this->controlAccess('bookmark_delete');

        $this->bookmark->delete($this->data_path);
        $this->redirect($this->data_target);
    }

    /**
     * Sets bookmark data
     */
    protected function setBookmark()
    {
        $this->data_path = $this->getQuery('path');
        $this->data_title = $this->getQuery('title');
        $this->data_target = $this->getQuery('target', $this->data_path);

        if (empty($this->data_path)) {
            $this->outputHttpStatus(403);
        }
    }

    /**
     * Displays the bookmark overview page
     */
    public function listBookmark()
    {
        $this->setTitleListBookmark();
        $this->setBreadcrumbListBookmark();
        $this->setFilterListBookmark();
        $this->setPagerlListBookmark();

        $this->setData('bookmarks', $this->getListBookmark());
        $this->outputListBookmark();
    }

    /**
     * Sets the current filter parameters
     */
    protected function setFilterListBookmark()
    {
        $this->setFilter(array('created', 'title'));
    }

    /**
     * Sets pager
     * @return array
     */
    protected function setPagerlListBookmark()
    {
        $options = $this->query_filter;
        $options['count'] = true;
        $total = (int) $this->bookmark->getList($options);

        $pager = array(
            'total' => $total,
            'query' => $this->query_filter
        );

        return $this->data_limit = $this->setPager($pager);
    }

    /**
     * Returns an array of bookmarks
     * @return array
     */
    protected function getListBookmark()
    {
        $conditions = $this->query_filter;
        $conditions['user_id'] = $this->uid;
        $conditions['limit'] = $this->data_limit;

        return (array) $this->bookmark->getList($conditions);
    }

    /**
     * Sets titles on the bookmark overview page
     */
    protected function setTitleListBookmark()
    {
        $this->setTitle($this->text('Bookmarks'));
    }

    /**
     * Sets breadcrumbs on the bookmark overview page
     */
    protected function setBreadcrumbListBookmark()
    {
        $breadcrumb = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')
        );

        $this->setBreadcrumb($breadcrumb);
    }

    /**
     * Render and output the bookmark overview page
     */
    protected function outputListBookmark()
    {
        $this->output('bookmark/list');
    }

}
