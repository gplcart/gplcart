<?php

/**
 * @package GPL Cart core
 * @version $Id$
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace modules\backend;

use core\Route;
use core\classes\Url;
use core\classes\Document;
use core\models\User;
use core\models\Store;
use core\models\Search;

class Backend
{

    /**
     * User model instance
     * @var \core\models\User $user
     */
    protected $user;

    /**
     * Store model instance
     * @var \core\models\Store $store
     */
    protected $store;

    /**
     * Search model instance
     * @var \core\models\Search $search
     */
    protected $search;

    /**
     * Url class instance
     * @var \core\classes\Url $url
     */
    protected $url;

    /**
     * Document class instance
     * @var \core\classes\Document $document
     */
    protected $document;

    /**
     * Route class instance
     * @var \core\Route $route
     */
    protected $route;

    /**
     * Constructor
     * @param User $user
     * @param Store $store
     * @param Url $url
     * @param Search $search
     * @param Document $document
     * @param Route $route
     */
    public function __construct(User $user, Store $store, Url $url, Search $search, Document $document, Route $route)
    {
        $this->url = $url;
        $this->user = $user;
        $this->store = $store;
        $this->search = $search;
        $this->document = $document;
        $this->route = $route;

        if ($this->url->isBackend()) {
            $this->addMeta();
            $this->addCss();
            $this->addJs();
        }
    }

    /**
     * Returns module info
     * @return array
     */
    public function info()
    {
        return array(
            'name' => 'Backend theme',
            'description' => 'Backend theme',
            'author' => 'Iurii Makukh',
            'core' => '1.0',
            'type' => 'theme',
            'settings' => array()
        );
    }

    /**
     * Implements hook data
     * @param array $data
     */
    public function hookData(&$data)
    {
        if ($this->url->isBackend()) {
            $data['store_list'] = $this->store->getList();
            $data['search_handlers'] = $this->search->getHandlers();
        }
    }

    protected function addMeta()
    {
        $this->document->meta(array('charset' => 'utf-8'));
        $this->document->meta(array('http-equiv' => 'X-UA-Compatible', 'content' => 'IE=edge'));
        $this->document->meta(array('name' => 'viewport', 'content' => 'width=device-width, initial-scale=1'));
        $this->document->meta(array('name' => 'author', 'content' => 'GPL Cart'));
    }

    protected function addCss()
    {
        $this->document->css('files/assets/bootstrap/bootstrap/css/bootstrap.min.css');
        $this->document->css('files/assets/font-awesome/css/font-awesome.min.css');
        $this->document->css('files/assets/jquery/ui/jquery-ui.min.css');
        $this->document->css('files/assets/jquery/summernote/summernote.css');
        $this->document->css('files/assets/bootstrap/select/dist/css/bootstrap-select.min.css');
        $this->document->css('files/assets/bootstrap/colorpicker/dist/css/bootstrap-colorpicker.min.css');
        $this->document->css('system/modules/backend/css/style.css');
    }

    protected function addJs()
    {
        $this->document->js('system/modules/backend/js/common.js', 'top');
        $this->document->js('files/assets/jquery/ui/jquery-ui.min.js', 'top');
        $this->document->js('files/assets/bootstrap/bootstrap/js/bootstrap.min.js', 'top');

        $segments = $this->url->segments();

        if ($this->url->isDashboard()) {
            $this->document->js('system/modules/backend/js/dashboard.js', 'bottom');
        }

        if (isset($segments[2])) {
            $this->document->js("system/modules/backend/js/{$segments[2]}.js", 'bottom');
        }

        $this->document->js('files/assets/bootstrap/growl/jquery.bootstrap-growl.min.js', 'bottom');
        $this->document->js('files/assets/jquery/fileupload/jquery.fileupload.js', 'bottom');
        $this->document->js('files/assets/bootstrap/select/dist/js/bootstrap-select.min.js', 'bottom');

        $langcode = $this->route->getLangcode();
        $lang_region = (strpos($langcode, '_') === false) ? $langcode . '-' . strtoupper($langcode) : $langcode;

        $this->document->js('files/assets/jquery/summernote/summernote.min.js', 'bottom');
        $this->document->js("files/assets/jquery/summernote/lang/summernote-$lang_region.js", 'bottom');

        $this->document->js('files/assets/jquery/cookie/js.cookie.js', 'bottom');
        $this->document->js('files/assets/bootstrap/colorpicker/dist/js/bootstrap-colorpicker.min.js', 'bottom');
        $this->document->js('files/assets/jquery/countdown/countdown.js', 'bottom');
    }
}
