<?php

/**
 * @package GPL Cart core
 * @version $Id$
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @author Jason Grimes https://github.com/jasongrimes/php-paginator
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace core\classes;

class Pager
{

    /**
     * Total number of items
     * @var integer
     */
    protected $total;

    /**
     * Number of pages
     * @var integer
     */
    protected $pages;

    /**
     * Max items per page
     * @var integer
     */
    protected $limit;

    /**
     * Current page number
     * @var integer
     */
    protected $current;

    /**
     * URL pattern to extract page id
     * @var string
     */
    protected $pattern = '';

    /**
     * Pages limit
     * @var integer
     */
    protected $limit_pages = 10;

    /**
     * "Previous" label text
     * @var string
     */
    protected $previous_text = 'Previous';

    /**
     * "Next" label text
     * @var string
     */
    protected $next_text = 'Next';

    /**
     * Updates the current number of pages
     * @return \core\classes\Pager
     */
    protected function updateNumPages()
    {
        $this->pages = ($this->limit == 0 ? 0 : (int) ceil($this->total / $this->limit));
        return $this;
    }

    /**
     * Sets max number of pages to show in the pager links
     * @param int $limit_pages
     */
    public function setMaxPagesToShow($limit_pages)
    {
        $this->limit_pages = $limit_pages;
        return $this;
    }

    /**
     * Sets total number of items
     * @param integer $num
     * @return \core\classes\Pager
     */
    public function setTotal($num)
    {
        $this->total = $num;
        $this->updateNumPages();
        return $this;
    }

    /**
     * Sets items per page
     * @param integer $num
     * @return \core\classes\Pager
     */
    public function setPerPage($num)
    {
        $this->limit = $num;
        $this->updateNumPages();
        return $this;
    }

    /**
     * Sets the current page
     * @param integer $current
     * @return \core\classes\Pager
     */
    public function setPage($current)
    {
        $this->current = $current;
        $this->updateNumPages();
        return $this;
    }

    /**
     * Sets pager URL pattern
     * @param string $pattern
     */
    public function setUrlPattern($pattern)
    {
        $this->pattern = $pattern;
        return $this;
    }

    /**
     * Returns max number of pages to show in the pager links
     * @return int
     */
    public function getMaxPagesToShow()
    {
        return $this->limit_pages;
    }

    /**
     * Returns the current page number
     * @return int
     */
    public function getCurrentPage()
    {
        return $this->current;
    }

    /**
     * Returns number of items per page
     * @return int
     */
    public function getItemsPerPage()
    {
        return $this->limit;
    }

    /**
     * Returns total number of items
     * @return int
     */
    public function getTotalItems()
    {
        return $this->total;
    }

    /**
     * Returns number of pages
     * @return int
     */
    public function getNumPages()
    {
        return $this->pages;
    }

    /**
     * Returns a URL pattern
     * @return string
     */
    public function getUrlPattern()
    {
        return $this->pattern;
    }

    /**
     * Parses pager number placeholder
     * @param int $num
     * @return string
     */
    public function getPageUrl($num)
    {
        return str_replace('%num', $num, $this->pattern);
    }

    /**
     * Returns the next page number
     * @return integer
     */
    public function getNextPage()
    {
        if ($this->current < $this->pages) {
            return $this->current + 1;
        }

        return 0;
    }

    /**
     * Returns the previous page number
     * @return integer
     */
    public function getPrevPage()
    {
        if ($this->current > 1) {
            return $this->current - 1;
        }

        return 0;
    }

    /**
     * Returns the next pager URL
     * @return string
     */
    public function getNextUrl()
    {
        if ($this->getNextPage() === 0) {
            return '';
        }

        return $this->getPageUrl($this->getNextPage());
    }

    /**
     * Returns the previous pager URL
     * @return string
     */
    public function getPrevUrl()
    {
        if ($this->getPrevPage() === 0) {
            return '';
        }

        return $this->getPageUrl($this->getPrevPage());
    }

    /**
     * Get an array of paginated page data
     * @return array
     */
    public function getPages()
    {
        $pages = array();

        if ($this->pages <= 1) {
            return array();
        }

        if ($this->pages <= $this->limit_pages) {
            for ($i = 1; $i <= $this->pages; $i++) {
                $pages[] = $this->createPage($i, $i == $this->current);
            }
        } else {

            // Determine the sliding range, centered around the current page.
            $num_adjacents = (int) floor(($this->limit_pages - 3) / 2);

            if ($this->current + $num_adjacents > $this->pages) {
                $sliding_start = $this->pages - $this->limit_pages + 2;
            } else {
                $sliding_start = $this->current - $num_adjacents;
            }

            if ($sliding_start < 2) {
                $sliding_start = 2;
            }

            $sliding_end = $sliding_start + $this->limit_pages - 3;

            if ($sliding_end >= $this->pages) {
                $sliding_end = $this->pages - 1;
            }

            // Build the list of pages.
            $pages[] = $this->createPage(1, $this->current == 1);

            if ($sliding_start > 2) {
                $pages[] = $this->createPageEllipsis();
            }

            for ($i = $sliding_start; $i <= $sliding_end; $i++) {
                $pages[] = $this->createPage($i, $i == $this->current);
            }

            if ($sliding_end < $this->pages - 1) {
                $pages[] = $this->createPageEllipsis();
            }

            $pages[] = $this->createPage($this->pages, $this->current == $this->pages);
        }

        return $pages;
    }

    /**
     * Creates a page data structure
     * @param int $num
     * @param bool $is_current
     * @return array
     */
    protected function createPage($num, $is_current = false)
    {
        return array(
            'num' => $num,
            'url' => $this->getPageUrl($num),
            'is_current' => $is_current,
        );
    }

    /**
     * Created pager ellipsis
     * @return array
     */
    protected function createPageEllipsis()
    {
        return array(
            'num' => '...',
            'url' => '',
            'is_current' => false,
        );
    }

    /**
     * Render an HTML pagination control.
     * @return string
     */
    public function render()
    {
        if ($this->pages <= 1) {
            return '';
        }

        $html = '<ul class="pagination">';
        if ($this->getPrevUrl() !== '') {
            $html .= '<li><a rel="prev" href="' . $this->getPrevUrl() . '">&laquo; ' . $this->previous_text . '</a></li>';
        }

        foreach ($this->getPages() as $page) {
            if ($page['url']) {
                $html .= '<li' . ($page['is_current'] ? ' class="active"' : '') . '><a href="' . $page['url'] . '">' . $page['num'] . '</a></li>';
            } else {
                $html .= '<li class="disabled"><span>' . $page['num'] . '</span></li>';
            }
        }

        if ($this->getNextUrl() !== '') {
            $html .= '<li><a rel="next" href="' . $this->getNextUrl() . '">' . $this->next_text . ' &raquo;</a></li>';
        }
        $html .= '</ul>';

        return $html;
    }

    /**
     * Returns the first item for the current page
     * @return integer
     */
    public function getCurrentPageFirstItem()
    {
        $first = ($this->current - 1) * $this->limit;

        if ($first > $this->total) {
            return 0;
        }

        return $first;
    }

    /**
     * Returns the last item for the current page
     * @return integer
     */
    public function getCurrentPageLastItem()
    {
        $first = $this->getCurrentPageFirstItem();

        if (empty($first)) {
            return 0;
        }

        $last = $first + $this->limit - 1;

        if ($last > $this->total) {
            return $this->total;
        }

        return $last;
    }

    /**
     * Returns a string with limits to be used in SQL LIMIT
     * @return string
     */
    public function getLimit()
    {
        $start = $this->getCurrentPageFirstItem();

        if ($start < 0) {
            $start = 0;
        }

        return array($start, $this->getItemsPerPage());
    }

    /**
     * Sets "Previous" text
     * @param string $text
     * @return \core\classes\Pager
     */
    public function setPreviousText($text)
    {
        $this->previous_text = $text;
        return $this;
    }

    /**
     * Sets "Next" text
     * @param string $text
     * @return \core\classes\Pager
     */
    public function setNextText($text)
    {
        $this->next_text = $text;
        return $this;
    }
}
