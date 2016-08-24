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
                'controller' => array('core\\controllers\\admin\\Cron', 'executeCron')
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

        $routes['admin/content'] = array(
            'menu' => array('admin' => 'Content'),
            'handlers' => array(
                'controller' => array('core\\Controller', 'adminSections')
            )
        );

        $routes['admin/content/product'] = array(
            'access' => 'product',
            'menu' => array('admin' => 'Products'),
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

        $routes['admin/content/product-class'] = array(
            'access' => 'product_class',
            'menu' => array('admin' => 'Product classes'),
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\ProductClass', 'classes')
            )
        );

        $routes['admin/content/product-class/edit/(\d+)'] = array(
            'access' => 'product_class_edit',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\ProductClass', 'edit')
            )
        );

        $routes['admin/content/product-class/add'] = array(
            'access' => 'product_class_add',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\ProductClass', 'edit')
            )
        );

        $routes['admin/content/product-class/field/(\d+)'] = array(
            'access' => 'product_class_edit',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\ProductClass', 'fields')
            )
        );

        $routes['admin/content/product-class/field/(\d+)/add'] = array(
            'access' => 'product_class_edit',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\ProductClass', 'addField')
            )
        );

        $routes['admin/tool'] = array(
            'menu' => array('admin' => 'Tools'),
            'handlers' => array(
                'controller' => array('core\\Controller', 'adminSections')
            )
        );

        $routes['admin/tool/import'] = array(
            'access' => 'import',
            'menu' => array('admin' => 'Import'),
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Import', 'listImport')
            )
        );

        $routes['admin/tool/import/(\w+)'] = array(
            'access' => 'import',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Import', 'editImport')
            )
        );

        $routes['admin/tool/export'] = array(
            'access' => 'export',
            'menu' => array('admin' => 'Export'),
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Export', 'listExport')
            )
        );

        $routes['admin/tool/export/(\w+)'] = array(
            'access' => 'export',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Export', 'editExport')
            )
        );

        $routes['admin/tool/search'] = array(
            'access' => 'search_edit',
            'menu' => array('admin' => 'Search'),
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Search', 'index')
            )
        );

        $routes['admin/sale'] = array(
            'menu' => array('admin' => 'Sales'),
            'handlers' => array(
                'controller' => array('core\\Controller', 'adminSections')
            )
        );

        $routes['admin/sale/order'] = array(
            'access' => 'order',
            'menu' => array('admin' => 'Orders'),
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
            'menu' => array('admin' => 'Prices'),
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\PriceRule', 'listPriceRule')
            )
        );

        $routes['admin/sale/price/add'] = array(
            'access' => 'price_rule_add',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\PriceRule', 'editPriceRule')
            )
        );

        $routes['admin/sale/price/edit/(\d+)'] = array(
            'access' => 'price_rule_edit',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\PriceRule', 'editPriceRule')
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
            'menu' => array('admin' => 'Pages'),
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Page', 'listPage')
            )
        );

        $routes['admin/content/page/add'] = array(
            'access' => 'page_add',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Page', 'editPage')
            )
        );

        $routes['admin/content/page/edit/(\d+)'] = array(
            'access' => 'page_edit',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Page', 'editPage')
            )
        );

        $routes['admin/content/review'] = array(
            'access' => 'review',
            'menu' => array('admin' => 'Reviews'),
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
            'menu' => array('admin' => 'Files'),
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\File', 'listFile')
            )
        );

        $routes['admin/content/category-group'] = array(
            'access' => 'category_group',
            'menu' => array('admin' => 'Categories'),
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\CategoryGroup', 'listCategoryGroup')
            )
        );

        $routes['admin/content/category-group/edit/(\d+)'] = array(
            'access' => 'category_group_edit',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\CategoryGroup', 'editCategoryGroup')
            )
        );

        $routes['admin/content/category-group/add'] = array(
            'access' => 'category_group_add',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\CategoryGroup', 'editCategoryGroup')
            )
        );

        $routes['admin/content/category/(\d+)'] = array(
            'access' => 'category',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Category', 'listCategory')
            )
        );

        $routes['admin/content/category/add/(\d+)'] = array(
            'access' => 'category_add',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Category', 'editCategory')
            )
        );

        $routes['admin/content/category/edit/(\d+)/(\d+)'] = array(
            'access' => 'category_edit',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Category', 'editCategory')
            )
        );

        $routes['admin/user'] = array(
            'menu' => array('admin' => 'Users'),
            'handlers' => array(
                'controller' => array('core\\Controller', 'adminSections')
            )
        );

        $routes['admin/user/list'] = array(
            'access' => 'user',
            'menu' => array('admin' => 'Users'),
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\User', 'users')
            )
        );

        $routes['admin/user/edit/(\d+)'] = array(
            'access' => 'user_edit',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\User', 'edit')
            )
        );

        $routes['admin/user/add'] = array(
            'access' => 'user_add',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\User', 'edit')
            )
        );

        $routes['admin/user/role'] = array(
            'access' => 'user_role',
            'menu' => array('admin' => 'Roles'),
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
            'menu' => array('admin' => 'Fields'),
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Field', 'listField')
            )
        );

        $routes['admin/content/field/value/(\d+)'] = array(
            'access' => 'field_value',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\FieldValue', 'listFieldValue')
            )
        );

        $routes['admin/content/field/value/(\d+)/(\w+)'] = array(
            'access' => 'field_value',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\FieldValue', 'editFieldValue')
            )
        );

        $routes['admin/content/field/edit/(\d+)'] = array(
            'access' => 'field_edit',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Field', 'editField')
            )
        );

        $routes['admin/content/field/add'] = array(
            'access' => 'field_add',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Field', 'editField')
            )
        );

        $routes['admin/content/alias'] = array(
            'access' => 'alias',
            'menu' => array('admin' => 'Aliases'),
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Alias', 'listAlias')
            )
        );

        $routes['admin/module'] = array(
            'menu' => array('admin' => 'Modules'),
            'handlers' => array(
                'controller' => array('core\\Controller', 'adminSections')
            )
        );

        $routes['admin/module/list'] = array(
            'access' => 'module',
            'menu' => array('admin' => 'Local'),
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Module', 'listModule')
            )
        );

        $routes['admin/module/upload'] = array(
            'access' => 'module_upload',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Module', 'uploadModule')
            )
        );

        $routes['admin/module/marketplace'] = array(
            'access' => 'marketplace',
            'menu' => array('admin' => 'Marketplace'),
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Module', 'marketplaceModule')
            )
        );

        $routes['admin/settings'] = array(
            'menu' => array('admin' => 'Settings'),
            'handlers' => array(
                'controller' => array('core\\Controller', 'adminSections')
            )
        );

        $routes['admin/settings/common'] = array(
            'access' => 'settings',
            'menu' => array('admin' => 'Common'),
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Settings', 'common')
            )
        );

        $routes['admin/settings/language'] = array(
            'access' => 'language',
            'menu' => array('admin' => 'Languages'),
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Language', 'listLanguage')
            )
        );

        $routes['admin/settings/language/edit/(\w+)'] = array(
            'access' => 'language_edit',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Language', 'editLanguage')
            )
        );

        $routes['admin/settings/language/add'] = array(
            'access' => 'language_add',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Language', 'editLanguage')
            )
        );

        $routes['admin/settings/imagestyle'] = array(
            'access' => 'image_style',
            'menu' => array('admin' => 'Images'),
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\ImageStyle', 'listImageStyle')
            )
        );

        $routes['admin/settings/search'] = array(
            'access' => 'search_edit',
            'menu' => array('admin' => 'Search'),
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Search', 'settings')
            )
        );

        $routes['admin/settings/imagestyle/edit/(\d+)'] = array(
            'access' => 'image_style_edit',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\ImageStyle', 'editImageStyle')
            )
        );

        $routes['admin/settings/imagestyle/add'] = array(
            'access' => 'image_style_add',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\ImageStyle', 'editImageStyle')
            )
        );

        $routes['admin/settings/currency'] = array(
            'access' => 'currency',
            'menu' => array('admin' => 'Currencies'),
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Currency', 'listCurrency')
            )
        );

        $routes['admin/settings/currency/edit/(\w+)'] = array(
            'access' => 'currency_edit',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Currency', 'editCurrency')
            )
        );

        $routes['admin/settings/currency/add'] = array(
            'access' => 'currency_add',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Currency', 'editCurrency')
            )
        );

        $routes['admin/settings/country'] = array(
            'access' => 'country',
            'menu' => array('admin' => 'Countries'),
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Country', 'listCountry')
            )
        );

        $routes['admin/settings/country/edit/(\w+)'] = array(
            'access' => 'country_edit',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Country', 'editCountry')
            )
        );

        $routes['admin/settings/country/add'] = array(
            'access' => 'country_add',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Country', 'editCountry')
            )
        );

        $routes['admin/settings/country/format/(\w+)'] = array(
            'access' => 'country_format',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Country', 'formatCountry')
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
                'controller' => array('core\\controllers\\admin\\City', 'listCity')
            )
        );

        $routes['admin/settings/city/add/(\w+)/(\d+)'] = array(
            'access' => 'city_add',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\City', 'editCity')
            )
        );

        $routes['admin/settings/city/edit/(\w+)/(\d+)/(\d+)'] = array(
            'access' => 'city_edit',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\City', 'editCity')
            )
        );

        $routes['admin/settings/store'] = array(
            'access' => 'store',
            'menu' => array('admin' => 'Store'),
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

        $routes['admin/report'] = array(
            'menu' => array('admin' => 'Reports'),
            'handlers' => array(
                'controller' => array('core\\Controller', 'adminSections')
            )
        );

        $routes['admin/report/system'] = array(
            'access' => 'report_system',
            'menu' => array('admin' => 'Events'),
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Report', 'system')
            )
        );

        $routes['admin/report/status'] = array(
            'access' => 'report_status',
            'menu' => array('admin' => 'Status'),
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Report', 'status')
            )
        );

        $routes['admin/report/ga'] = array(
            'access' => 'report_ga',
            'menu' => array('admin' => 'Analytics'),
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Report', 'ga')
            )
        );

        $routes['admin/help'] = array(
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Help', 'listHelp')
            )
        );

        $routes['admin/help/([a-z0-9_-]+)'] = array(
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Help', 'pageHelp')
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
