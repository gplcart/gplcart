<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core;

use core\Hook;
use core\Config;
use core\Handler;
use core\classes\Url;
use core\classes\Tool;
use core\classes\Cache;
use core\classes\Request;
use core\exceptions\RouteException;

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
                'controller' => array('core\\controllers\\Ajax', 'getResponseAjax')
            )
        );

        $routes[''] = array(
            'handlers' => array(
                'controller' => array('core\\controllers\\Front', 'indexFront')
            )
        );

        $routes['transaction/success/(\d+)'] = array(
            'handlers' => array(
                'controller' => array('core\\controllers\\Transaction', 'successTransaction')
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
                'controller' => array('core\\controllers\\Product', 'indexProduct')
            )
        );

        $routes['review/add/(\d+)'] = array(
            'handlers' => array(
                'controller' => array('core\\controllers\\Review', 'editReview')
            )
        );

        $routes['review/edit/(\d+)/(\d+)'] = array(
            'handlers' => array(
                'controller' => array('core\\controllers\\Review', 'editReview')
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
                'controller' => array('core\\controllers\\Page', 'indexPage')
            )
        );

        $routes['login'] = array(
            'handlers' => array(
                'controller' => array('core\\controllers\\User', 'editLoginUser')
            )
        );

        $routes['logout'] = array(
            'handlers' => array(
                'controller' => array('core\\controllers\\User', 'logoutUser')
            )
        );

        $routes['register'] = array(
            'handlers' => array(
                'controller' => array('core\\controllers\\User', 'EditRegisterUser')
            )
        );

        $routes['forgot'] = array(
            'handlers' => array(
                'controller' => array('core\\controllers\\User', 'EditResetPasswordUser')
            )
        );

        $routes['category/(\d+)'] = array(
            'alias' => array(0, 1),
            'handlers' => array(
                'controller' => array('core\\controllers\\Category', 'indexCategory')
            )
        );

        $routes['checkout'] = array(
            'handlers' => array(
                'controller' => array('core\\controllers\\Checkout', 'editCheckout')
            )
        );

        $routes['checkout/complete/(\d+)'] = array(
            'handlers' => array(
                'controller' => array('core\\controllers\\Checkout', 'completeCheckout')
            )
        );

        $routes['checkout/edit/(\d+)'] = array(
            'access' => 'order_edit',
            'handlers' => array(
                'controller' => array('core\\controllers\\Checkout', 'editOrderCheckout')
            )
        );

        $routes['checkout/add/(\d+)'] = array(
            'access' => 'order_add',
            'handlers' => array(
                'controller' => array('core\\controllers\\Checkout', 'addUserOrderCheckout')
            )
        );

        $routes['account/(\d+)'] = array(
            'handlers' => array(
                'controller' => array('core\\controllers\\Account', 'indexAccount')
            )
        );

        $routes['account/(\d+)/edit'] = array(
            'handlers' => array(
                'controller' => array('core\\controllers\\Account', 'editAccount')
            )
        );

        $routes['account/(\d+)/address'] = array(
            'handlers' => array(
                'controller' => array('core\\controllers\\Account', 'listAddressAccount')
            )
        );

        $routes['account/(\d+)/address/add'] = array(
            'handlers' => array(
                'controller' => array('core\\controllers\\Account', 'editAddressAccount')
            )
        );

        $routes['wishlist'] = array(
            'handlers' => array(
                'controller' => array('core\\controllers\\Wishlist', 'indexWishlist')
            )
        );

        $routes['compare'] = array(
            'handlers' => array(
                'controller' => array('core\\controllers\\Compare', 'selectCompare')
            )
        );

        $routes['compare/([^/]+)'] = array(
            'handlers' => array(
                'controller' => array('core\\controllers\\Compare', 'compare')
            )
        );

        $routes['search'] = array(
            'handlers' => array(
                'controller' => array('core\\controllers\\Search', 'indexSearch')
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
                'controller' => array('core\\controllers\\admin\\Controller', 'adminSections')
            )
        );

        $routes['admin/content/product'] = array(
            'access' => 'product',
            'menu' => array('admin' => 'Products'),
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Product', 'listProduct')
            )
        );

        $routes['admin/content/product/add'] = array(
            'access' => 'product_add',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Product', 'editProduct')
            )
        );

        $routes['admin/content/product/edit/(\d+)'] = array(
            'access' => 'product_edit',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Product', 'editProduct')
            )
        );

        $routes['admin/content/product-class'] = array(
            'access' => 'product_class',
            'menu' => array('admin' => 'Product classes'),
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\ProductClass', 'listProductClass')
            )
        );

        $routes['admin/content/product-class/edit/(\d+)'] = array(
            'access' => 'product_class_edit',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\ProductClass', 'editProductClass')
            )
        );

        $routes['admin/content/product-class/add'] = array(
            'access' => 'product_class_add',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\ProductClass', 'editProductClass')
            )
        );

        $routes['admin/content/product-class/field/(\d+)'] = array(
            'access' => 'product_class_edit',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\ProductClass', 'fieldsProductClass')
            )
        );

        $routes['admin/content/product-class/field/(\d+)/add'] = array(
            'access' => 'product_class_edit',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\ProductClass', 'editFieldProductClass')
            )
        );

        $routes['admin/content/collection'] = array(
            'access' => 'collection',
            'menu' => array('admin' => 'Collections'),
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Collection', 'listCollection')
            )
        );

        $routes['admin/content/collection/add'] = array(
            'access' => 'collection_add',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Collection', 'editCollection')
            )
        );

        $routes['admin/content/collection/edit/(\d+)'] = array(
            'access' => 'collection_edit',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Collection', 'editCollection')
            )
        );

        $routes['admin/content/collection-item/(\d+)'] = array(
            'access' => 'collection_item',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\CollectionItem', 'listCollectionItem')
            )
        );

        $routes['admin/content/collection-item/(\d+)/add'] = array(
            'access' => 'collection_item_add',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\CollectionItem', 'editCollectionItem')
            )
        );

        $routes['admin/tool'] = array(
            'menu' => array('admin' => 'Tools'),
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Controller', 'adminSections')
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

        $routes['admin/sale'] = array(
            'menu' => array('admin' => 'Sales'),
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Controller', 'adminSections')
            )
        );

        $routes['admin/sale/order'] = array(
            'access' => 'order',
            'menu' => array('admin' => 'Orders'),
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Order', 'listOrder')
            )
        );

        $routes['admin/sale/order/(\d+)'] = array(
            'access' => 'order',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Order', 'viewOrder')
            )
        );

        $routes['admin/sale/order-snapshot/(\d+)'] = array(
            'access' => 'order',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Order', 'snapshotOrder')
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

        $routes['admin/sale/transaction'] = array(
            'access' => 'transaction',
            'menu' => array('admin' => 'Transactions'),
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Transaction', 'listTransaction')
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
                'controller' => array('core\\controllers\\admin\\Review', 'listReview')
            )
        );

        $routes['admin/content/review/add'] = array(
            'access' => 'review_add',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Review', 'editReview')
            )
        );

        $routes['admin/content/review/edit/(\d+)'] = array(
            'access' => 'review_edit',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Review', 'editReview')
            )
        );

        $routes['admin/content/file'] = array(
            'access' => 'file',
            'menu' => array('admin' => 'Files'),
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\File', 'listFile')
            )
        );

        $routes['admin/content/file/add'] = array(
            'access' => 'file_add',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\File', 'editFile')
            )
        );

        $routes['admin/content/file/edit/(\d+)'] = array(
            'access' => 'file_edit',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\File', 'editFile')
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
                'controller' => array('core\\controllers\\admin\\Controller', 'adminSections')
            )
        );

        $routes['admin/user/list'] = array(
            'access' => 'user',
            'menu' => array('admin' => 'Users'),
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\User', 'listUser')
            )
        );

        $routes['admin/user/edit/(\d+)'] = array(
            'access' => 'user_edit',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\User', 'editUser')
            )
        );

        $routes['admin/user/add'] = array(
            'access' => 'user_add',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\User', 'editUser')
            )
        );

        $routes['admin/user/role'] = array(
            'access' => 'user_role',
            'menu' => array('admin' => 'Roles'),
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\UserRole', 'listUserRole')
            )
        );

        $routes['admin/user/role/add'] = array(
            'access' => 'user_role_add',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\UserRole', 'editUserRole')
            )
        );

        $routes['admin/user/role/edit/(\d+)'] = array(
            'access' => 'user_role_edit',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\UserRole', 'editUserRole')
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
                'controller' => array('core\\controllers\\admin\\Controller', 'adminSections')
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
                'controller' => array('core\\controllers\\admin\\Controller', 'adminSections')
            )
        );

        $routes['admin/settings/common'] = array(
            'access' => 'settings',
            'menu' => array('admin' => 'Common'),
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Settings', 'editSettings')
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
                'controller' => array('core\\controllers\\admin\\State', 'listState')
            )
        );

        $routes['admin/settings/state/add/(\w+)'] = array(
            'access' => 'state_add',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\State', 'editState')
            )
        );

        $routes['admin/settings/state/edit/(\w+)/(\d+)'] = array(
            'access' => 'state_edit',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\State', 'editState')
            )
        );

        $routes['admin/settings/trigger'] = array(
            'access' => 'trigger',
            'menu' => array('admin' => 'Triggers'),
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Trigger', 'listTrigger')
            )
        );

        $routes['admin/settings/trigger/add'] = array(
            'access' => 'trigger_add',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Trigger', 'editTrigger')
            )
        );

        $routes['admin/settings/trigger/edit/(\d+)'] = array(
            'access' => 'trigger_edit',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Trigger', 'editTrigger')
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
                'controller' => array('core\\controllers\\admin\\Store', 'listStore')
            )
        );

        $routes['admin/settings/store/(\w+)'] = array(
            'access' => 'store_edit',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Store', 'editStore')
            )
        );

        $routes['admin/settings/zone'] = array(
            'access' => 'zone',
            'menu' => array('admin' => 'Zones'),
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Zone', 'listZone')
            )
        );

        $routes['admin/settings/zone/add'] = array(
            'access' => 'zone_add',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Zone', 'editZone')
            )
        );

        $routes['admin/settings/zone/edit/(\d+)'] = array(
            'access' => 'zone_edit',
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Zone', 'editZone')
            )
        );

        $routes['admin/report'] = array(
            'menu' => array('admin' => 'Reports'),
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Controller', 'adminSections')
            )
        );

        $routes['admin/report/events'] = array(
            'access' => 'report_events',
            'menu' => array('admin' => 'Events'),
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Report', 'listEventReport')
            )
        );

        $routes['admin/report/status'] = array(
            'access' => 'report_status',
            'menu' => array('admin' => 'Status'),
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Report', 'listStatusReport')
            )
        );

        $routes['admin/report/ga'] = array(
            'access' => 'report_ga',
            'menu' => array('admin' => 'Analytics'),
            'handlers' => array(
                'controller' => array('core\\controllers\\admin\\Report', 'listGaReport')
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
        $this->callControllerAlias();
        $this->callControllerRoute();
        $this->callControllerNotFound();
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
        $lang = $this->request->get('lang');

        if (isset($lang)) {
            $this->langcode = $lang;
            return;
        }

        $default_langcode = $this->config->get('language', '');
        $languages = $this->config->get('languages', array());

        $segments = $this->url->segments();

        if (empty($languages[$segments[0]]['status'])) {
            $this->langcode = $default_langcode;
        } else {
            $this->langcode = $segments[0];
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
    protected function callControllerAlias()
    {
        if (empty($this->db)) {
            return; // No database available, exit
        }

        $info = $this->getAliasInfo($this->path);

        if (isset($info['id_key'])) {

            // Entity name: product, page, category etc...
            $entityname = str_replace('_id', '', $info['id_key']);

            foreach ($this->getList() as $pattern => $route) {

                if (!isset($route['alias'][0])) {
                    continue; // This route doesn't support aliases
                }

                $pattern_segments = explode('/', $pattern);

                if ($pattern_segments[$route['alias'][0]] !== $entityname) {
                    continue; // Entity name not matching, try the next route
                }

                $route['arguments'] = array($info['id_value']);
                $this->route = $route + array('pattern' => $pattern);
                Handler::call($route, null, 'controller', $route['arguments']);
                throw new RouteException('An error occurred while processing the route');
            }
        }

        // Failed to found the matching controller above
        // The current path can be a system path like product/1
        // so now we'll try to find an appropriate alias in the database and redirect to it
        $this->redirectToAlias();
    }

    /**
     * Finds an alias by the route pattern
     * and redirects to it
     */
    protected function redirectToAlias()
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

            $alias = $this->getAliasById($path_segments, $route);

            if (empty($alias)) {
                continue;
            }

            $this->route = $route + array('pattern' => $pattern);
            $this->url->redirect($alias);
            throw new RouteException('An error occurred while processing the route');
        }
    }

    /**
     * Selects an alias using entity key ID and numeric value
     * @param array $segments
     * @param array $route
     * @return string
     */
    protected function getAliasById(array $segments, array $route)
    {
        $sql = 'SELECT alias'
                . ' FROM alias'
                . ' WHERE id_key=? AND id_value=?';

        $conditions = array(
            $segments[$route['alias'][0]] . '_id',
            $segments[$route['alias'][1]]
        );

        return (string) $this->db->fetchColumn($sql, $conditions);
    }

    /**
     * Returns alias info (keys) using the current URL path
     * @param string $alias
     * @return array|boolean
     */
    protected function getAliasInfo($alias)
    {
        $sql = 'SELECT id_key, id_value FROM alias WHERE alias=?';
        return $this->db->fetch($sql, array($alias));
    }

    /**
     * Calls an appropriate controller for the current URL
     * @param array $routes
     */
    protected function callControllerRoute()
    {
        foreach ($this->getList() as $pattern => $route) {

            $arguments = Tool::patternMatch($this->path, $pattern);

            if ($arguments === false) {
                continue;
            }

            $route['arguments'] = $arguments;
            $this->route = $route + array('pattern' => $pattern);

            Handler::call($route, null, 'controller', $arguments);
            throw new RouteException('An error occurred while processing the route');
        }
    }

    /**
     * Displays 404 Not Found Page
     */
    protected function callControllerNotFound()
    {
        $class = 'core\\controllers\\Controller';

        // Use correct templates
        if ($this->url->isBackend()) {
            $class = 'core\\controllers\\admin\\Controller';
        }

        $route = array(
            'handlers' => array(
                'controller' => array($class, 'outputError'))
        );

        Handler::call($route, null, 'controller', array(404));
        throw new RouteException('An error occurred while processing the route');
    }

    /**
     * Returns the current path
     * @return string
     */
    public function path()
    {
        return $this->path;
    }

}
