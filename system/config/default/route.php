<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
$routes = array();

$routes['install'] = array(
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\frontend\\Install', 'install')
    )
);

$routes['ajax'] = array(
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\frontend\\Ajax', 'responseAjax')
    )
);

$routes['/'] = array(
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\frontend\\Front', 'indexFront')
    )
);

$routes['transaction/success/(\d+)'] = array(
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\frontend\\Transaction', 'successTransaction')
    )
);

$routes['cron'] = array(
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\Cron', 'executeCron')
    )
);

$routes['product/(\d+)'] = array(
    'alias' => array(0, 1),
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\frontend\\Product', 'indexProduct')
    )
);

$routes['review/add/(\d+)'] = array(
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\frontend\\Review', 'editReview')
    )
);

$routes['review/edit/(\d+)/(\d+)'] = array(
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\frontend\\Review', 'editReview')
    )
);

$routes['files/image/cache/.*'] = array(
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\frontend\\Image', 'cache')
    )
);

$routes['page/(\d+)'] = array(
    'alias' => array(0, 1),
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\frontend\\Page', 'indexPage')
    )
);

$routes['login'] = array(
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\frontend\\User', 'editLoginUser')
    )
);

$routes['logout'] = array(
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\frontend\\User', 'logoutUser')
    )
);

$routes['register'] = array(
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\frontend\\User', 'editRegisterUser')
    )
);

$routes['forgot'] = array(
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\frontend\\User', 'editResetPasswordUser')
    )
);

$routes['category/(\d+)'] = array(
    'alias' => array(0, 1),
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\frontend\\Category', 'indexCategory')
    )
);

$routes['checkout'] = array(
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\frontend\\Checkout', 'editCheckout')
    )
);

$routes['checkout/complete/(\d+)'] = array(
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\frontend\\Checkout', 'completeCheckout')
    )
);

$routes['checkout/edit/(\d+)'] = array(
    'access' => 'order_edit',
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\frontend\\Checkout', 'editOrderCheckout')
    )
);

$routes['checkout/add/(\d+)'] = array(
    'access' => 'order_add',
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\frontend\\Checkout', 'addUserOrderCheckout')
    )
);

$routes['account/(\d+)'] = array(
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\frontend\\Account', 'indexAccount')
    )
);

$routes['account/(\d+)/edit'] = array(
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\frontend\\Account', 'editAccount')
    )
);

$routes['account/(\d+)/address'] = array(
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\frontend\\Account', 'listAddressAccount')
    )
);

$routes['account/(\d+)/address/add'] = array(
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\frontend\\Account', 'editAddressAccount')
    )
);

$routes['wishlist'] = array(
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\frontend\\Wishlist', 'indexWishlist')
    )
);

$routes['compare'] = array(
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\frontend\\Compare', 'selectCompare')
    )
);

$routes['compare/([^/]+)'] = array(
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\frontend\\Compare', 'compare')
    )
);

$routes['search'] = array(
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\frontend\\Search', 'listSearch')
    )
);

$routes['admin'] = array(
    'access' => 'dashboard',
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\Dashboard', 'dashboard')
    )
);

$routes['admin/content'] = array(
    'menu' => array('admin' => 'Content'),
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\Controller', 'adminSections')
    )
);

$routes['admin/content/product'] = array(
    'access' => 'product',
    'menu' => array('admin' => 'Products'),
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\Product', 'listProduct')
    )
);

$routes['admin/content/product/add'] = array(
    'access' => 'product_add',
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\Product', 'editProduct')
    )
);

$routes['admin/content/product/edit/(\d+)'] = array(
    'access' => 'product_edit',
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\Product', 'editProduct')
    )
);

$routes['admin/content/product-class'] = array(
    'access' => 'product_class',
    'menu' => array('admin' => 'Product classes'),
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\ProductClass', 'listProductClass')
    )
);

$routes['admin/content/product-class/edit/(\d+)'] = array(
    'access' => 'product_class_edit',
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\ProductClass', 'editProductClass')
    )
);

$routes['admin/content/product-class/add'] = array(
    'access' => 'product_class_add',
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\ProductClass', 'editProductClass')
    )
);

$routes['admin/content/product-class/field/(\d+)'] = array(
    'access' => 'product_class_edit',
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\ProductClass', 'fieldsProductClass')
    )
);

$routes['admin/content/product-class/field/(\d+)/add'] = array(
    'access' => 'product_class_edit',
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\ProductClass', 'editFieldProductClass')
    )
);

$routes['admin/content/collection'] = array(
    'access' => 'collection',
    'menu' => array('admin' => 'Collections'),
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\Collection', 'listCollection')
    )
);

$routes['admin/content/collection/add'] = array(
    'access' => 'collection_add',
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\Collection', 'editCollection')
    )
);

$routes['admin/content/collection/edit/(\d+)'] = array(
    'access' => 'collection_edit',
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\Collection', 'editCollection')
    )
);

$routes['admin/content/collection-item/(\d+)'] = array(
    'access' => 'collection_item',
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\CollectionItem', 'listCollectionItem')
    )
);

$routes['admin/content/collection-item/(\d+)/add'] = array(
    'access' => 'collection_item_add',
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\CollectionItem', 'editCollectionItem')
    )
);

$routes['admin/tool'] = array(
    'menu' => array('admin' => 'Tools'),
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\Controller', 'adminSections')
    )
);

$routes['admin/tool/import'] = array(
    'access' => 'import',
    'menu' => array('admin' => 'Import'),
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\Import', 'listImport')
    )
);

$routes['admin/tool/export'] = array(
    'access' => 'export',
    'menu' => array('admin' => 'Export'),
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\Export', 'listExport')
    )
);

$routes['admin/tool/export/(\w+)'] = array(
    'access' => 'export',
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\Export', 'editExport')
    )
);

$routes['admin/tool/backup'] = array(
    'access' => 'backup',
    'menu' => array('admin' => 'Backup'),
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\Backup', 'listBackup')
    )
);

$routes['admin/tool/editor/(\w+)'] = array(
    'access' => 'editor',
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\Editor', 'listEditor')
    )
);

$routes['admin/tool/editor/(\w+)/([^/]+)'] = array(
    'access' => 'editor_edit',
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\Editor', 'editEditor')
    )
);

$routes['admin/sale'] = array(
    'menu' => array('admin' => 'Sales'),
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\Controller', 'adminSections')
    )
);

$routes['admin/sale/order'] = array(
    'access' => 'order',
    'menu' => array('admin' => 'Orders'),
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\Order', 'listOrder')
    )
);

$routes['admin/sale/order/(\d+)'] = array(
    'access' => 'order',
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\Order', 'viewOrder')
    )
);

$routes['admin/sale/order-snapshot/(\d+)'] = array(
    'access' => 'order',
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\Order', 'snapshotOrder')
    )
);

$routes['admin/sale/price'] = array(
    'access' => 'price_rule',
    'menu' => array('admin' => 'Prices'),
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\PriceRule', 'listPriceRule')
    )
);

$routes['admin/sale/price/add'] = array(
    'access' => 'price_rule_add',
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\PriceRule', 'editPriceRule')
    )
);

$routes['admin/sale/price/edit/(\d+)'] = array(
    'access' => 'price_rule_edit',
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\PriceRule', 'editPriceRule')
    )
);

$routes['admin/sale/transaction'] = array(
    'access' => 'transaction',
    'menu' => array('admin' => 'Transactions'),
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\Transaction', 'listTransaction')
    )
);

$routes['admin/content/page'] = array(
    'access' => 'page',
    'menu' => array('admin' => 'Pages'),
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\Page', 'listPage')
    )
);

$routes['admin/content/page/add'] = array(
    'access' => 'page_add',
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\Page', 'editPage')
    )
);

$routes['admin/content/page/edit/(\d+)'] = array(
    'access' => 'page_edit',
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\Page', 'editPage')
    )
);

$routes['admin/content/review'] = array(
    'access' => 'review',
    'menu' => array('admin' => 'Reviews'),
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\Review', 'listReview')
    )
);

$routes['admin/content/review/add'] = array(
    'access' => 'review_add',
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\Review', 'editReview')
    )
);

$routes['admin/content/review/edit/(\d+)'] = array(
    'access' => 'review_edit',
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\Review', 'editReview')
    )
);

$routes['admin/content/file'] = array(
    'access' => 'file',
    'menu' => array('admin' => 'Files'),
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\File', 'listFile')
    )
);

$routes['admin/content/file/add'] = array(
    'access' => 'file_add',
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\File', 'editFile')
    )
);

$routes['admin/content/file/edit/(\d+)'] = array(
    'access' => 'file_edit',
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\File', 'editFile')
    )
);

$routes['admin/content/category-group'] = array(
    'access' => 'category_group',
    'menu' => array('admin' => 'Categories'),
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\CategoryGroup', 'listCategoryGroup')
    )
);

$routes['admin/content/category-group/edit/(\d+)'] = array(
    'access' => 'category_group_edit',
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\CategoryGroup', 'editCategoryGroup')
    )
);

$routes['admin/content/category-group/add'] = array(
    'access' => 'category_group_add',
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\CategoryGroup', 'editCategoryGroup')
    )
);

$routes['admin/content/category/(\d+)'] = array(
    'access' => 'category',
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\Category', 'listCategory')
    )
);

$routes['admin/content/category/add/(\d+)'] = array(
    'access' => 'category_add',
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\Category', 'editCategory')
    )
);

$routes['admin/content/category/edit/(\d+)/(\d+)'] = array(
    'access' => 'category_edit',
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\Category', 'editCategory')
    )
);

$routes['admin/user'] = array(
    'menu' => array('admin' => 'Users'),
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\Controller', 'adminSections')
    )
);

$routes['admin/user/list'] = array(
    'access' => 'user',
    'menu' => array('admin' => 'Users'),
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\User', 'listUser')
    )
);

$routes['admin/user/edit/(\d+)'] = array(
    'access' => 'user_edit',
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\User', 'editUser')
    )
);

$routes['admin/user/add'] = array(
    'access' => 'user_add',
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\User', 'editUser')
    )
);

$routes['admin/user/role'] = array(
    'access' => 'user_role',
    'menu' => array('admin' => 'Roles'),
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\UserRole', 'listUserRole')
    )
);

$routes['admin/user/role/add'] = array(
    'access' => 'user_role_add',
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\UserRole', 'editUserRole')
    )
);

$routes['admin/user/role/edit/(\d+)'] = array(
    'access' => 'user_role_edit',
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\UserRole', 'editUserRole')
    )
);

$routes['admin/content/field'] = array(
    'access' => 'field',
    'menu' => array('admin' => 'Fields'),
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\Field', 'listField')
    )
);

$routes['admin/content/field/value/(\d+)'] = array(
    'access' => 'field_value',
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\FieldValue', 'listFieldValue')
    )
);

$routes['admin/content/field/value/(\d+)/(\w+)'] = array(
    'access' => 'field_value',
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\FieldValue', 'editFieldValue')
    )
);

$routes['admin/content/field/edit/(\d+)'] = array(
    'access' => 'field_edit',
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\Field', 'editField')
    )
);

$routes['admin/content/field/add'] = array(
    'access' => 'field_add',
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\Field', 'editField')
    )
);

$routes['admin/content/alias'] = array(
    'access' => 'alias',
    'menu' => array('admin' => 'Aliases'),
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\Alias', 'listAlias')
    )
);

$routes['admin/module'] = array(
    'menu' => array('admin' => 'Modules'),
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\Controller', 'adminSections')
    )
);

$routes['admin/module/list'] = array(
    'access' => 'module',
    'menu' => array('admin' => 'Local'),
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\Module', 'listModule')
    )
);

$routes['admin/module/upload'] = array(
    'access' => 'module_upload',
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\Module', 'uploadModule')
    )
);

$routes['admin/module/marketplace'] = array(
    'access' => 'marketplace',
    'menu' => array('admin' => 'Marketplace'),
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\Module', 'marketplaceModule')
    )
);

$routes['admin/settings'] = array(
    'menu' => array('admin' => 'Settings'),
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\Controller', 'adminSections')
    )
);

$routes['admin/settings/common'] = array(
    'access' => 'settings',
    'menu' => array('admin' => 'Common'),
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\Settings', 'editSettings')
    )
);

$routes['admin/settings/language'] = array(
    'access' => 'language',
    'menu' => array('admin' => 'Languages'),
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\Language', 'listLanguage')
    )
);

$routes['admin/settings/language/edit/(\w+)'] = array(
    'access' => 'language_edit',
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\Language', 'editLanguage')
    )
);

$routes['admin/settings/language/add'] = array(
    'access' => 'language_add',
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\Language', 'editLanguage')
    )
);

$routes['admin/settings/imagestyle'] = array(
    'access' => 'image_style',
    'menu' => array('admin' => 'Images'),
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\ImageStyle', 'listImageStyle')
    )
);

$routes['admin/settings/imagestyle/edit/(\d+)'] = array(
    'access' => 'image_style_edit',
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\ImageStyle', 'editImageStyle')
    )
);

$routes['admin/settings/imagestyle/add'] = array(
    'access' => 'image_style_add',
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\ImageStyle', 'editImageStyle')
    )
);

$routes['admin/settings/currency'] = array(
    'access' => 'currency',
    'menu' => array('admin' => 'Currencies'),
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\Currency', 'listCurrency')
    )
);

$routes['admin/settings/currency/edit/(\w+)'] = array(
    'access' => 'currency_edit',
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\Currency', 'editCurrency')
    )
);

$routes['admin/settings/currency/add'] = array(
    'access' => 'currency_add',
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\Currency', 'editCurrency')
    )
);

$routes['admin/settings/country'] = array(
    'access' => 'country',
    'menu' => array('admin' => 'Countries'),
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\Country', 'listCountry')
    )
);

$routes['admin/settings/country/edit/(\w+)'] = array(
    'access' => 'country_edit',
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\Country', 'editCountry')
    )
);

$routes['admin/settings/country/add'] = array(
    'access' => 'country_add',
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\Country', 'editCountry')
    )
);

$routes['admin/settings/country/format/(\w+)'] = array(
    'access' => 'country_format',
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\Country', 'formatCountry')
    )
);

$routes['admin/settings/states/(\w+)'] = array(
    'access' => 'state',
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\State', 'listState')
    )
);

$routes['admin/settings/state/add/(\w+)'] = array(
    'access' => 'state_add',
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\State', 'editState')
    )
);

$routes['admin/settings/state/edit/(\w+)/(\d+)'] = array(
    'access' => 'state_edit',
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\State', 'editState')
    )
);

$routes['admin/settings/trigger'] = array(
    'access' => 'trigger',
    'menu' => array('admin' => 'Triggers'),
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\Trigger', 'listTrigger')
    )
);

$routes['admin/settings/trigger/add'] = array(
    'access' => 'trigger_add',
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\Trigger', 'editTrigger')
    )
);

$routes['admin/settings/trigger/edit/(\d+)'] = array(
    'access' => 'trigger_edit',
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\Trigger', 'editTrigger')
    )
);

$routes['admin/settings/cities/(\w+)/(\d+)'] = array(
    'access' => 'city',
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\City', 'listCity')
    )
);

$routes['admin/settings/city/add/(\w+)/(\d+)'] = array(
    'access' => 'city_add',
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\City', 'editCity')
    )
);

$routes['admin/settings/city/edit/(\w+)/(\d+)/(\d+)'] = array(
    'access' => 'city_edit',
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\City', 'editCity')
    )
);

$routes['admin/settings/store'] = array(
    'access' => 'store',
    'menu' => array('admin' => 'Store'),
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\Store', 'listStore')
    )
);

$routes['admin/settings/store/(\w+)'] = array(
    'access' => 'store_edit',
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\Store', 'editStore')
    )
);

$routes['admin/settings/zone'] = array(
    'access' => 'zone',
    'menu' => array('admin' => 'Zones'),
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\Zone', 'listZone')
    )
);

$routes['admin/settings/zone/add'] = array(
    'access' => 'zone_add',
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\Zone', 'editZone')
    )
);

$routes['admin/settings/zone/edit/(\d+)'] = array(
    'access' => 'zone_edit',
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\Zone', 'editZone')
    )
);

$routes['admin/settings/filter'] = array(
    'access' => 'filter',
    'menu' => array('admin' => 'Filters'),
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\Filter', 'listFilter')
    )
);

$routes['admin/settings/filter/edit/(\d+)'] = array(
    'access' => 'filter_edit',
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\Filter', 'editFilter')
    )
);

$routes['admin/report'] = array(
    'access' => 'report',
    'menu' => array('admin' => 'Reports'),
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\Controller', 'adminSections')
    )
);

$routes['admin/report/events'] = array(
    'access' => 'report_events',
    'menu' => array('admin' => 'Events'),
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\Report', 'listEventReport')
    )
);

$routes['admin/report/status'] = array(
    'access' => 'report_status',
    'menu' => array('admin' => 'Status'),
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\Report', 'listStatusReport')
    )
);

$routes['admin/report/library'] = array(
    'access' => 'report_library',
    'menu' => array('admin' => 'Libraries'),
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\Library', 'listLibrary')
    )
);

$routes['admin/report/route'] = array(
    'access' => 'report',
    'menu' => array('admin' => 'Routes'),
    'handlers' => array(
        'controller' => array('gplcart\\core\\controllers\\backend\\Report', 'listRoutesReport')
    )
);

return $routes;
