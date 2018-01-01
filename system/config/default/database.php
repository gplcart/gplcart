<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
return array(
    'address' => array(
        'fields' => array(
            'address_id' => array('type' => 'integer', 'length' => 10, 'auto_increment' => true, 'primary' => true),
            'state_id' => array('type' => 'integer', 'length' => 10, 'not_null' => true, 'default' => 0),
            'created' => array('type' => 'integer', 'length' => 10, 'not_null' => true),
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
    ),
    'alias' => array(
        'fields' => array(
            'alias_id' => array('type' => 'integer', 'length' => 10, 'auto_increment' => true, 'primary' => true),
            'entity_id' => array('type' => 'integer', 'length' => 10, 'not_null' => true),
            'entity' => array('type' => 'varchar', 'length' => 50, 'not_null' => true),
            'alias' => array('type' => 'varchar', 'length' => 255, 'not_null' => true),
        )
    ),
    'bookmark' => array(
        'fields' => array(
            'bookmark_id' => array('type' => 'integer', 'length' => 10, 'auto_increment' => true, 'primary' => true),
            'user_id' => array('type' => 'integer', 'length' => 10, 'not_null' => true),
            'path' => array('type' => 'varchar', 'length' => 255, 'not_null' => true),
            'title' => array('type' => 'varchar', 'length' => 255, 'not_null' => true),
            'created' => array('type' => 'integer', 'length' => 10, 'not_null' => true)
        )
    ),
    'cart' => array(
        'fields' => array(
            'cart_id' => array('type' => 'integer', 'length' => 10, 'auto_increment' => true, 'primary' => true),
            'store_id' => array('type' => 'integer', 'length' => 10, 'not_null' => true),
            'created' => array('type' => 'integer', 'length' => 10, 'not_null' => true),
            'modified' => array('type' => 'integer', 'length' => 10, 'not_null' => true, 'default' => 0),
            'product_id' => array('type' => 'integer', 'length' => 10, 'not_null' => true),
            'quantity' => array('type' => 'integer', 'length' => 2, 'not_null' => true, 'default' => 1),
            'order_id' => array('type' => 'integer', 'length' => 10, 'not_null' => true, 'default' => 0),
            'user_id' => array('type' => 'varchar', 'length' => 255, 'not_null' => true),
            'sku' => array('type' => 'varchar', 'length' => 255, 'not_null' => true),
            'data' => array('type' => 'blob', 'not_null' => true, 'serialize' => true)
        )
    ),
    'category' => array(
        'fields' => array(
            'category_id' => array('type' => 'integer', 'length' => 10, 'auto_increment' => true, 'primary' => true),
            'weight' => array('type' => 'integer', 'length' => 2, 'not_null' => true, 'default' => 0),
            'user_id' => array('type' => 'integer', 'length' => 10, 'not_null' => true),
            'status' => array('type' => 'integer', 'length' => 1, 'not_null' => true, 'default' => 0),
            'category_group_id' => array('type' => 'integer', 'length' => 10, 'not_null' => true),
            'parent_id' => array('type' => 'integer', 'length' => 10, 'not_null' => true, 'default' => 0),
            'meta_title' => array('type' => 'varchar', 'length' => 255, 'not_null' => true, 'default' => ''),
            'title' => array('type' => 'varchar', 'length' => 255, 'not_null' => true),
            'meta_description' => array('type' => 'varchar', 'length' => 255, 'not_null' => true, 'default' => ''),
            'description_1' => array('type' => 'text', 'not_null' => true, 'default' => ''),
            'description_2' => array('type' => 'text', 'not_null' => true, 'default' => '')
        )
    ),
    'category_group' => array(
        'fields' => array(
            'category_group_id' => array('type' => 'integer', 'length' => 10, 'auto_increment' => true, 'primary' => true),
            'store_id' => array('type' => 'integer', 'length' => 10, 'not_null' => true),
            'type' => array('type' => 'varchar', 'length' => 50, 'not_null' => true, 'default' => ''),
            'title' => array('type' => 'varchar', 'length' => 255, 'not_null' => true)
        )
    ),
    'category_group_translation' => array(
        'fields' => array(
            'translation_id' => array('type' => 'integer', 'length' => 10, 'auto_increment' => true, 'primary' => true),
            'category_group_id' => array('type' => 'integer', 'length' => 10, 'not_null' => true),
            'language' => array('type' => 'varchar', 'length' => 2, 'not_null' => true),
            'title' => array('type' => 'varchar', 'length' => 255, 'not_null' => true),
        )
    ),
    'category_translation' => array(
        'fields' => array(
            'translation_id' => array('type' => 'integer', 'length' => 10, 'auto_increment' => true, 'primary' => true),
            'category_id' => array('type' => 'integer', 'length' => 10, 'not_null' => true),
            'language' => array('type' => 'varchar', 'length' => 4, 'not_null' => true),
            'title' => array('type' => 'varchar', 'length' => 255, 'not_null' => true, 'default' => ''),
            'meta_title' => array('type' => 'varchar', 'length' => 255, 'not_null' => true, 'default' => ''),
            'description_1' => array('type' => 'text', 'not_null' => true, 'default' => ''),
            'meta_description' => array('type' => 'text', 'not_null' => true, 'default' => ''),
            'description_2' => array('type' => 'text', 'not_null' => true, 'default' => ''),
        )
    ),
    'city' => array(
        'fields' => array(
            'city_id' => array('type' => 'integer', 'length' => 10, 'auto_increment' => true, 'primary' => true),
            'name' => array('type' => 'varchar', 'length' => 255, 'not_null' => true),
            'state_id' => array('type' => 'integer', 'length' => 10, 'not_null' => true),
            'country' => array('type' => 'varchar', 'length' => 2, 'not_null' => true),
            'status' => array('type' => 'integer', 'length' => 1, 'not_null' => true, 'default' => 0),
            'zone_id' => array('type' => 'integer', 'length' => 10, 'not_null' => true, 'default' => 0)
        )
    ),
    'country' => array(
        'fields' => array(
            'code' => array('type' => 'varchar', 'length' => 2, 'primary' => true),
            'name' => array('type' => 'varchar', 'length' => 255, 'not_null' => true),
            'native_name' => array('type' => 'varchar', 'length' => 255, 'not_null' => true),
            'status' => array('type' => 'integer', 'length' => 1, 'not_null' => true, 'default' => 0),
            'weight' => array('type' => 'integer', 'length' => 2, 'not_null' => true, 'default' => 0),
            'format' => array('type' => 'blob', 'not_null' => true, 'serialize' => true),
            'zone_id' => array('type' => 'integer', 'length' => 10, 'not_null' => true, 'default' => 0),
            'template' => array('type' => 'text', 'not_null' => true, 'default' => '')
        )
    ),
    'collection' => array(
        'fields' => array(
            'collection_id' => array('type' => 'integer', 'length' => 10, 'auto_increment' => true, 'primary' => true),
            'title' => array('type' => 'varchar', 'length' => 255, 'not_null' => true),
            'description' => array('type' => 'text', 'not_null' => true, 'default' => ''),
            'type' => array('type' => 'varchar', 'length' => 50, 'not_null' => true),
            'store_id' => array('type' => 'integer', 'length' => 10, 'not_null' => true),
            'status' => array('type' => 'integer', 'length' => 1, 'not_null' => true, 'default' => 0),
        )
    ),
    'collection_translation' => array(
        'fields' => array(
            'translation_id' => array('type' => 'integer', 'length' => 10, 'auto_increment' => true, 'primary' => true),
            'collection_id' => array('type' => 'integer', 'length' => 10, 'not_null' => true),
            'language' => array('type' => 'varchar', 'length' => 4, 'not_null' => true),
            'title' => array('type' => 'varchar', 'length' => 255, 'not_null' => true)
        )
    ),
    'collection_item' => array(
        'fields' => array(
            'collection_item_id' => array('type' => 'integer', 'length' => 10, 'auto_increment' => true, 'primary' => true),
            'collection_id' => array('type' => 'integer', 'length' => 10, 'not_null' => true),
            'value' => array('type' => 'integer', 'length' => 10, 'not_null' => true),
            'weight' => array('type' => 'integer', 'length' => 2, 'not_null' => true, 'default' => 0),
            'status' => array('type' => 'integer', 'length' => 1, 'not_null' => true, 'default' => 0),
            'data' => array('type' => 'blob', 'not_null' => true, 'serialize' => true),
        )
    ),
    'dashboard' => array(
        'fields' => array(
            'dashboard_id' => array('type' => 'integer', 'length' => 10, 'auto_increment' => true, 'primary' => true),
            'user_id' => array('type' => 'integer', 'length' => 10, 'not_null' => true),
            'data' => array('type' => 'blob', 'not_null' => true, 'serialize' => true)
        )
    ),
    'field' => array(
        'fields' => array(
            'field_id' => array('type' => 'integer', 'length' => 10, 'auto_increment' => true, 'primary' => true),
            'weight' => array('type' => 'integer', 'length' => 2, 'not_null' => true, 'default' => 0),
            'type' => array('type' => 'varchar', 'length' => 50, 'not_null' => true),
            'widget' => array('type' => 'varchar', 'length' => 50, 'not_null' => true),
            'title' => array('type' => 'varchar', 'length' => 255, 'not_null' => true)
        )
    ),
    'field_translation' => array(
        'fields' => array(
            'translation_id' => array('type' => 'integer', 'length' => 10, 'auto_increment' => true, 'primary' => true),
            'field_id' => array('type' => 'integer', 'length' => 10, 'not_null' => true),
            'title' => array('type' => 'varchar', 'length' => 255, 'not_null' => true),
            'language' => array('type' => 'varchar', 'length' => 4, 'not_null' => true),
        )
    ),
    'field_value' => array(
        'fields' => array(
            'field_value_id' => array('type' => 'integer', 'length' => 10, 'auto_increment' => true, 'primary' => true),
            'field_id' => array('type' => 'integer', 'length' => 10, 'not_null' => true),
            'weight' => array('type' => 'integer', 'length' => 2, 'not_null' => true, 'default' => 0),
            'file_id' => array('type' => 'integer', 'length' => 10, 'not_null' => true, 'default' => 0),
            'color' => array('type' => 'varchar', 'length' => 10, 'not_null' => true, 'default' => ''),
            'title' => array('type' => 'varchar', 'length' => 255, 'not_null' => true),
        )
    ),
    'field_value_translation' => array(
        'fields' => array(
            'translation_id' => array('type' => 'integer', 'length' => 10, 'auto_increment' => true, 'primary' => true),
            'field_value_id' => array('type' => 'integer', 'length' => 10, 'not_null' => true),
            'title' => array('type' => 'varchar', 'length' => 255, 'not_null' => true),
            'language' => array('type' => 'varchar', 'length' => 4, 'not_null' => true),
        )
    ),
    'file' => array(
        'fields' => array(
            'file_id' => array('type' => 'integer', 'length' => 10, 'auto_increment' => true, 'primary' => true),
            'entity_id' => array('type' => 'integer', 'length' => 10, 'not_null' => true, 'default' => 0),
            'created' => array('type' => 'integer', 'length' => 10, 'not_null' => true),
            'modified' => array('type' => 'integer', 'length' => 10, 'not_null' => true, 'default' => 0),
            'weight' => array('type' => 'integer', 'length' => 2, 'not_null' => true, 'default' => 0),
            'entity' => array('type' => 'varchar', 'length' => 50, 'not_null' => true, 'default' => 'file'),
            'file_type' => array('type' => 'varchar', 'length' => 50, 'not_null' => true),
            'title' => array('type' => 'varchar', 'length' => 255, 'not_null' => true, 'default' => ''),
            'mime_type' => array('type' => 'varchar', 'length' => 255, 'not_null' => true, 'default' => ''),
            'path' => array('type' => 'text', 'not_null' => true),
            'description' => array('type' => 'text', 'not_null' => true, 'default' => ''),
        )
    ),
    'file_translation' => array(
        'fields' => array(
            'translation_id' => array('type' => 'integer', 'length' => 10, 'auto_increment' => true, 'primary' => true),
            'file_id' => array('type' => 'integer', 'length' => 10, 'not_null' => true),
            'language' => array('type' => 'varchar', 'length' => 4, 'not_null' => true),
            'title' => array('type' => 'varchar', 'length' => 255, 'not_null' => true),
            'description' => array('type' => 'text', 'not_null' => true),
        )
    ),
    'history' => array(
        'fields' => array(
            'history_id' => array('type' => 'integer', 'length' => 10, 'auto_increment' => true, 'primary' => true),
            'user_id' => array('type' => 'integer', 'length' => 10, 'not_null' => true),
            'entity_id' => array('type' => 'integer', 'length' => 10, 'not_null' => true),
            'entity' => array('type' => 'varchar', 'length' => 50, 'not_null' => true),
            'created' => array('type' => 'integer', 'length' => 10, 'not_null' => true)
        )
    ),
    'log' => array(
        'fields' => array(
            'log_id' => array('type' => 'varchar', 'length' => 50, 'primary' => true),
            'time' => array('type' => 'integer', 'length' => 10, 'not_null' => true),
            'text' => array('type' => 'text', 'not_null' => true),
            'type' => array('type' => 'varchar', 'length' => 255, 'not_null' => true),
            'severity' => array('type' => 'varchar', 'length' => 255, 'not_null' => true),
            'translatable' => array('type' => 'integer', 'length' => 1, 'not_null' => true, 'default' => 1),
            'data' => array('type' => 'blob', 'not_null' => true, 'serialize' => true),
        )
    ),
    'order_log' => array(
        'fields' => array(
            'order_log_id' => array('type' => 'integer', 'length' => 10, 'auto_increment' => true, 'primary' => true),
            'user_id' => array('type' => 'integer', 'length' => 10, 'not_null' => true),
            'order_id' => array('type' => 'integer', 'length' => 10, 'not_null' => true),
            'created' => array('type' => 'integer', 'length' => 10, 'not_null' => true),
            'text' => array('type' => 'varchar', 'length' => 255, 'not_null' => true),
            'data' => array('type' => 'blob', 'not_null' => true, 'serialize' => true),
        )
    ),
    'module' => array(
        'fields' => array(
            'module_id' => array('type' => 'varchar', 'length' => 255, 'primary' => true),
            'status' => array('type' => 'integer', 'length' => 1, 'not_null' => true, 'default' => 0),
            'weight' => array('type' => 'integer', 'length' => 2, 'not_null' => true, 'default' => 0),
            'settings' => array('type' => 'blob', 'not_null' => true, 'serialize' => true),
            'created' => array('type' => 'integer', 'length' => 10, 'not_null' => true),
            'modified' => array('type' => 'integer', 'length' => 10, 'not_null' => true, 'default' => 0)
        )
    ),
    'orders' => array(
        'fields' => array(
            'order_id' => array('type' => 'integer', 'length' => 10, 'auto_increment' => true, 'primary' => true),
            'store_id' => array('type' => 'integer', 'length' => 10, 'not_null' => true, 'default' => 0),
            'shipping_address' => array('type' => 'integer', 'length' => 10, 'not_null' => true, 'default' => 0),
            'payment_address' => array('type' => 'integer', 'length' => 10, 'not_null' => true, 'default' => 0),
            'created' => array('type' => 'integer', 'length' => 10, 'not_null' => true),
            'modified' => array('type' => 'integer', 'length' => 10, 'not_null' => true, 'default' => 0),
            'total' => array('type' => 'integer', 'length' => 10, 'not_null' => true, 'default' => 0),
            'creator' => array('type' => 'integer', 'length' => 10, 'not_null' => true, 'default' => 0),
            'transaction_id' => array('type' => 'integer', 'length' => 10, 'not_null' => true, 'default' => 0),
            'currency' => array('type' => 'varchar', 'length' => 4, 'not_null' => true),
            'user_id' => array('type' => 'varchar', 'length' => 255, 'not_null' => true),
            'payment' => array('type' => 'varchar', 'length' => 255, 'not_null' => true),
            'shipping' => array('type' => 'varchar', 'length' => 255, 'not_null' => true),
            'tracking_number' => array('type' => 'varchar', 'length' => 255, 'not_null' => true, 'default' => ''),
            'status' => array('type' => 'varchar', 'length' => 50, 'not_null' => true),
            'comment' => array('type' => 'text', 'not_null' => true, 'default' => ''),
            'volume' => array('type' => 'float', 'default' => 0),
            'weight' => array('type' => 'float', 'default' => 0),
            'size_unit' => array('type' => 'varchar', 'length' => 2, 'not_null' => true, 'default' => 'mm'),
            'weight_unit' => array('type' => 'varchar', 'length' => 2, 'not_null' => true, 'default' => 'g'),
            'data' => array('type' => 'blob', 'not_null' => true, 'serialize' => true),
        )
    ),
    'page' => array(
        'fields' => array(
            'page_id' => array('type' => 'integer', 'length' => 10, 'auto_increment' => true, 'primary' => true),
            'related_page_id' => array('type' => 'integer', 'length' => 10, 'not_null' => true, 'default' => 0), // Reserved
            'user_id' => array('type' => 'integer', 'length' => 10, 'not_null' => true),
            'status' => array('type' => 'integer', 'length' => 1, 'not_null' => true, 'default' => 0),
            'store_id' => array('type' => 'integer', 'length' => 10, 'not_null' => true, 'default' => 0),
            'category_id' => array('type' => 'integer', 'length' => 10, 'not_null' => true, 'default' => 0),
            'created' => array('type' => 'integer', 'length' => 10, 'not_null' => true),
            'modified' => array('type' => 'integer', 'length' => 10, 'not_null' => true, 'default' => 0),
            'blog_post' => array('type' => 'integer', 'length' => 1, 'not_null' => true, 'default' => 0),
            'title' => array('type' => 'varchar', 'length' => 255, 'not_null' => true),
            'meta_title' => array('type' => 'varchar', 'length' => 255, 'not_null' => true, 'default' => ''),
            'meta_description' => array('type' => 'varchar', 'length' => 255, 'not_null' => true, 'default' => ''),
            'description' => array('type' => 'text', 'not_null' => true)
        )
    ),
    'page_translation' => array(
        'fields' => array(
            'translation_id' => array('type' => 'integer', 'length' => 10, 'auto_increment' => true, 'primary' => true),
            'page_id' => array('type' => 'integer', 'length' => 10, 'not_null' => true),
            'language' => array('type' => 'varchar', 'length' => 4, 'not_null' => true),
            'title' => array('type' => 'varchar', 'length' => 255, 'not_null' => true, 'default' => ''),
            'meta_description' => array('type' => 'varchar', 'length' => 255, 'not_null' => true, 'default' => ''),
            'meta_title' => array('type' => 'varchar', 'length' => 255, 'not_null' => true, 'default' => ''),
            'description' => array('type' => 'text', 'not_null' => true, 'default' => ''),
        )
    ),
    'price_rule' => array(
        'fields' => array(
            'price_rule_id' => array('type' => 'integer', 'length' => 10, 'auto_increment' => true, 'primary' => true),
            'value' => array('type' => 'integer', 'length' => 10, 'not_null' => true),
            'status' => array('type' => 'integer', 'length' => 1, 'not_null' => true, 'default' => 0),
            'weight' => array('type' => 'integer', 'length' => 2, 'not_null' => true, 'default' => 0),
            'used' => array('type' => 'integer', 'length' => 10, 'not_null' => true, 'default' => 0),
            'name' => array('type' => 'varchar', 'length' => 255, 'not_null' => true),
            'trigger_id' => array('type' => 'varchar', 'length' => 255, 'not_null' => true),
            'code' => array('type' => 'varchar', 'length' => 255, 'not_null' => true),
            'value_type' => array('type' => 'varchar', 'length' => 255, 'not_null' => true),
            'currency' => array('type' => 'varchar', 'length' => 4, 'not_null' => true),
            'created' => array('type' => 'integer', 'length' => 10, 'not_null' => true),
            'modified' => array('type' => 'integer', 'length' => 10, 'not_null' => true, 'default' => 0)
        )
    ),
    'product' => array(
        'fields' => array(
            'product_id' => array('type' => 'integer', 'length' => 10, 'auto_increment' => true, 'primary' => true),
            'status' => array('type' => 'integer', 'length' => 1, 'not_null' => true, 'default' => 1),
            'subtract' => array('type' => 'integer', 'length' => 1, 'not_null' => true, 'default' => 1),
            'product_class_id' => array('type' => 'integer', 'length' => 10, 'not_null' => true, 'default' => 0),
            'created' => array('type' => 'integer', 'length' => 10, 'not_null' => true),
            'modified' => array('type' => 'integer', 'length' => 10, 'not_null' => true, 'default' => 0),
            'store_id' => array('type' => 'integer', 'length' => 10, 'not_null' => true, 'default' => 0),
            'brand_category_id' => array('type' => 'integer', 'length' => 10, 'not_null' => true, 'default' => 0),
            'user_id' => array('type' => 'integer', 'length' => 10, 'not_null' => true),
            'category_id' => array('type' => 'integer', 'length' => 10, 'not_null' => true, 'default' => 0),
            'length' => array('type' => 'float', 'not_null' => true, 'default' => 0),
            'width' => array('type' => 'float', 'not_null' => true, 'default' => 0),
            'height' => array('type' => 'float', 'not_null' => true, 'default' => 0),
            'weight' => array('type' => 'float', 'not_null' => true, 'default' => 0),
            'meta_title' => array('type' => 'varchar', 'length' => 255, 'not_null' => true, 'default' => ''),
            'meta_description' => array('type' => 'varchar', 'length' => 255, 'not_null' => true, 'default' => ''),
            'currency' => array('type' => 'varchar', 'length' => 4, 'not_null' => true),
            'size_unit' => array('type' => 'varchar', 'length' => 2, 'not_null' => true, 'default' => 'mm'),
            'weight_unit' => array('type' => 'varchar', 'length' => 2, 'not_null' => true, 'default' => 'g'),
            'title' => array('type' => 'varchar', 'length' => 255, 'not_null' => true),
            'description' => array('type' => 'text', 'not_null' => true, 'default' => '')
        )
    ),
    'product_class' => array(
        'fields' => array(
            'product_class_id' => array('type' => 'integer', 'length' => 10, 'auto_increment' => true, 'primary' => true),
            'status' => array('type' => 'integer', 'length' => 1, 'not_null' => true, 'default' => 0),
            'title' => array('type' => 'varchar', 'length' => 255, 'not_null' => true),
        )
    ),
    'product_class_field' => array(
        'fields' => array(
            'product_class_field_id' => array('type' => 'integer', 'length' => 10, 'auto_increment' => true, 'primary' => true),
            'product_class_id' => array('type' => 'integer', 'length' => 10, 'not_null' => true),
            'field_id' => array('type' => 'integer', 'length' => 10, 'not_null' => true),
            'required' => array('type' => 'integer', 'length' => 1, 'not_null' => true, 'default' => 0),
            'multiple' => array('type' => 'integer', 'length' => 1, 'not_null' => true, 'default' => 0),
            'weight' => array('type' => 'integer', 'length' => 2, 'not_null' => true, 'default' => 0),
        )
    ),
    'product_field' => array(
        'fields' => array(
            'product_field_id' => array('type' => 'integer', 'length' => 10, 'auto_increment' => true, 'primary' => true),
            'field_id' => array('type' => 'integer', 'length' => 10, 'not_null' => true),
            'field_value_id' => array('type' => 'integer', 'length' => 10, 'not_null' => true),
            'product_id' => array('type' => 'integer', 'length' => 10, 'not_null' => true),
            'type' => array('type' => 'varchar', 'length' => 50, 'not_null' => true),
        )
    ),
    'product_sku' => array(
        'fields' => array(
            'product_sku_id' => array('type' => 'integer', 'length' => 10, 'auto_increment' => true, 'primary' => true),
            'sku' => array('type' => 'varchar', 'length' => 255, 'not_null' => true),
            'product_id' => array('type' => 'integer', 'length' => 10, 'not_null' => true),
            'combination_id' => array('type' => 'varchar', 'length' => 255, 'not_null' => true, 'default' => ''),
            'store_id' => array('type' => 'integer', 'length' => 10, 'not_null' => true),
            'stock' => array('type' => 'integer', 'length' => 10, 'not_null' => true, 'default' => 0),
            'price' => array('type' => 'integer', 'length' => 10, 'not_null' => true, 'default' => 0),
            'file_id' => array('type' => 'integer', 'length' => 10, 'not_null' => true, 'default' => 0),
            'is_default' => array('type' => 'integer', 'length' => 1, 'not_null' => true, 'default' => 0),
            'status' => array('type' => 'integer', 'length' => 1, 'not_null' => true, 'default' => 1),
        )
    ),
    'product_translation' => array(
        'fields' => array(
            'translation_id' => array('type' => 'integer', 'length' => 10, 'auto_increment' => true, 'primary' => true),
            'product_id' => array('type' => 'integer', 'length' => 10, 'not_null' => true),
            'language' => array('type' => 'varchar', 'length' => 4, 'not_null' => true),
            'title' => array('type' => 'varchar', 'length' => 255, 'not_null' => true, 'default' => ''),
            'meta_description' => array('type' => 'varchar', 'length' => 255, 'not_null' => true, 'default' => ''),
            'meta_title' => array('type' => 'varchar', 'length' => 255, 'not_null' => true, 'default' => ''),
            'description' => array('type' => 'text', 'not_null' => true, 'default' => ''),
        )
    ),
    'product_related' => array(
        'fields' => array(
            'product_related_id' => array('type' => 'integer', 'length' => 10, 'auto_increment' => true, 'primary' => true),
            'product_id' => array('type' => 'integer', 'length' => 10),
            'item_product_id' => array('type' => 'integer', 'length' => 10, 'not_null' => true),
        )
    ),
    'product_view' => array(
        'fields' => array(
            'product_view_id' => array('type' => 'integer', 'length' => 10, 'auto_increment' => true, 'primary' => true),
            'product_id' => array('type' => 'integer', 'length' => 10, 'not_null' => true),
            'user_id' => array('type' => 'varchar', 'length' => 255, 'not_null' => true),
            'created' => array('type' => 'integer', 'length' => 10, 'not_null' => true)
        )
    ),
    'product_bundle' => array(
        'fields' => array(
            'product_bundle_id' => array('type' => 'integer', 'length' => 10, 'auto_increment' => true, 'primary' => true),
            'product_id' => array('type' => 'integer', 'length' => 10, 'not_null' => true),
            'item_product_id' => array('type' => 'integer', 'length' => 10, 'not_null' => true)
        )
    ),
    'review' => array(
        'fields' => array(
            'review_id' => array('type' => 'integer', 'length' => 10, 'auto_increment' => true, 'primary' => true),
            'user_id' => array('type' => 'integer', 'length' => 10, 'not_null' => true),
            'status' => array('type' => 'integer', 'length' => 1, 'not_null' => true, 'default' => 0),
            'created' => array('type' => 'integer', 'length' => 10, 'not_null' => true),
            'modified' => array('type' => 'integer', 'length' => 10, 'not_null' => true, 'default' => 0),
            'product_id' => array('type' => 'integer', 'length' => 10, 'not_null' => true),
            'text' => array('type' => 'text', 'not_null' => true)
        )
    ),
    'rating' => array(
        'fields' => array(
            'product_id' => array('type' => 'integer', 'length' => 10, 'primary' => true),
            'votes' => array('type' => 'integer', 'length' => 10, 'not_null' => true),
            'rating' => array('type' => 'float', 'default' => 0),
        )
    ),
    'rating_user' => array(
        'fields' => array(
            'rating_user_id' => array('type' => 'integer', 'length' => 10, 'auto_increment' => true, 'primary' => true),
            'product_id' => array('type' => 'integer', 'length' => 10, 'not_null' => true),
            'user_id' => array('type' => 'varchar', 'length' => 255, 'not_null' => true),
            'rating' => array('type' => 'float', 'default' => 0),
        )
    ),
    'role' => array(
        'fields' => array(
            'role_id' => array('type' => 'integer', 'length' => 10, 'auto_increment' => true, 'primary' => true),
            'status' => array('type' => 'integer', 'length' => 1, 'not_null' => true, 'default' => 0),
            'name' => array('type' => 'varchar', 'length' => 255, 'not_null' => true),
            'redirect' => array('type' => 'varchar', 'length' => 255, 'not_null' => true, 'default' => ''),
            'permissions' => array('type' => 'blob', 'not_null' => true, 'serialize' => true),
        )
    ),
    'settings' => array(
        'fields' => array(
            'id' => array('type' => 'varchar', 'length' => 255, 'primary' => true),
            'value' => array('type' => 'blob', 'not_null' => true),
            'created' => array('type' => 'integer', 'length' => 10, 'not_null' => true),
            'serialized' => array('type' => 'integer', 'length' => 1, 'not_null' => true, 'default' => 0),
        )
    ),
    'state' => array(
        'fields' => array(
            'state_id' => array('type' => 'integer', 'length' => 10, 'auto_increment' => true, 'primary' => true),
            'status' => array('type' => 'integer', 'length' => 1, 'not_null' => true, 'default' => 0),
            'code' => array('type' => 'varchar', 'length' => 255, 'not_null' => true),
            'name' => array('type' => 'varchar', 'length' => 255, 'not_null' => true),
            'country' => array('type' => 'varchar', 'length' => 2, 'not_null' => true),
            'zone_id' => array('type' => 'integer', 'length' => 10, 'not_null' => true, 'default' => 0)
        )
    ),
    'store' => array(
        'fields' => array(
            'store_id' => array('type' => 'integer', 'length' => 10, 'auto_increment' => true, 'primary' => true),
            'status' => array('type' => 'integer', 'length' => 1, 'not_null' => true, 'default' => 0),
            'domain' => array('type' => 'varchar', 'length' => 255, 'not_null' => true),
            'name' => array('type' => 'varchar', 'length' => 255, 'not_null' => true),
            'basepath' => array('type' => 'varchar', 'length' => 50, 'not_null' => true, 'default' => ''),
            'created' => array('type' => 'integer', 'length' => 10, 'not_null' => true),
            'modified' => array('type' => 'integer', 'length' => 10, 'not_null' => true, 'default' => 0),
            'data' => array('type' => 'blob', 'not_null' => true, 'serialize' => true),
        )
    ),
    'transactions' => array(
        'fields' => array(
            'transaction_id' => array('type' => 'integer', 'length' => 10, 'auto_increment' => true, 'primary' => true),
            'order_id' => array('type' => 'integer', 'length' => 10, 'not_null' => true),
            'total' => array('type' => 'integer', 'length' => 10, 'not_null' => true),
            'currency' => array('type' => 'varchar', 'length' => 4, 'not_null' => true),
            'created' => array('type' => 'integer', 'length' => 10, 'not_null' => true),
            'payment_method' => array('type' => 'varchar', 'length' => 255, 'not_null' => true),
            'gateway_transaction_id' => array('type' => 'varchar', 'length' => 255, 'not_null' => true, 'default' => ''),
            'data' => array('type' => 'blob', 'not_null' => true, 'serialize' => true),
        )
    ),
    'triggers' => array(
        'fields' => array(
            'trigger_id' => array('type' => 'integer', 'length' => 10, 'auto_increment' => true, 'primary' => true),
            'name' => array('type' => 'varchar', 'length' => 255, 'not_null' => true),
            'status' => array('type' => 'integer', 'length' => 1, 'not_null' => true, 'default' => 0),
            'store_id' => array('type' => 'integer', 'length' => 10, 'not_null' => true),
            'weight' => array('type' => 'integer', 'length' => 2, 'not_null' => true, 'default' => 0),
            'data' => array('type' => 'blob', 'not_null' => true, 'serialize' => true),
        )
    ),
    'user' => array(
        'fields' => array(
            'user_id' => array('type' => 'integer', 'length' => 10, 'auto_increment' => true, 'primary' => true),
            'created' => array('type' => 'integer', 'length' => 10, 'not_null' => true),
            'modified' => array('type' => 'integer', 'length' => 10, 'not_null' => true, 'default' => 0),
            'status' => array('type' => 'integer', 'length' => 1, 'not_null' => true, 'default' => 1),
            'role_id' => array('type' => 'integer', 'length' => 10, 'not_null' => true, 'default' => 0),
            'store_id' => array('type' => 'integer', 'length' => 10, 'not_null' => true),
            'email' => array('type' => 'varchar', 'length' => 255, 'not_null' => true),
            'name' => array('type' => 'varchar', 'length' => 255, 'not_null' => true),
            'timezone' => array('type' => 'varchar', 'length' => 255, 'not_null' => true, 'default' => ''),
            'hash' => array('type' => 'text', 'not_null' => true),
            'data' => array('type' => 'blob', 'not_null' => true, 'serialize' => true),
        )
    ),
    'zone' => array(
        'fields' => array(
            'zone_id' => array('type' => 'integer', 'length' => 10, 'auto_increment' => true, 'primary' => true),
            'status' => array('type' => 'integer', 'length' => 1, 'not_null' => true, 'default' => 0),
            'title' => array('type' => 'varchar', 'length' => 255, 'not_null' => true)
        )
    ),
    'wishlist' => array(
        'fields' => array(
            'wishlist_id' => array('type' => 'integer', 'length' => 10, 'auto_increment' => true, 'primary' => true),
            'product_id' => array('type' => 'integer', 'length' => 10, 'not_null' => true),
            'store_id' => array('type' => 'integer', 'length' => 10, 'not_null' => true),
            'created' => array('type' => 'integer', 'length' => 10, 'not_null' => true),
            'user_id' => array('type' => 'varchar', 'length' => 255, 'not_null' => true),
        )
    ),
    'search_index' => array(
        'fields' => array(
            'search_index_id' => array('type' => 'integer', 'length' => 10, 'auto_increment' => true, 'primary' => true),
            'entity_id' => array('type' => 'integer', 'length' => 10, 'not_null' => true),
            'entity' => array('type' => 'varchar', 'length' => 50, 'not_null' => true),
            'language' => array('type' => 'varchar', 'length' => 4, 'not_null' => true),
            'text' => array('type' => 'longtext', 'not_null' => true),
        ),
        'engine' => 'MyISAM',
        'alter' => 'ADD FULLTEXT(text)',
    )
);
