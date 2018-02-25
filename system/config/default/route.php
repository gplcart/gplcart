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
    'blog' => array(
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\frontend\\Blog', 'listBlog')
        )
    ),
    'install' => array(
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\frontend\\Install', 'editInstall')
        )
    ),
    'product/(\d+)' => array(
        'handlers' => array(
            'alias' => array('gplcart\\core\\Route', 'aliasCallback'),
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
            'alias' => array('gplcart\\core\\Route', 'aliasCallback'),
            'controller' => array('gplcart\\core\\controllers\\frontend\\Page', 'indexPage')
        )
    ),
    'login' => array(
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\frontend\\UserLogin', 'editUserLogin')
        )
    ),
    'logout' => array(
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\frontend\\UserLogOut', 'UserLogOut')
        )
    ),
    'register' => array(
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\frontend\\UserRegister', 'editUserRegister')
        )
    ),
    'forgot' => array(
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\frontend\\UserForgot', 'editUserForgot')
        )
    ),
    'catalog' => array(
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\frontend\\Category', 'listCategory')
        )
    ),
    'category/(\d+)' => array(
        'handlers' => array(
            'alias' => array('gplcart\\core\\Route', 'aliasCallback'),
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
            'controller' => array('gplcart\\core\\controllers\\frontend\\CheckoutComplete', 'checkoutComplete')
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
            'controller' => array('gplcart\\core\\controllers\\frontend\\AccountOrder', 'accountOrder')
        )
    ),
    'account/(\d+)/edit' => array(
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\frontend\\Account', 'editAccount')
        )
    ),
    'account/(\d+)/address' => array(
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\frontend\\AccountAddress', 'listAccountAddress')
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
    'admin/help' => array(
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\Help', 'listHelp')
        )
    ),
    'admin/help/(\w+)' => array(
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\Help', 'viewHelp')
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
        'menu' => array(
            'admin' => 'Content' // @text
        ),
        'arguments' => array('admin/content'),
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\Section', 'listSection')
        )
    ),
    'admin/content/product' => array(
        'access' => 'product',
        'menu' => array(
            'admin' => 'Products' // @text
        ),
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
    'admin/content/product/bundle/(\d+)' => array(
        'access' => 'product_bundle_edit',
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\ProductBundle', 'editProductBundle')
        )
    ),
    'admin/content/product-class' => array(
        'access' => 'product_class',
        'menu' => array(
            'admin' => 'Product classes' // @text
        ),
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
            'controller' => array('gplcart\\core\\controllers\\backend\\ProductClassField', 'listProductClassField')
        )
    ),
    'admin/content/product-class/field/(\d+)/add' => array(
        'access' => 'product_class_edit',
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\ProductClassField', 'editProductClassField')
        )
    ),
    'admin/content/collection' => array(
        'access' => 'collection',
        'menu' => array(
            'admin' => 'Collections' // @text
        ),
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
    'admin/content/collection-item/(\d+)/edit/(\d+)' => array(
        'access' => 'collection_item_edit',
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\CollectionItem', 'editCollectionItem')
        )
    ),
    'admin/tool' => array(
        'menu' => array(
            'admin' => 'Tools' // @text
        ),
        'arguments' => array('admin/tool'),
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\Section', 'listSection')
        )
    ),
    'admin/sale' => array(
        'menu' => array(
            'admin' => 'Sales' // @text
        ),
        'arguments' => array('admin/sale'),
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\Section', 'listSection')
        )
    ),
    'admin/sale/order' => array(
        'access' => 'order',
        'menu' => array(
            'admin' => 'Orders' // @text
        ),
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
        'menu' => array(
            'admin' => 'Prices' // @text
        ),
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
        'menu' => array(
            'admin' => 'Transactions' // @text
        ),
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\Transaction', 'listTransaction')
        )
    ),
    'admin/sale/cart' => array(
        'access' => 'cart',
        'menu' => array(
            'admin' => 'Cart' // @text
        ),
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\Cart', 'listCart')
        )
    ),
    'admin/content/page' => array(
        'access' => 'page',
        'menu' => array(
            'admin' => 'Pages' // @text
        ),
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
        'menu' => array(
            'admin' => 'Files' // @text
        ),
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
        'menu' => array(
            'admin' => 'Users' // @text
        ),
        'arguments' => array('admin/user'),
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\Section', 'listSection')
        )
    ),
    'admin/user/list' => array(
        'access' => 'user',
        'menu' => array(
            'admin' => 'Users' // @text
        ),
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
        'menu' => array(
            'admin' => 'Roles' // @text
        ),
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
        'menu' => array(
            'admin' => 'Addresses' // @text
        ),
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\Address', 'listAddress')
        )
    ),
    'admin/content/field' => array(
        'access' => 'field',
        'menu' => array(
            'admin' => 'Fields' // @text
        ),
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
        'menu' => array(
            'admin' => 'Aliases' // @text
        ),
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\Alias', 'listAlias')
        )
    ),
    'admin/module' => array(
        'menu' => array(
            'admin' => 'Modules' // @text
        ),
        'arguments' => array('admin/module'),
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\Section', 'listSection')
        )
    ),
    'admin/module/list' => array(
        'access' => 'module',
        'menu' => array(
            'admin' => 'On site' // @text
        ),
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\Module', 'listModule')
        )
    ),
    'admin/settings' => array(
        'menu' => array(
            'admin' => 'Settings' // @text
        ),
        'arguments' => array('admin/settings'),
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\Section', 'listSection')
        )
    ),
    'admin/settings/common' => array(
        'access' => GC_PERM_SUPERADMIN,
        'menu' => array(
            'admin' => 'Common' // @text
        ),
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\Settings', 'editSettings')
        )
    ),
    'admin/settings/dashboard' => array(
        'menu' => array(
            'admin' => 'Dashboard' // @text
        ),
        'access' => 'dashboard_edit',
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\Dashboard', 'editDashboard')
        )
    ),
    'admin/settings/language' => array(
        'access' => 'language',
        'menu' => array(
            'admin' => 'Languages' // @text
        ),
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
        'menu' => array(
            'admin' => 'Images' // @text
        ),
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
        'menu' => array(
            'admin' => 'Currencies' // @text
        ),
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
        'menu' => array(
            'admin' => 'Countries' // @text
        ),
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
            'controller' => array('gplcart\\core\\controllers\\backend\\CountryState', 'listState')
        )
    ),
    'admin/settings/state/add/(\w+)' => array(
        'access' => 'state_add',
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\CountryState', 'editState')
        )
    ),
    'admin/settings/state/edit/(\w+)/(\d+)' => array(
        'access' => 'state_edit',
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\CountryState', 'editState')
        )
    ),
    'admin/settings/trigger' => array(
        'access' => 'trigger',
        'menu' => array(
            'admin' => 'Triggers' // @text
        ),
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
        'menu' => array(
            'admin' => 'Store' // @text
        ),
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
        'menu' => array(
            'admin' => 'Zones' // @text
        ),
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
        'menu' => array(
            'admin' => 'Reports' // @text
        ),
        'arguments' => array('admin/report'),
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\Section', 'listSection')
        )
    ),
    'admin/report/events' => array(
        'access' => 'report_events',
        'menu' => array(
            'admin' => 'Events' // @text
        ),
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\ReportEvent', 'listReportEvent')
        )
    ),
    'admin/report/status' => array(
        'access' => 'report_status',
        'menu' => array(
            'admin' => 'Status' // @text
        ),
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\ReportStatus', 'listReportStatus')
        )
    ),
    'admin/report/library' => array(
        'access' => 'report_library',
        'menu' => array(
            'admin' => 'Libraries' // @text
        ),
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\ReportLibrary', 'listReportLibrary')
        )
    ),
    'admin/report/route' => array(
        'access' => 'report',
        'menu' => array(
            'admin' => 'Routes' // @text
        ),
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\ReportRoute', 'listReportRoute')
        )
    ),
    'admin/report/payment' => array(
        'access' => 'report',
        'menu' => array(
            'admin' => 'Payment methods' // @text
        ),
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\ReportPayment', 'listReportPayment')
        )
    ),
    'admin/report/shipping' => array(
        'access' => 'report',
        'menu' => array(
            'admin' => 'Shipping methods' // @text
        ),
        'handlers' => array(
            'controller' => array('gplcart\\core\\controllers\\backend\\ReportShipping', 'listReportShipping')
        )
    )
);
