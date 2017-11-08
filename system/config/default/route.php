<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
return array(
    '/' => array(
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\frontend\\Front', 'indexFront')
        )
    ),
    'status-backend' => array(
        'status' => false,
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\Controller', 'outputHttpStatus')
        )
    ),
    'status-frontend' => array(
        'status' => false,
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\frontend\\Controller', 'outputHttpStatus')
        )
    ),
    'oauth' => array(
        'internal' => true,
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\frontend\\Oauth', 'callbackOauth')
        )
    ),
    'ajax' => array(
        'internal' => true,
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\frontend\\Ajax', 'responseAjax')
        )
    ),
    'cron' => array(
        'internal' => true,
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\frontend\\Cron', 'executeCron')
        )
    ),
    'files/image/cache/(.*)' => array(
        'internal' => true,
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\frontend\\Image', 'outputCacheImage')
        )
    ),
    'install' => array(
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\frontend\\Install', 'editInstall')
        )
    ),
    'product/(\d+)' => array(
        'handlers' => array(
            'alias' => array('gplcart\\core\\Route', 'findAlias'),
            'controller' => array('gplcart\\core\\controllers\\frontend\\Product', 'indexProduct')
        )
    ),
    'review/add/(\d+)' => array(
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\frontend\\Review', 'editReview')
        )
    ),
    'review/edit/(\d+)/(\d+)' => array(
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\frontend\\Review', 'editReview')
        )
    ),
    'page/(\d+)' => array(
        'handlers' => array(
            'alias' => array('gplcart\\core\\Route', 'findAlias'),
            'controller' => array('gplcart\\core\\controllers\\frontend\\Page', 'indexPage')
        )
    ),
    'login' => array(
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\frontend\\User', 'editLoginUser')
        )
    ),
    'logout' => array(
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\frontend\\User', 'logoutUser')
        )
    ),
    'register' => array(
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\frontend\\User', 'editRegisterUser')
        )
    ),
    'forgot' => array(
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\frontend\\User', 'editResetPasswordUser')
        )
    ),
    'catalog' => array(
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\frontend\\Category', 'listCategory')
        )
    ),
    'category/(\d+)' => array(
        'handlers' => array(
            'alias' => array('gplcart\\core\\Route', 'findAlias'),
            'controller' => array('gplcart\\core\\controllers\\frontend\\Category', 'indexCategory')
        )
    ),
    'checkout' => array(
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\frontend\\Checkout', 'editCheckout')
        )
    ),
    'checkout/complete/(\d+)' => array(
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\frontend\\Checkout', 'completeCheckout')
        )
    ),
    'checkout/clone/(\d+)' => array(
        'access' => 'order_add',
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\frontend\\Checkout', 'cloneOrderCheckout')
        )
    ),
    'checkout/add/(\d+)' => array(
        'access' => 'order_add',
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\frontend\\Checkout', 'createOrderCheckout')
        )
    ),
    'account/(\d+)' => array(
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\frontend\\Account', 'indexAccount')
        )
    ),
    'account/(\d+)/order/(\d+)' => array(
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\frontend\\Account', 'orderAccount')
        )
    ),
    'account/(\d+)/edit' => array(
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\frontend\\Account', 'editAccount')
        )
    ),
    'account/(\d+)/address' => array(
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\frontend\\Account', 'listAddressAccount')
        )
    ),
    'wishlist' => array(
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\frontend\\Wishlist', 'indexWishlist')
        )
    ),
    'compare' => array(
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\frontend\\Compare', 'selectCompare')
        )
    ),
    'compare/([^/]+)' => array(
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\frontend\\Compare', 'compareCompare')
        )
    ),
    'search' => array(
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\frontend\\Search', 'listSearch')
        )
    ),
    'admin' => array(
        'access' => 'dashboard',
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\Dashboard', 'indexDashboard')
        )
    ),
    'admin/bookmark' => array(
        'access' => 'bookmark',
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\Bookmark', 'listBookmark')
        )
    ),
    'admin/bookmark/add' => array(
        'access' => 'bookmark_add',
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\Bookmark', 'addBookmark')
        )
    ),
    'admin/bookmark/delete' => array(
        'access' => 'bookmark_delete',
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\Bookmark', 'deleteBookmark')
        )
    ),
    'admin/content' => array(
        'menu' => array('admin' => /* @text */'Content'),
        'arguments' => array('admin/content'),
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\Section', 'listSection')
        )
    ),
    'admin/content/product' => array(
        'access' => 'product',
        'menu' => array('admin' => /* @text */'Products'),
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\Product', 'listProduct')
        )
    ),
    'admin/content/product/add' => array(
        'access' => 'product_add',
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\Product', 'editProduct')
        )
    ),
    'admin/content/product/edit/(\d+)' => array(
        'access' => 'product_edit',
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\Product', 'editProduct')
        )
    ),
    'admin/content/product-class' => array(
        'access' => 'product_class',
        'menu' => array('admin' => /* @text */'Product classes'),
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\ProductClass', 'listProductClass')
        )
    ),
    'admin/content/product-class/edit/(\d+)' => array(
        'access' => 'product_class_edit',
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\ProductClass', 'editProductClass')
        )
    ),
    'admin/content/product-class/add' => array(
        'access' => 'product_class_add',
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\ProductClass', 'editProductClass')
        )
    ),
    'admin/content/product-class/field/(\d+)' => array(
        'access' => 'product_class_edit',
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\ProductClass', 'fieldsProductClass')
        )
    ),
    'admin/content/product-class/field/(\d+)/add' => array(
        'access' => 'product_class_edit',
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\ProductClass', 'editFieldProductClass')
        )
    ),
    'admin/content/collection' => array(
        'access' => 'collection',
        'menu' => array('admin' => /* @text */'Collections'),
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\Collection', 'listCollection')
        )
    ),
    'admin/content/collection/add' => array(
        'access' => 'collection_add',
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\Collection', 'editCollection')
        )
    ),
    'admin/content/collection/edit/(\d+)' => array(
        'access' => 'collection_edit',
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\Collection', 'editCollection')
        )
    ),
    'admin/content/collection-item/(\d+)' => array(
        'access' => 'collection_item',
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\CollectionItem', 'listCollectionItem')
        )
    ),
    'admin/content/collection-item/(\d+)/add' => array(
        'access' => 'collection_item_add',
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\CollectionItem', 'editCollectionItem')
        )
    ),
    'admin/tool' => array(
        'menu' => array('admin' => /* @text */'Tools'),
        'arguments' => array('admin/tool'),
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\Section', 'listSection')
        )
    ),
    'admin/sale' => array(
        'menu' => array('admin' => /* @text */'Sales'),
        'arguments' => array('admin/sale'),
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\Section', 'listSection')
        )
    ),
    'admin/sale/order' => array(
        'access' => 'order',
        'menu' => array('admin' => /* @text */'Orders'),
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\Order', 'listOrder')
        )
    ),
    'admin/sale/order/(\d+)' => array(
        'access' => 'order',
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\Order', 'indexOrder')
        )
    ),
    'admin/sale/price' => array(
        'access' => 'price_rule',
        'menu' => array('admin' => /* @text */'Prices'),
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\PriceRule', 'listPriceRule')
        )
    ),
    'admin/sale/price/add' => array(
        'access' => 'price_rule_add',
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\PriceRule', 'editPriceRule')
        )
    ),
    'admin/sale/price/edit/(\d+)' => array(
        'access' => 'price_rule_edit',
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\PriceRule', 'editPriceRule')
        )
    ),
    'admin/sale/transaction' => array(
        'access' => 'transaction',
        'menu' => array('admin' => /* @text */'Transactions'),
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\Transaction', 'listTransaction')
        )
    ),
    'admin/sale/cart' => array(
        'access' => 'cart',
        'menu' => array('admin' => /* @text */'Cart'),
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\Cart', 'listCart')
        )
    ),
    'admin/content/page' => array(
        'access' => 'page',
        'menu' => array('admin' => /* @text */'Pages'),
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\Page', 'listPage')
        )
    ),
    'admin/content/page/add' => array(
        'access' => 'page_add',
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\Page', 'editPage')
        )
    ),
    'admin/content/page/edit/(\d+)' => array(
        'access' => 'page_edit',
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\Page', 'editPage')
        )
    ),
    'admin/content/review' => array(
        'access' => 'review',
        'menu' => array('admin' => 'Reviews'),
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\Review', 'listReview')
        )
    ),
    'admin/content/review/add' => array(
        'access' => 'review_add',
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\Review', 'editReview')
        )
    ),
    'admin/content/review/edit/(\d+)' => array(
        'access' => 'review_edit',
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\Review', 'editReview')
        )
    ),
    'admin/content/file' => array(
        'access' => 'file',
        'menu' => array('admin' => /* @text */'Files'),
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\File', 'listFile')
        )
    ),
    'admin/content/file/add' => array(
        'access' => 'file_add',
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\File', 'editFile')
        )
    ),
    'admin/content/file/edit/(\d+)' => array(
        'access' => 'file_edit',
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\File', 'editFile')
        )
    ),
    'admin/content/category-group' => array(
        'access' => 'category_group',
        'menu' => array('admin' => 'Categories'),
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\CategoryGroup', 'listCategoryGroup')
        )
    ),
    'admin/content/category-group/edit/(\d+)' => array(
        'access' => 'category_group_edit',
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\CategoryGroup', 'editCategoryGroup')
        )
    ),
    'admin/content/category-group/add' => array(
        'access' => 'category_group_add',
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\CategoryGroup', 'editCategoryGroup')
        )
    ),
    'admin/content/category/(\d+)' => array(
        'access' => 'category',
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\Category', 'listCategory')
        )
    ),
    'admin/content/category/add/(\d+)' => array(
        'access' => 'category_add',
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\Category', 'editCategory')
        )
    ),
    'admin/content/category/edit/(\d+)/(\d+)' => array(
        'access' => 'category_edit',
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\Category', 'editCategory')
        )
    ),
    'admin/user' => array(
        'menu' => array('admin' => /* @text */'Users'),
        'arguments' => array('admin/user'),
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\Section', 'listSection')
        )
    ),
    'admin/user/list' => array(
        'access' => 'user',
        'menu' => array('admin' => /* @text */'Users'),
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\User', 'listUser')
        )
    ),
    'admin/user/edit/(\d+)' => array(
        'access' => 'user_edit',
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\User', 'editUser')
        )
    ),
    'admin/user/add' => array(
        'access' => 'user_add',
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\User', 'editUser')
        )
    ),
    'admin/user/role' => array(
        'access' => 'user_role',
        'menu' => array('admin' => /* @text */'Roles'),
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\UserRole', 'listUserRole')
        )
    ),
    'admin/user/role/add' => array(
        'access' => 'user_role_add',
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\UserRole', 'editUserRole')
        )
    ),
    'admin/user/role/edit/(\d+)' => array(
        'access' => 'user_role_edit',
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\UserRole', 'editUserRole')
        )
    ),
    'admin/user/address' => array(
        'access' => 'address',
        'menu' => array('admin' => /* @text */'Addresses'),
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\Address', 'listAddress')
        )
    ),
    'admin/content/field' => array(
        'access' => 'field',
        'menu' => array('admin' => /* @text */'Fields'),
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\Field', 'listField')
        )
    ),
    'admin/content/field/value/(\d+)' => array(
        'access' => 'field_value',
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\FieldValue', 'listFieldValue')
        )
    ),
    'admin/content/field/value/(\d+)/add' => array(
        'access' => 'field_value_add',
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\FieldValue', 'editFieldValue')
        )
    ),
    'admin/content/field/value/(\d+)/(\d+)/edit' => array(
        'access' => 'field_value_edit',
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\FieldValue', 'editFieldValue')
        )
    ),
    'admin/content/field/edit/(\d+)' => array(
        'access' => 'field_edit',
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\Field', 'editField')
        )
    ),
    'admin/content/field/add' => array(
        'access' => 'field_add',
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\Field', 'editField')
        )
    ),
    'admin/content/alias' => array(
        'access' => 'alias',
        'menu' => array('admin' => /* @text */'Aliases'),
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\Alias', 'listAlias')
        )
    ),
    'admin/module' => array(
        'menu' => array('admin' => /* @text */'Modules'),
        'arguments' => array('admin/module'),
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\Section', 'listSection')
        )
    ),
    'admin/module/list' => array(
        'access' => 'module',
        'menu' => array('admin' => /* @text */'On site'),
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\Module', 'listModule')
        )
    ),
    'admin/settings' => array(
        'menu' => array('admin' => /* @text */'Settings'),
        'arguments' => array('admin/settings'),
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\Section', 'listSection')
        )
    ),
    'admin/settings/common' => array(
        'access' => '__superadmin',
        'menu' => array('admin' => /* @text */'Common'),
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\Settings', 'editSettings')
        )
    ),
    'admin/settings/dashboard' => array(
        'menu' => array('admin' => /* @text */'Dashboard'),
        'access' => 'dashboard_edit',
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\Dashboard', 'editDashboard')
        )
    ),
    'admin/settings/language' => array(
        'access' => 'language',
        'menu' => array('admin' => /* @text */'Languages'),
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\Language', 'listLanguage')
        )
    ),
    'admin/settings/language/edit/([^/]+)' => array(
        'access' => 'language_edit',
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\Language', 'editLanguage')
        )
    ),
    'admin/settings/language/add' => array(
        'access' => 'language_add',
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\Language', 'editLanguage')
        )
    ),
    'admin/settings/imagestyle' => array(
        'access' => 'image_style',
        'menu' => array('admin' => /* @text */'Images'),
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\ImageStyle', 'listImageStyle')
        )
    ),
    'admin/settings/imagestyle/edit/(\d+)' => array(
        'access' => 'image_style_edit',
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\ImageStyle', 'editImageStyle')
        )
    ),
    'admin/settings/imagestyle/add' => array(
        'access' => 'image_style_add',
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\ImageStyle', 'editImageStyle')
        )
    ),
    'admin/settings/currency' => array(
        'access' => 'currency',
        'menu' => array('admin' => /* @text */'Currencies'),
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\Currency', 'listCurrency')
        )
    ),
    'admin/settings/currency/edit/(\w+)' => array(
        'access' => 'currency_edit',
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\Currency', 'editCurrency')
        )
    ),
    'admin/settings/currency/add' => array(
        'access' => 'currency_add',
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\Currency', 'editCurrency')
        )
    ),
    'admin/settings/country' => array(
        'access' => 'country',
        'menu' => array('admin' => /* @text */'Countries'),
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\Country', 'listCountry')
        )
    ),
    'admin/settings/country/edit/(\w+)' => array(
        'access' => 'country_edit',
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\Country', 'editCountry')
        )
    ),
    'admin/settings/country/add' => array(
        'access' => 'country_add',
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\Country', 'editCountry')
        )
    ),
    'admin/settings/country/format/(\w+)' => array(
        'access' => 'country_format',
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\Country', 'formatCountry')
        )
    ),
    'admin/settings/states/(\w+)' => array(
        'access' => 'state',
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\State', 'listState')
        )
    ),
    'admin/settings/state/add/(\w+)' => array(
        'access' => 'state_add',
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\State', 'editState')
        )
    ),
    'admin/settings/state/edit/(\w+)/(\d+)' => array(
        'access' => 'state_edit',
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\State', 'editState')
        )
    ),
    'admin/settings/trigger' => array(
        'access' => 'trigger',
        'menu' => array('admin' => /* @text */'Triggers'),
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\Trigger', 'listTrigger')
        )
    ),
    'admin/settings/trigger/add' => array(
        'access' => 'trigger_add',
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\Trigger', 'editTrigger')
        )
    ),
    'admin/settings/trigger/edit/(\d+)' => array(
        'access' => 'trigger_edit',
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\Trigger', 'editTrigger')
        )
    ),
    'admin/settings/cities/(\w+)/(\d+)' => array(
        'access' => 'city',
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\City', 'listCity')
        )
    ),
    'admin/settings/city/add/(\w+)/(\d+)' => array(
        'access' => 'city_add',
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\City', 'editCity')
        )
    ),
    'admin/settings/city/edit/(\w+)/(\d+)/(\d+)' => array(
        'access' => 'city_edit',
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\City', 'editCity')
        )
    ),
    'admin/settings/store' => array(
        'access' => 'store',
        'menu' => array('admin' => /* @text */'Store'),
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\Store', 'listStore')
        )
    ),
    'admin/settings/store/add' => array(
        'access' => 'store_add',
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\Store', 'editStore')
        )
    ),
    'admin/settings/store/edit/(\d+)' => array(
        'access' => 'store_edit',
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\Store', 'editStore')
        )
    ),
    'admin/settings/zone' => array(
        'access' => 'zone',
        'menu' => array('admin' => /* @text */'Zones'),
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\Zone', 'listZone')
        )
    ),
    'admin/settings/zone/add' => array(
        'access' => 'zone_add',
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\Zone', 'editZone')
        )
    ),
    'admin/settings/zone/edit/(\d+)' => array(
        'access' => 'zone_edit',
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\Zone', 'editZone')
        )
    ),
    'admin/report' => array(
        'access' => 'report',
        'menu' => array('admin' => /* @text */'Reports'),
        'arguments' => array('admin/report'),
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\Section', 'listSection')
        )
    ),
    'admin/report/events' => array(
        'access' => 'report_events',
        'menu' => array('admin' => /* @text */'Events'),
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\Report', 'listEventReport')
        )
    ),
    'admin/report/status' => array(
        'access' => 'report_status',
        'menu' => array('admin' => /* @text */'Status'),
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\Report', 'listStatusReport')
        )
    ),
    'admin/report/library' => array(
        'access' => 'report_library',
        'menu' => array('admin' => /* @text */'Libraries'),
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\Library', 'listLibrary')
        )
    ),
    'admin/report/route' => array(
        'access' => 'report',
        'menu' => array('admin' => /* @text */'Routes'),
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\Report', 'listRoutesReport')
        )
    ),
    'admin/report/payment' => array(
        'access' => 'report',
        'menu' => array('admin' => /* @text */'Payment methods'),
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\Report', 'listPaymentMethodsReport')
        )
    ),
    'admin/report/shipping' => array(
        'access' => 'report',
        'menu' => array('admin' => /* @text */'Shipping methods'),
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\Report', 'listShippingMethodsReport')
        )
    )
);
