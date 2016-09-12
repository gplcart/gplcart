<?php

/**
 * Contains an array of database scheme used to create tables upon installation
 */
$tables = array();

$tables['address'] = array(
    'fields' => array(
        'address_id' => array('type' => 'int', 'length' => 10, 'auto_increment' => true, 'primary' => true),
        'state_id' => array('type' => 'int', 'length' => 10, 'not_null' => true, 'default' => 0),
        'created' => array('type' => 'int', 'length' => 10, 'not_null' => true),
        'country' => array('type' => 'varchar', 'length' => 2, 'not_null' => true, 'default' => ''),
        'city_id' => array('type' => 'varchar', 'length' => 255, 'not_null' => true, 'default' => ''),
        'address_1' => array('type' => 'varchar', 'length' => 255, 'not_null' => true, 'default' => ''),
        'address_2' => array('type' => 'varchar', 'length' => 255, 'not_null' => true, 'default' => ''),
        'phone' => array('type' => 'varchar', 'length' => 255, 'not_null' => true, 'default' => ''),
        'type' => array('type' => 'varchar', 'length' => 255, 'not_null' => true, 'default' => 'shipping'),
        'user_id' => array('type' => 'varchar', 'length' => 255, 'not_null' => true),
        'middle_name' => array('type' => 'varchar', 'length' => 255, 'not_null' => true, 'default' => ''),
        'last_name' => array('type' => 'varchar', 'length' => 255, 'not_null' => true, 'default' => ''),
        'first_name' => array('type' => 'varchar', 'length' => 255, 'not_null' => true, 'default' => ''),
        'postcode' => array('type' => 'varchar', 'length' => 50, 'not_null' => true, 'default' => ''),
        'company' => array('type' => 'varchar', 'length' => 255, 'not_null' => true, 'default' => ''),
        'fax' => array('type' => 'varchar', 'length' => 50, 'not_null' => true, 'default' => ''),
        'data' => array('type' => 'blob', 'not_null' => true, 'serialize' => true),
    )
);

$tables['alias'] = array(
    'fields' => array(
        'alias_id' => array('type' => 'int', 'length' => 10, 'auto_increment' => true, 'primary' => true),
        'id_value' => array('type' => 'int', 'length' => 10, 'not_null' => true),
        'id_key' => array('type' => 'varchar', 'length' => 50, 'not_null' => true),
        'alias' => array('type' => 'varchar', 'length' => 255, 'not_null' => true),
    )
);

$tables['wishlist'] = array(
    'fields' => array(
        'wishlist_id' => array('type' => 'int', 'length' => 10, 'auto_increment' => true, 'primary' => true),
        'product_id' => array('type' => 'int', 'length' => 10, 'not_null' => true),
        'created' => array('type' => 'int', 'length' => 10, 'not_null' => true),
        'user_id' => array('type' => 'varchar', 'length' => 255, 'not_null' => true),
    )
);

$tables['cart'] = array(
    'fields' => array(
        'cart_id' => array('type' => 'int', 'length' => 10, 'auto_increment' => true, 'primary' => true),
        'store_id' => array('type' => 'int', 'length' => 10, 'not_null' => true),
        'created' => array('type' => 'int', 'length' => 10, 'not_null' => true),
        'modified' => array('type' => 'int', 'length' => 10, 'not_null' => true, 'default' => 0),
        'product_id' => array('type' => 'int', 'length' => 10, 'not_null' => true),
        'quantity' => array('type' => 'int', 'length' => 2, 'not_null' => true, 'default' => 1),
        'order_id' => array('type' => 'int', 'length' => 10, 'not_null' => true, 'default' => 0),
        'user_id' => array('type' => 'varchar', 'length' => 255, 'not_null' => true),
        'sku' => array('type' => 'varchar', 'length' => 255, 'not_null' => true),
        'data' => array('type' => 'blob', 'not_null' => true, 'serialize' => true)
    )
);

$tables['category'] = array(
    'fields' => array(
        'category_id' => array('type' => 'int', 'length' => 10, 'auto_increment' => true, 'primary' => true),
        'weight' => array('type' => 'int', 'length' => 2, 'not_null' => true, 'default' => 0),
        'status' => array('type' => 'int', 'length' => 1, 'not_null' => true, 'default' => 0),
        'category_group_id' => array('type' => 'int', 'length' => 10, 'not_null' => true),
        'parent_id' => array('type' => 'int', 'length' => 10, 'not_null' => true, 'default' => 0),
        'meta_title' => array('type' => 'varchar', 'length' => 255, 'not_null' => true, 'default' => ''),
        'title' => array('type' => 'varchar', 'length' => 255, 'not_null' => true),
        'meta_description' => array('type' => 'varchar', 'length' => 255, 'not_null' => true, 'default' => ''),
        'description_1' => array('type' => 'text', 'not_null' => true, 'default' => ''),
        'description_2' => array('type' => 'text', 'not_null' => true, 'default' => '')
    )
);

$tables['category_group'] = array(
    'fields' => array(
        'category_group_id' => array('type' => 'int', 'length' => 10, 'auto_increment' => true, 'primary' => true),
        'store_id' => array('type' => 'int', 'length' => 10, 'not_null' => true),
        'type' => array('type' => 'varchar', 'length' => 50, 'not_null' => true, 'default' => ''),
        'title' => array('type' => 'varchar', 'length' => 255, 'not_null' => true)
    )
);

$tables['category_group_translation'] = array(
    'fields' => array(
        'translation_id' => array('type' => 'int', 'length' => 10, 'auto_increment' => true, 'primary' => true),
        'category_group_id' => array('type' => 'int', 'length' => 10, 'not_null' => true),
        'language' => array('type' => 'varchar', 'length' => 2, 'not_null' => true),
        'title' => array('type' => 'varchar', 'length' => 255, 'not_null' => true),
    )
);

$tables['category_translation'] = array(
    'fields' => array(
        'translation_id' => array('type' => 'int', 'length' => 10, 'auto_increment' => true, 'primary' => true),
        'category_id' => array('type' => 'int', 'length' => 10, 'not_null' => true),
        'language' => array('type' => 'varchar', 'length' => 4, 'not_null' => true),
        'title' => array('type' => 'varchar', 'length' => 255, 'not_null' => true, 'default' => ''),
        'meta_title' => array('type' => 'varchar', 'length' => 255, 'not_null' => true, 'default' => ''),
        'description_1' => array('type' => 'text', 'not_null' => true, 'default' => ''),
        'meta_description' => array('type' => 'text', 'not_null' => true, 'default' => ''),
        'description_2' => array('type' => 'text', 'not_null' => true, 'default' => ''),
    )
);

$tables['city'] = array(
    'fields' => array(
        'city_id' => array('type' => 'int', 'length' => 10, 'auto_increment' => true, 'primary' => true),
        'name' => array('type' => 'varchar', 'length' => 255, 'not_null' => true),
        'state_id' => array('type' => 'int', 'length' => 10, 'not_null' => true),
        'country' => array('type' => 'varchar', 'length' => 2, 'not_null' => true),
        'status' => array('type' => 'int', 'length' => 1, 'not_null' => true, 'default' => 0)
    )
);

$tables['country'] = array(
    'fields' => array(
        'code' => array('type' => 'varchar', 'length' => 2, 'primary' => true),
        'name' => array('type' => 'varchar', 'length' => 255, 'not_null' => true),
        'native_name' => array('type' => 'varchar', 'length' => 255, 'not_null' => true),
        'status' => array('type' => 'int', 'length' => 1, 'not_null' => true, 'default' => 0),
        'weight' => array('type' => 'int', 'length' => 2, 'not_null' => true, 'default' => 0),
        'format' => array('type' => 'blob', 'not_null' => true, 'serialize' => true),
    )
);

$tables['collection'] = array(
    'fields' => array(
        'collection_id' => array('type' => 'int', 'length' => 10, 'auto_increment' => true, 'primary' => true),
        'title' => array('type' => 'varchar', 'length' => 255, 'not_null' => true),
        'description' => array('type' => 'text', 'not_null' => true, 'default' => ''),
        'type' => array('type' => 'varchar', 'length' => 50, 'not_null' => true),
        'store_id' => array('type' => 'int', 'length' => 10, 'not_null' => true),
        'status' => array('type' => 'int', 'length' => 1, 'not_null' => true, 'default' => 0),
    )
);

$tables['collection_translation'] = array(
    'fields' => array(
        'translation_id' => array('type' => 'int', 'length' => 10, 'auto_increment' => true, 'primary' => true),
        'collection_id' => array('type' => 'int', 'length' => 10, 'not_null' => true),
        'language' => array('type' => 'varchar', 'length' => 4, 'not_null' => true),
        'title' => array('type' => 'varchar', 'length' => 255, 'not_null' => true)
    )
);

$tables['collection_item'] = array(
    'fields' => array(
        'collection_item_id' => array('type' => 'int', 'length' => 10, 'auto_increment' => true, 'primary' => true),
        'collection_id' => array('type' => 'int', 'length' => 10, 'not_null' => true),
        'value' => array('type' => 'int', 'length' => 10, 'not_null' => true),
        'weight' => array('type' => 'int', 'length' => 2, 'not_null' => true, 'default' => 0),
        'status' => array('type' => 'int', 'length' => 1, 'not_null' => true, 'default' => 0),
        'data' => array('type' => 'blob', 'not_null' => true, 'serialize' => true),
    )
);

$tables['field'] = array(
    'fields' => array(
        'field_id' => array('type' => 'int', 'length' => 10, 'auto_increment' => true, 'primary' => true),
        'weight' => array('type' => 'int', 'length' => 2, 'not_null' => true, 'default' => 0),
        'type' => array('type' => 'varchar', 'length' => 50, 'not_null' => true),
        'widget' => array('type' => 'varchar', 'length' => 50, 'not_null' => true),
        'title' => array('type' => 'varchar', 'length' => 255, 'not_null' => true)
    )
);

$tables['field_translation'] = array(
    'fields' => array(
        'translation_id' => array('type' => 'int', 'length' => 10, 'auto_increment' => true, 'primary' => true),
        'field_id' => array('type' => 'int', 'length' => 10, 'not_null' => true),
        'title' => array('type' => 'varchar', 'length' => 255, 'not_null' => true),
        'language' => array('type' => 'varchar', 'length' => 4, 'not_null' => true),
    )
);

$tables['field_value'] = array(
    'fields' => array(
        'field_value_id' => array('type' => 'int', 'length' => 10, 'auto_increment' => true, 'primary' => true),
        'field_id' => array('type' => 'int', 'length' => 10, 'not_null' => true),
        'weight' => array('type' => 'int', 'length' => 2, 'not_null' => true, 'default' => 0),
        'file_id' => array('type' => 'int', 'length' => 10, 'not_null' => true, 'default' => 0),
        'color' => array('type' => 'varchar', 'length' => 10, 'not_null' => true, 'default' => ''),
        'title' => array('type' => 'varchar', 'length' => 255, 'not_null' => true),
    )
);

$tables['field_value_translation'] = array(
    'fields' => array(
        'translation_id' => array('type' => 'int', 'length' => 10, 'auto_increment' => true, 'primary' => true),
        'field_value_id' => array('type' => 'int', 'length' => 10, 'not_null' => true),
        'title' => array('type' => 'varchar', 'length' => 255, 'not_null' => true),
        'language' => array('type' => 'varchar', 'length' => 4, 'not_null' => true),
    )
);

$tables['file'] = array(
    'fields' => array(
        'file_id' => array('type' => 'int', 'length' => 10, 'auto_increment' => true, 'primary' => true),
        'id_value' => array('type' => 'int', 'length' => 10, 'not_null' => true),
        'created' => array('type' => 'int', 'length' => 10, 'not_null' => true),
        'weight' => array('type' => 'int', 'length' => 2, 'not_null' => true, 'default' => 0),
        'id_key' => array('type' => 'varchar', 'length' => 50, 'not_null' => true),
        'file_type' => array('type' => 'varchar', 'length' => 50, 'not_null' => true),
        'title' => array('type' => 'varchar', 'length' => 255, 'not_null' => true, 'default' => ''),
        'mime_type' => array('type' => 'varchar', 'length' => 255, 'not_null' => true, 'default' => ''),
        'path' => array('type' => 'text', 'not_null' => true),
        'description' => array('type' => 'text', 'not_null' => true, 'default' => ''),
    )
);

$tables['file_translation'] = array(
    'fields' => array(
        'translation_id' => array('type' => 'int', 'length' => 10, 'auto_increment' => true, 'primary' => true),
        'file_id' => array('type' => 'int', 'length' => 10, 'not_null' => true),
        'language' => array('type' => 'varchar', 'length' => 4, 'not_null' => true),
        'title' => array('type' => 'varchar', 'length' => 255, 'not_null' => true),
        'description' => array('type' => 'text', 'not_null' => true),
    )
);

$tables['history'] = array(
    'fields' => array(
        'history_id' => array('type' => 'int', 'length' => 10, 'auto_increment' => true, 'primary' => true),
        'user_id' => array('type' => 'int', 'length' => 10, 'not_null' => true),
        'id_value' => array('type' => 'int', 'length' => 10, 'not_null' => true),
        'id_key' => array('type' => 'varchar', 'length' => 50, 'not_null' => true),
        'time' => array('type' => 'int', 'length' => 10, 'not_null' => true)
    )
);

$tables['log'] = array(
    'fields' => array(
        'log_id' => array('type' => 'varchar', 'length' => 50, 'primary' => true),
        'time' => array('type' => 'int', 'length' => 10, 'not_null' => true),
        'text' => array('type' => 'text', 'not_null' => true),
        'type' => array('type' => 'varchar', 'length' => 255, 'not_null' => true),
        'severity' => array('type' => 'varchar', 'length' => 255, 'not_null' => true),
        'translatable' => array('type' => 'int', 'length' => 1, 'not_null' => true, 'default' => 1),
        'data' => array('type' => 'blob', 'not_null' => true, 'serialize' => true),
    )
);

$tables['module'] = array(
    'fields' => array(
        'module_id' => array('type' => 'varchar', 'length' => 255, 'primary' => true),
        'status' => array('type' => 'int', 'length' => 1, 'not_null' => true, 'default' => 0),
        'weight' => array('type' => 'int', 'length' => 2, 'not_null' => true, 'default' => 0),
        'settings' => array('type' => 'blob', 'not_null' => true, 'serialize' => true),
    )
);

$tables['option_combination'] = array(
    'fields' => array(
        'combination_id' => array('type' => 'varchar', 'length' => 255, 'primary' => true),
        'product_id' => array('type' => 'int', 'length' => 10, 'not_null' => true),
        'stock' => array('type' => 'int', 'length' => 10, 'not_null' => true, 'default' => 0),
        'file_id' => array('type' => 'int', 'length' => 10, 'not_null' => true, 'default' => 0),
        'price' => array('type' => 'int', 'length' => 10, 'not_null' => true, 'default' => 0)
    )
);

$tables['orders'] = array(
    'fields' => array(
        'order_id' => array('type' => 'int', 'length' => 10, 'auto_increment' => true, 'primary' => true),
        'store_id' => array('type' => 'int', 'length' => 10, 'not_null' => true, 'default' => 0),
        'shipping_address' => array('type' => 'int', 'length' => 10, 'not_null' => true, 'default' => 0),
        'payment_address' => array('type' => 'int', 'length' => 10, 'not_null' => true, 'default' => 0),
        'created' => array('type' => 'int', 'length' => 10, 'not_null' => true),
        'modified' => array('type' => 'int', 'length' => 10, 'not_null' => true, 'default' => 0),
        'total' => array('type' => 'int', 'length' => 10, 'not_null' => true, 'default' => 0),
        'creator' => array('type' => 'int', 'length' => 10, 'not_null' => true, 'default' => 0),
        'currency' => array('type' => 'varchar', 'length' => 4, 'not_null' => true, 'default' => ''),
        'user_id' => array('type' => 'varchar', 'length' => 255, 'not_null' => true),
        'payment' => array('type' => 'varchar', 'length' => 255, 'not_null' => true, 'default' => ''),
        'shipping' => array('type' => 'varchar', 'length' => 255, 'not_null' => true, 'default' => ''),
        'status' => array('type' => 'varchar', 'length' => 50, 'not_null' => true, 'default' => ''),
        'comment' => array('type' => 'text', 'not_null' => true, 'default' => ''),
        'data' => array('type' => 'blob', 'not_null' => true, 'serialize' => true),
    )
);

$tables['page'] = array(
    'fields' => array(
        'page_id' => array('type' => 'int', 'length' => 10, 'auto_increment' => true, 'primary' => true),
        'user_id' => array('type' => 'int', 'length' => 10, 'not_null' => true),
        'status' => array('type' => 'int', 'length' => 1, 'not_null' => true, 'default' => 0),
        'store_id' => array('type' => 'int', 'length' => 10, 'not_null' => true, 'default' => 0),
        'category_id' => array('type' => 'int', 'length' => 10, 'not_null' => true, 'default' => 0),
        'created' => array('type' => 'int', 'length' => 10, 'not_null' => true),
        'modified' => array('type' => 'int', 'length' => 10, 'not_null' => true, 'default' => 0),
        'title' => array('type' => 'varchar', 'length' => 255, 'not_null' => true),
        'meta_title' => array('type' => 'varchar', 'length' => 255, 'not_null' => true, 'default' => ''),
        'meta_description' => array('type' => 'varchar', 'length' => 255, 'not_null' => true, 'default' => ''),
        'description' => array('type' => 'text', 'not_null' => true)
    )
);

$tables['page_translation'] = array(
    'fields' => array(
        'translation_id' => array('type' => 'int', 'length' => 10, 'auto_increment' => true, 'primary' => true),
        'page_id' => array('type' => 'int', 'length' => 10, 'not_null' => true),
        'language' => array('type' => 'varchar', 'length' => 4, 'not_null' => true),
        'title' => array('type' => 'varchar', 'length' => 255, 'not_null' => true, 'default' => ''),
        'meta_description' => array('type' => 'varchar', 'length' => 255, 'not_null' => true, 'default' => ''),
        'meta_title' => array('type' => 'varchar', 'length' => 255, 'not_null' => true, 'default' => ''),
        'description' => array('type' => 'text', 'not_null' => true, 'default' => ''),
    )
);

$tables['price_rule'] = array(
    'fields' => array(
        'price_rule_id' => array('type' => 'int', 'length' => 10, 'auto_increment' => true, 'primary' => true),
        'value' => array('type' => 'int', 'length' => 10, 'not_null' => true),
        'status' => array('type' => 'int', 'length' => 1, 'not_null' => true, 'default' => 0),
        'weight' => array('type' => 'int', 'length' => 2, 'not_null' => true, 'default' => 0),
        'used' => array('type' => 'int', 'length' => 10, 'not_null' => true, 'default' => 0),
        'name' => array('type' => 'varchar', 'length' => 255, 'not_null' => true),
        'trigger_id' => array('type' => 'varchar', 'length' => 255, 'not_null' => true),
        'code' => array('type' => 'varchar', 'length' => 255, 'not_null' => true),
        'value_type' => array('type' => 'varchar', 'length' => 255, 'not_null' => true),
        'currency' => array('type' => 'varchar', 'length' => 4, 'not_null' => true)
    )
);

$tables['product'] = array(
    'fields' => array(
        'product_id' => array('type' => 'int', 'length' => 10, 'auto_increment' => true, 'primary' => true),
        'status' => array('type' => 'int', 'length' => 10, 'not_null' => true, 'default' => 1),
        'subtract' => array('type' => 'int', 'length' => 10, 'not_null' => true, 'default' => 1),
        'product_class_id' => array('type' => 'int', 'length' => 10, 'not_null' => true, 'default' => 0),
        'price' => array('type' => 'int', 'length' => 10, 'not_null' => true, 'default' => 0),
        'created' => array('type' => 'int', 'length' => 10, 'not_null' => true),
        'modified' => array('type' => 'int', 'length' => 10, 'not_null' => true, 'default' => 0),
        'stock' => array('type' => 'int', 'length' => 10, 'not_null' => true, 'default' => 0),
        'store_id' => array('type' => 'int', 'length' => 10, 'not_null' => true, 'default' => 0),
        'brand_category_id' => array('type' => 'int', 'length' => 10, 'not_null' => true, 'default' => 0),
        'user_id' => array('type' => 'int', 'length' => 10, 'not_null' => true),
        'category_id' => array('type' => 'int', 'length' => 10, 'not_null' => true, 'default' => 0),
        'length' => array('type' => 'int', 'length' => 10, 'not_null' => true, 'default' => 0),
        'width' => array('type' => 'int', 'length' => 10, 'not_null' => true, 'default' => 0),
        'height' => array('type' => 'int', 'length' => 10, 'not_null' => true, 'default' => 0),
        'weight' => array('type' => 'int', 'length' => 10, 'not_null' => true, 'default' => 0),
        'meta_title' => array('type' => 'varchar', 'length' => 255, 'not_null' => true, 'default' => ''),
        'meta_description' => array('type' => 'varchar', 'length' => 255, 'not_null' => true, 'default' => ''),
        'currency' => array('type' => 'varchar', 'length' => 4, 'not_null' => true),
        'volume_unit' => array('type' => 'varchar', 'length' => 2, 'not_null' => true, 'default' => 'mm'),
        'weight_unit' => array('type' => 'varchar', 'length' => 2, 'not_null' => true, 'default' => 'g'),
        'title' => array('type' => 'varchar', 'length' => 255, 'not_null' => true),
        'description' => array('type' => 'text', 'not_null' => true, 'default' => '')
    )
);

$tables['product_class'] = array(
    'fields' => array(
        'product_class_id' => array('type' => 'int', 'length' => 10, 'auto_increment' => true, 'primary' => true),
        'status' => array('type' => 'int', 'length' => 1, 'not_null' => true, 'default' => 0),
        'title' => array('type' => 'varchar', 'length' => 255, 'not_null' => true),
    )
);

$tables['product_class_field'] = array(
    'fields' => array(
        'product_class_field_id' => array('type' => 'int', 'length' => 10, 'auto_increment' => true, 'primary' => true),
        'product_class_id' => array('type' => 'int', 'length' => 10, 'not_null' => true),
        'field_id' => array('type' => 'int', 'length' => 10, 'not_null' => true),
        'required' => array('type' => 'int', 'length' => 1, 'not_null' => true, 'default' => 0),
        'multiple' => array('type' => 'int', 'length' => 1, 'not_null' => true, 'default' => 0),
        'weight' => array('type' => 'int', 'length' => 2, 'not_null' => true, 'default' => 0),
    )
);

$tables['product_field'] = array(
    'fields' => array(
        'product_field_id' => array('type' => 'int', 'length' => 10, 'auto_increment' => true, 'primary' => true),
        'field_id' => array('type' => 'int', 'length' => 10, 'not_null' => true),
        'field_value_id' => array('type' => 'int', 'length' => 10, 'not_null' => true),
        'product_id' => array('type' => 'int', 'length' => 10, 'not_null' => true),
        'type' => array('type' => 'varchar', 'length' => 50, 'not_null' => true),
    )
);

$tables['product_sku'] = array(
    'fields' => array(
        'product_sku_id' => array('type' => 'int', 'length' => 10, 'auto_increment' => true, 'primary' => true),
        'sku' => array('type' => 'varchar', 'length' => 255, 'not_null' => true),
        'product_id' => array('type' => 'int', 'length' => 10, 'not_null' => true),
        'combination_id' => array('type' => 'varchar', 'length' => 255, 'not_null' => true, 'default' => ''),
        'store_id' => array('type' => 'int', 'length' => 10, 'not_null' => true)
    )
);

$tables['product_translation'] = array(
    'fields' => array(
        'translation_id' => array('type' => 'int', 'length' => 10, 'auto_increment' => true, 'primary' => true),
        'product_id' => array('type' => 'int', 'length' => 10, 'not_null' => true),
        'language' => array('type' => 'varchar', 'length' => 4, 'not_null' => true),
        'title' => array('type' => 'varchar', 'length' => 255, 'not_null' => true, 'default' => ''),
        'meta_description' => array('type' => 'varchar', 'length' => 255, 'not_null' => true, 'default' => ''),
        'meta_title' => array('type' => 'varchar', 'length' => 255, 'not_null' => true, 'default' => ''),
        'description' => array('type' => 'text', 'not_null' => true, 'default' => ''),
    )
);

$tables['product_related'] = array(
    'fields' => array(
        'product_id' => array('type' => 'int', 'length' => 10, 'primary' => true),
        'related_product_id' => array('type' => 'int', 'length' => 10, 'not_null' => true),
    )
);

$tables['review'] = array(
    'fields' => array(
        'review_id' => array('type' => 'int', 'length' => 10, 'auto_increment' => true, 'primary' => true),
        'user_id' => array('type' => 'int', 'length' => 10, 'not_null' => true),
        'status' => array('type' => 'int', 'length' => 1, 'not_null' => true, 'default' => 0),
        'created' => array('type' => 'int', 'length' => 10, 'not_null' => true),
        'modified' => array('type' => 'int', 'length' => 10, 'not_null' => true, 'default' => 0),
        'product_id' => array('type' => 'int', 'length' => 10, 'not_null' => true),
        'text' => array('type' => 'text', 'not_null' => true)
    )
);

$tables['rating'] = array(
    'fields' => array(
        'product_id' => array('type' => 'int', 'length' => 10, 'primary' => true),
        'votes' => array('type' => 'int', 'length' => 10, 'not_null' => true),
        'rating' => array('type' => 'float', 'default' => 0),
    )
);

$tables['rating_user'] = array(
    'fields' => array(
        'id' => array('type' => 'int', 'length' => 10, 'auto_increment' => true, 'primary' => true),
        'product_id' => array('type' => 'int', 'length' => 10, 'not_null' => true),
        'user_id' => array('type' => 'varchar', 'length' => 255, 'not_null' => true),
        'rating' => array('type' => 'float', 'default' => 0),
    )
);

$tables['role'] = array(
    'fields' => array(
        'role_id' => array('type' => 'int', 'length' => 10, 'auto_increment' => true, 'primary' => true),
        'status' => array('type' => 'int', 'length' => 1, 'not_null' => true, 'default' => 0),
        'name' => array('type' => 'varchar', 'length' => 255, 'not_null' => true),
        'permissions' => array('type' => 'blob', 'not_null' => true, 'serialize' => true),
    )
);

$tables['settings'] = array(
    'fields' => array(
        'id' => array('type' => 'varchar', 'length' => 255, 'primary' => true),
        'value' => array('type' => 'blob', 'not_null' => true),
        'created' => array('type' => 'int', 'length' => 10, 'not_null' => true),
        'serialized' => array('type' => 'int', 'length' => 1, 'not_null' => true, 'default' => 0),
    )
);

$tables['state'] = array(
    'fields' => array(
        'state_id' => array('type' => 'int', 'length' => 10, 'auto_increment' => true, 'primary' => true),
        'status' => array('type' => 'int', 'length' => 1, 'not_null' => true, 'default' => 0),
        'code' => array('type' => 'varchar', 'length' => 255, 'not_null' => true),
        'name' => array('type' => 'varchar', 'length' => 255, 'not_null' => true),
        'country' => array('type' => 'varchar', 'length' => 2, 'not_null' => true)
    )
);

$tables['store'] = array(
    'fields' => array(
        'store_id' => array('type' => 'int', 'length' => 10, 'auto_increment' => true, 'primary' => true),
        'status' => array('type' => 'int', 'length' => 1, 'not_null' => true, 'default' => 0),
        'domain' => array('type' => 'varchar', 'length' => 255, 'not_null' => true),
        'name' => array('type' => 'varchar', 'length' => 255, 'not_null' => true),
        'basepath' => array('type' => 'varchar', 'length' => 50, 'not_null' => true, 'default' => ''),
        'created' => array('type' => 'int', 'length' => 10, 'not_null' => true),
        'modified' => array('type' => 'int', 'length' => 10, 'not_null' => true, 'default' => 0),
        'data' => array('type' => 'blob', 'not_null' => true, 'serialize' => true),
    )
);

$tables['transaction'] = array(
    'fields' => array(
        'transaction_id' => array('type' => 'int', 'length' => 10, 'auto_increment' => true, 'primary' => true),
        'order_id' => array('type' => 'int', 'length' => 10, 'not_null' => true),
        'created' => array('type' => 'int', 'length' => 10, 'not_null' => true),
        'payment_service' => array('type' => 'varchar', 'length' => 255, 'not_null' => true),
        'service_transaction_id' => array('type' => 'varchar', 'length' => 255, 'not_null' => true, 'default' => ''),
        'data' => array('type' => 'blob', 'not_null' => true, 'serialize' => true),
    )
);

$tables['triggers'] = array(
    'fields' => array(
        'trigger_id' => array('type' => 'int', 'length' => 10, 'auto_increment' => true, 'primary' => true),
        'name' => array('type' => 'varchar', 'length' => 255, 'not_null' => true),
        'status' => array('type' => 'int', 'length' => 1, 'not_null' => true, 'default' => 0),
        'store_id' => array('type' => 'int', 'length' => 10, 'not_null' => true),
        'weight' => array('type' => 'int', 'length' => 2, 'not_null' => true, 'default' => 0),
        'data' => array('type' => 'blob', 'not_null' => true, 'serialize' => true),
    )
);

$tables['user'] = array(
    'fields' => array(
        'user_id' => array('type' => 'int', 'length' => 10, 'auto_increment' => true, 'primary' => true),
        'created' => array('type' => 'int', 'length' => 10, 'not_null' => true),
        'modified' => array('type' => 'int', 'length' => 10, 'not_null' => true, 'default' => 0),
        'status' => array('type' => 'int', 'length' => 1, 'not_null' => true, 'default' => 1),
        'role_id' => array('type' => 'int', 'length' => 10, 'not_null' => true, 'default' => 0),
        'store_id' => array('type' => 'int', 'length' => 10, 'not_null' => true),
        'email' => array('type' => 'varchar', 'length' => 255, 'not_null' => true),
        'name' => array('type' => 'varchar', 'length' => 255, 'not_null' => true),
        'hash' => array('type' => 'text', 'not_null' => true),
        'data' => array('type' => 'blob', 'not_null' => true, 'serialize' => true),
    )
);

$tables['zone'] = array(
    'fields' => array(
        'zone_id' => array('type' => 'int', 'length' => 10, 'auto_increment' => true, 'primary' => true),
        'state_id' => array('type' => 'int', 'length' => 10, 'not_null' => true),
        'status' => array('type' => 'int', 'length' => 1, 'not_null' => true, 'default' => 1),
        'name' => array('type' => 'varchar', 'length' => 255, 'not_null' => true),
        'country' => array('type' => 'varchar', 'length' => 2, 'not_null' => true),
    )
);

$tables['search_index'] = array(
    'fields' => array(
        'search_index_id' => array('type' => 'int', 'length' => 10, 'auto_increment' => true, 'primary' => true),
        'id_value' => array('type' => 'int', 'length' => 10, 'not_null' => true),
        'id_key' => array('type' => 'varchar', 'length' => 50, 'not_null' => true),
        'language' => array('type' => 'varchar', 'length' => 4, 'not_null' => true),
        'text' => array('type' => 'longtext', 'not_null' => true),
    ),
    'engine' => 'MyISAM',
    'alter' => 'ADD FULLTEXT(text)',
);

return $tables;
