<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\frontend;

use gplcart\core\models\Page as PageModel;

/**
 * Handles incoming requests and outputs data related to blogs
 */
class Blog extends Controller
{

    /**
     * Page model instance
     * @var \gplcart\core\models\Page $page
     */
    protected $page;

    /**
     * Pager limit
     * @var array
     */
    protected $data_limit;

    /**
     * Total number of blog posts
     * @var int
     */
    protected $data_total;

    /**
     * Blog constructor.
     * @param PageModel $page
     */
    public function __construct(PageModel $page)
    {
        parent::__construct();

        $this->page = $page;
    }

    /**
     * Page callback
     * Displays the blog page
     */
    public function listBlog()
    {
        $this->setTitleListBlog();
        $this->setBreadcrumbListBlog();
        $this->setTotalListBlog();
        $this->setPagerListBlog();

        $this->setData('pages', $this->getPagesBlog());
        $this->outputListBlog();
    }

    /**
     * Sets a total number of posts found
     * @return int
     */
    protected function setTotalListBlog()
    {
        $conditions = $this->query_filter;

        $conditions['status'] = 1;
        $conditions['count'] = true;
        $conditions['blog_post'] = 1;
        $conditions['store_id'] = $this->store_id;

        return $this->data_total = (int) $this->page->getList($conditions);
    }

    /**
     * Returns an array of blog posts
     * @return array
     */
    protected function getPagesBlog()
    {
        $conditions = $this->query_filter;

        $conditions['status'] = 1;
        $conditions['blog_post'] = 1;
        $conditions['limit'] = $this->data_limit;
        $conditions['store_id'] = $this->store_id;

        $list = (array) $this->page->getList($conditions);
        $this->preparePagesBlog($list);
        return $list;
    }

    /**
     * Prepare an array of pages
     * @param array $list
     */
    protected function preparePagesBlog(array &$list)
    {
        foreach ($list as &$item) {

            list($teaser, $body) = $this->explodeText($item['description']);

            if ($body !== '') {
                $item['teaser'] = strip_tags($teaser);
            }

            $this->setItemEntityUrl($item, array('entity' => 'page'));
        }
    }

    /**
     * Sets pager
     * @return array
     */
    protected function setPagerListBlog()
    {
        $pager = array(
            'total' => $this->data_total,
            'query' => $this->query_filter,
            'limit' => $this->configTheme('blog_limit', 20)
        );

        return $this->data_limit = $this->setPager($pager);
    }

    /**
     * Sets titles on the blog page
     */
    protected function setTitleListBlog()
    {
        $this->setTitle($this->text('Blog'));
    }

    /**
     * Sets bread crumbs on the blog page
     */
    protected function setBreadcrumbListBlog()
    {
        $breadcrumb = array(
            'url' => $this->url('/'),
            'text' => $this->text('Home')
        );

        $this->setBreadcrumb($breadcrumb);
    }

    /**
     * Renders and outputs the blog page templates
     */
    protected function outputListBlog()
    {
        $this->output('blog/list');
    }

}
