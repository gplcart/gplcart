<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core;

use PDO;
use core\Hook;
use core\Config;
use core\Handler;
use core\classes\Url;
use core\classes\Cache;
use core\classes\Request;

/**
 * Routes incoming requests
 */
class Route
{

    /**
     * Url class instance
     * @var \core\classes\Url $url
     */
    protected $url;

    /**
     * Request class instance
     * @var \core\classes\Request $request
     */
    protected $request;

    /**
     * Hook class instance
     * @var \core\Hook $hook
     */
    protected $hook;

    /**
     * Config class instance
     * @var \core\Config $config
     */
    protected $config;

    /**
     * The current path
     * @var string
     */
    protected $path;

    /**
     * Current language code from the url
     * @var string
     */
    protected $langcode = '';

    /**
     * Current route
     * @var array
     */
    protected $route;

    /**
     * PDO instance
     * @var \core\classes\Database $db
     */
    protected $db;

    /**
     * Constructor
     * @param Url $url
     * @param Request $request
     * @param Config $config
     * @param Hook $hook
     */
    public function __construct(Url $url, Request $request, Config $config,
            Hook $hook)
    {

        $this->url = $url;
        $this->hook = $hook;
        $this->config = $config;
        $this->request = $request;
        $this->db = $config->getDb();
        $this->path = $this->url->path();

        $this->setLangcode();
    }

    /**
     * Returns an array of all available routes
     * @return array
     */
    public function getList()
    {
        $routes = &Cache::memory('routes');

        if (isset($routes)) {
            return $routes;
        }

        $routes = array();

        $routes['install'] = array(
            'handlers' => array(
                'controller' => array('core\\controllers\\Install', 'install')
            )
        );

        $routes['ajax'] = array(
            'handlers' => array(
                'controller' => array('core\\controllers\\Ajax', 'ajax')
            )
        );

        $routes[''] = array(
            'handlers' => array(
                'controller' => array('core\\controllers\\Front', 'front')
            )
        );

        $routes['action'] = array(
            'handlers' => array(
                'controller' => array('core\\controllers\\Action', 'action')
            )
        );

        $routes['cron'] = array(
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Cron', 'cron')
            )
        );

        $routes['product/(\d+)'] = array(
            'alias' => array(0, 1),
            'handlers' => array(
                'controller' => array('core\\controllers\\Product', 'product')
            )
        );

        $routes['review/add/(\d+)'] = array(
            'handlers' => array(
                'controller' => array('core\\controllers\\Review', 'edit')
            )
        );

        $routes['review/edit/(\d+)/(\d+)'] = array(
            'handlers' => array(
                'controller' => array('core\\controllers\\Review', 'edit')
            )
        );

        $routes['files/image/cache/.*'] = array(
            'handlers' => array(
                'controller' => array('core\\controllers\\Image', 'cache')
            )
        );

        $routes['page/(\d+)'] = array(
            'alias' => array(0, 1),
            'handlers' => array(
                'controller' => array('core\\controllers\\Page', 'page')
            )
        );

        $routes['login'] = array(
            'handlers' => array(
                'controller' => array('core\\controllers\\Account', 'login')
            )
        );

        $routes['logout'] = array(
            'handlers' => array(
                'controller' => array('core\\controllers\\Account', 'logout')
            )
        );

        $routes['register'] = array(
            'handlers' => array(
                'controller' => array('core\\controllers\\Account', 'register')
            )
        );

        $routes['forgot'] = array(
            'handlers' => array(
                'controller' => array('core\\controllers\\Account', 'forgot')
            )
        );

        $routes['category/(\d+)'] = array(
            'alias' => array(0, 1),
            'handlers' => array(
                'controller' => array('core\\controllers\\Category', 'category')
            )
        );

        $routes['checkout'] = array(
            'handlers' => array(
                'controller' => array('core\\controllers\\Checkout', 'checkout')
            )
        );

        $routes['checkout/complete/(\d+)'] = array(
            'handlers' => array(
                'controller' => array('core\\controllers\\Checkout', 'complete')
            )
        );

        $routes['account/(\d+)'] = array(
            'handlers' => array(
                'controller' => array('core\\controllers\\Account', 'account')
            )
        );

        $routes['account/(\d+)/edit'] = array(
            'handlers' => array(
                'controller' => array('core\\controllers\\Account', 'edit')
            )
        );

        $routes['account/(\d+)/address'] = array(
            'handlers' => array(
                'controller' => array('core\\controllers\\Account', 'addresses')
            )
        );

        $routes['account/(\d+)/address/add'] = array(
            'handlers' => array(
                'controller' => array('core\\controllers\\Account', 'editAddress')
            )
        );

        $routes['wishlist'] = array(
            'handlers' => array(
                'controller' => array('core\\controllers\\Wishlist', 'wishlist')
            )
        );

        $routes['compare'] = array(
            'handlers' => array(
                'controller' => array('core\\controllers\\Compare', 'select')
            )
        );

        $routes['compare/([^/]+)'] = array(
            'handlers' => array(
                'controller' => array('core\\controllers\\Compare', 'compare')
            )
        );

        $routes['search'] = array(
            'handlers' => array(
                'controller' => array('core\\controllers\\Search', 'search')
            )
        );

        // Admin area
        $routes['admin'] = array(
            'access' => 'dashboard',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Dashboard', 'dashboard')
            )
        );

        $routes['admin/content/product'] = array(
            'access' => 'product',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Product', 'products')
            )
        );

        $routes['admin/content/product/add'] = array(
            'access' => 'product_add',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Product', 'edit')
            )
        );

        $routes['admin/content/product/edit/(\d+)'] = array(
            'access' => 'product_edit',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Product', 'edit')
            )
        );

        $routes['admin/content/product/class'] = array(
            'access' => 'product_class',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\ProductClass', 'classes')
            )
        );

        $routes['admin/content/product/class/edit/(\d+)'] = array(
            'access' => 'product_class_edit',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\ProductClass', 'edit')
            )
        );

        $routes['admin/content/product/class/add'] = array(
            'access' => 'product_class_add',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\ProductClass', 'edit')
            )
        );

        $routes['admin/content/product/class/field/(\d+)'] = array(
            'access' => 'product_class_edit',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\ProductClass', 'fields')
            )
        );

        $routes['admin/content/product/class/field/(\d+)/add'] = array(
            'access' => 'product_class_edit',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\ProductClass', 'addField')
            )
        );

        $routes['admin/tool/import'] = array(
            'access' => 'import',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Import', 'operations')
            )
        );

        $routes['admin/tool/import/(\w+)'] = array(
            'access' => 'import',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Import', 'import')
            )
        );

        $routes['admin/tool/export'] = array(
            'access' => 'export',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Export', 'operations')
            )
        );

        $routes['admin/tool/export/(\w+)'] = array(
            'access' => 'export',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Export', 'export')
            )
        );

        $routes['admin/tool/search'] = array(
            'access' => 'search_edit',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Search', 'index')
            )
        );

        $routes['admin/tool/demo'] = array(
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Demo', 'demo')
            )
        );

        $routes['admin/sale/order'] = array(
            'access' => 'order',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Order', 'orders')
            )
        );

        $routes['admin/sale/order/(\d+)'] = array(
            'access' => 'order',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Order', 'order')
            )
        );

        $routes['admin/sale/price'] = array(
            'access' => 'price_rule',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\PriceRule', 'rules')
            )
        );

        $routes['admin/sale/price/add'] = array(
            'access' => 'price_rule_add',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\PriceRule', 'edit')
            )
        );

        $routes['admin/sale/price/edit/(\d+)'] = array(
            'access' => 'price_rule_edit',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\PriceRule', 'edit')
            )
        );

        $routes['admin/search'] = array(
            'access' => 'search',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Search', 'search')
            )
        );

        $routes['admin/content/page'] = array(
            'access' => 'page',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Page', 'pages')
            )
        );

        $routes['admin/content/page/add'] = array(
            'access' => 'page_add',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Page', 'edit')
            )
        );

        $routes['admin/content/page/edit/(\d+)'] = array(
            'access' => 'page_edit',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Page', 'edit')
            )
        );

        $routes['admin/content/review'] = array(
            'access' => 'review',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Review', 'reviews')
            )
        );

        $routes['admin/content/review/add'] = array(
            'access' => 'review_add',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Review', 'edit')
            )
        );

        $routes['admin/content/review/edit/(\d+)'] = array(
            'access' => 'review_edit',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Review', 'edit')
            )
        );

        $routes['admin/content/file'] = array(
            'access' => 'file',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\File', 'files')
            )
        );

        $routes['admin/content/category/group'] = array(
            'access' => 'category_group',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\CategoryGroup', 'groups')
            )
        );

        $routes['admin/content/category/group/edit/(\d+)'] = array(
            'access' => 'category_group_edit',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\CategoryGroup', 'edit')
            )
        );

        $routes['admin/content/category/group/add'] = array(
            'access' => 'category_group_add',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\CategoryGroup', 'edit')
            )
        );

        $routes['admin/content/category/(\d+)'] = array(
            'access' => 'category',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Category', 'categories')
            )
        );

        $routes['admin/content/category/add/(\d+)'] = array(
            'access' => 'category_add',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Category', 'edit')
            )
        );

        $routes['admin/content/category/edit/(\d+)/(\d+)'] = array(
            'access' => 'category_edit',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Category', 'edit')
            )
        );

        $routes['admin/user'] = array(
            'access' => 'user',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\User', 'users')
            )
        );

        $routes['admin/user/role'] = array(
            'access' => 'user_role',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\UserRole', 'roles')
            )
        );

        $routes['admin/user/role/add'] = array(
            'access' => 'user_role_add',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\UserRole', 'edit')
            )
        );

        $routes['admin/user/role/edit/(\d+)'] = array(
            'access' => 'user_role_edit',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\UserRole', 'edit')
            )
        );

        $routes['admin/content/field'] = array(
            'access' => 'field',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Field', 'fields')
            )
        );

        $routes['admin/content/field/value/(\d+)'] = array(
            'access' => 'field_value',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\FieldValue', 'values')
            )
        );

        $routes['admin/content/field/value/(\d+)/(\w+)'] = array(
            'access' => 'field_value',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\FieldValue', 'edit')
            )
        );

        $routes['admin/content/field/edit/(\d+)'] = array(
            'access' => 'field_edit',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Field', 'edit')
            )
        );

        $routes['admin/content/field/add'] = array(
            'access' => 'field_add',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Field', 'edit')
            )
        );

        $routes['admin/content/alias'] = array(
            'access' => 'alias',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Alias', 'aliases')
            )
        );

        $routes['admin/module'] = array(
            'access' => 'module',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Module', 'modules')
            )
        );

        $routes['admin/module/upload'] = array(
            'access' => 'module_upload',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Module', 'upload')
            )
        );

        $routes['admin/module/marketplace'] = array(
            'access' => 'marketplace',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Module', 'marketplace')
            )
        );

        $routes['admin/settings/common'] = array(
            'access' => 'settings',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Settings', 'settings')
            )
        );

        $routes['admin/settings/language'] = array(
            'access' => 'language',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Language', 'languages')
            )
        );

        $routes['admin/settings/language/edit/(\w+)'] = array(
            'access' => 'language_edit',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Language', 'edit')
            )
        );

        $routes['admin/settings/language/add'] = array(
            'access' => 'language_add',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Language', 'edit')
            )
        );

        $routes['admin/settings/imagestyle'] = array(
            'access' => 'image_style',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Image', 'styles')
            )
        );

        $routes['admin/settings/search'] = array(
            'access' => 'search_edit',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Search', 'settings')
            )
        );

        $routes['admin/settings/imagestyle/edit/(\d+)'] = array(
            'access' => 'image_style_edit',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Image', 'edit')
            )
        );

        $routes['admin/settings/imagestyle/add'] = array(
            'access' => 'image_style_add',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Image', 'edit')
            )
        );

        $routes['admin/settings/currency'] = array(
            'access' => 'currency',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Currency', 'currencies')
            )
        );

        $routes['admin/settings/currency/edit/(\w+)'] = array(
            'access' => 'currency_edit',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Currency', 'edit')
            )
        );

        $routes['admin/settings/currency/add'] = array(
            'access' => 'currency_add',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Currency', 'edit')
            )
        );

        $routes['admin/settings/country'] = array(
            'access' => 'country',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Country', 'countries')
            )
        );

        $routes['admin/settings/country/edit/(\w+)'] = array(
            'access' => 'country_edit',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Country', 'edit')
            )
        );

        $routes['admin/settings/country/add'] = array(
            'access' => 'country_add',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Country', 'edit')
            )
        );

        $routes['admin/settings/country/format/(\w+)'] = array(
            'access' => 'country_format',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Country', 'format')
            )
        );

        $routes['admin/settings/states/(\w+)'] = array(
            'access' => 'state',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\State', 'states')
            )
        );

        $routes['admin/settings/state/add/(\w+)'] = array(
            'access' => 'state_add',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\State', 'edit')
            )
        );

        $routes['admin/settings/state/edit/(\w+)/(\d+)'] = array(
            'access' => 'state_edit',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\State', 'edit')
            )
        );

        $routes['admin/settings/cities/(\w+)/(\d+)'] = array(
            'access' => 'city',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\City', 'cities')
            )
        );

        $routes['admin/settings/city/add/(\w+)/(\d+)'] = array(
            'access' => 'city_add',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\City', 'edit')
            )
        );

        $routes['admin/settings/city/edit/(\w+)/(\d+)/(\d+)'] = array(
            'access' => 'city_edit',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\City', 'edit')
            )
        );

        $routes['admin/settings/store'] = array(
            'access' => 'store',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Store', 'stores')
            )
        );

        $routes['admin/settings/store/(\w+)'] = array(
            'access' => 'store_edit',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Store', 'edit')
            )
        );

        $routes['admin/report/system'] = array(
            'access' => 'report_system',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Report', 'system')
            )
        );

        $routes['admin/report/status'] = array(
            'access' => 'report_status',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Report', 'status')
            )
        );

        $routes['admin/report/ga'] = array(
            'access' => 'report_ga',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Report', 'ga')
            )
        );

        $routes['admin/help'] = array(
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Help', 'help')
            )
        );

        $routes['admin/help/(\w+)'] = array(
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Help', 'help')
            )
        );

        $this->hook->fire('route', $routes);
        return $routes;
    }

    /**
     * Processes the current route
     */
    public function process()
    {
        // Try to find an alias
        if (!empty($this->db)) {
            $this->findAliasByPath();
            $this->findAliasByPattern();
        }

        // No alias found, call the route controller
        $this->callController();

        // No route controller found, show 404 error message
        $route = array(
            'handlers' => array('controller' => array('core\\controllers\\Error', 'error404'))
        );

        Handler::call($route, null, 'controller');
    }

    /**
     * Returns a language from the current URL
     * @return string
     */
    public function getLangcode()
    {
        return $this->langcode;
    }

    /**
     * Returns the current route
     * @return array
     */
    public function getCurrent()
    {
        return $this->route;
    }

    /**
     * Sets the current language
     */
    protected function setLangcode()
    {
        $default_langcode = $this->config->get('language', '');
        $languages = $this->config->get('languages', array());

        $segments = $this->url->segments();

        if (!empty($languages[$segments[0]]['status'])) {
            $this->langcode = $segments[0];
        } else {
            $this->langcode = $default_langcode;
        }

        if ($this->langcode && ($this->langcode === $default_langcode)) {
            // TODO: redirect to url without language code
        }

        if ($this->langcode && ($this->langcode !== $default_langcode)) {
            $this->request->setBaseSuffix($this->langcode);
        }
    }

    /**
     * Finds an alias by the path
     * @return null
     */
    protected function findAliasByPath()
    {
        $sth = $this->db->prepare('SELECT id_key, id_value FROM alias WHERE alias=:alias');
        $sth->execute(array(':alias' => $this->path()));
        $result = $sth->fetch(PDO::FETCH_ASSOC);

        if (empty($result)) {
            return;
        }

        $key = str_replace('_id', '', $result['id_key']);

        foreach ($this->getList() as $pattern => $route) {
            if (!isset($route['alias'][0])) {
                continue;
            }

            $pattern_segments = explode('/', $pattern);

            if ($pattern_segments[$route['alias'][0]] === $key) {
                $route['arguments'] = array($result['id_value']);
                $this->route = $route + array('pattern' => $pattern);
                Handler::call($route, null, 'controller', $route['arguments']);
                exit;
            }
        }
    }

    /**
     * Finds an alias by the route pattern
     */
    protected function findAliasByPattern()
    {
        $path_segments = $this->url->segments();

        foreach ($this->getList() as $pattern => $route) {
            if (empty($route['alias'])) {
                continue;
            }

            if (!isset($path_segments[$route['alias'][0]])) {
                continue;
            }

            if (!isset($path_segments[$route['alias'][1]])) {
                continue;
            }

            $pattern_segments = explode('/', $pattern);

            if (!isset($pattern_segments[$route['alias'][0]])) {
                continue;
            }

            if ($pattern_segments[$route['alias'][0]] !== $path_segments[$route['alias'][0]]) {
                continue;
            }

            $sth = $this->db->prepare('SELECT alias FROM alias WHERE id_key=:id_key AND id_value=:id_value');
            $sth->execute(array(
                ':id_key' => $path_segments[$route['alias'][0]] . '_id',
                ':id_value' => $path_segments[$route['alias'][1]]));

            $alias = $sth->fetchColumn();

            if (!empty($alias)) {
                $this->url->redirect($alias);
            }
        }
    }

    /**
     * Calls an appropriate controller for the current URL
     * @param array $routes
     * @return boolean|null
     */
    protected function callController()
    {
        foreach ($this->getList() as $pattern => $route) {
            $arguments = $this->parsePattern($pattern);

            if ($arguments !== false) {
                $route['arguments'] = $arguments;
                $this->route = $route + array('pattern' => $pattern);
                Handler::call($route, null, 'controller', $arguments);
            }
        }

        return false;
    }

    /**
     * Parses a route patter and extracts arguments from it
     * @param string $pattern
     * @return boolean|array
     */
    protected function parsePattern($pattern)
    {
        $pattern = '/^' . str_replace('/', '\/', $pattern) . '$/';
        $url = $this->path();

        if (preg_match($pattern, $url, $params)) {
            array_shift($params);
            return array_values($params);
        }

        return false;
    }

    /**
     * Returns the current path
     * @return string
     */
    protected function path()
    {
        return $this->path;
    }

}
