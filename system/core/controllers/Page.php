<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers;

use core\Controller;
use core\models\Page as ModelsPage;

/**
 * Handles incoming requests and outputs data related to pages
 */
class Page extends Controller
{

    /**
     * Page model class instance
     * @var \core\models\Page $page
     */
    protected $page;

    /**
     * Constructor
     * @param ModelsPage $page
     */
    public function __construct(ModelsPage $page)
    {
        parent::__construct();

        $this->page = $page;
    }

    /**
     * Displays a page
     * @param type $page_id
     */
    public function page($page_id)
    {
        $page = $this->page->getCached($page_id);

        $langcode = $this->data['lang'];

        if (isset($page['description'][$langcode])) {
            $page['title'] = $page['description'][$langcode]['title'];
            $page['description'] = $page['description'][$langcode]['description'];

            $this->setMetaTitle($page['title']);
            $this->setMetaDescription($page['title']);
        }

        $this->data['page'] = $page;

        $this->hook->fire('page.view', $page);
    }

}
