<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\handlers\validator;

use core\models\Store as ModelsStore;
use core\models\Language as ModelsLanguage;

/**
 * Provides methods to validate various database related data
 */
class Store
{

    /**
     * Language model instance
     * @var \core\models\Language $language
     */
    protected $language;

    /**
     * Store model instance
     * @var \core\models\Store $store
     */
    protected $store;

    /**
     * Constructor
     * @param ModelsLanguage $language
     * @param ModelsStore $store
     */
    public function __construct(ModelsLanguage $language, ModelsStore $store)
    {
        $this->store = $store;
        $this->language = $language;
    }

    /**
     * Checks if a domain is unique
     * @param string $value
     * @param array $options
     * @return boolean|string
     */
    public function domainUnique($value, array $options = array())
    {
        if((!isset($value) || $value === '') && empty($options['required'])){
            return true;
        }        
        
        if (isset($options['data']['domain']) && ($options['data']['domain'] === $value)) {
            return true;
        }

        $existing = $this->store->get($value);

        if (empty($existing)) {
            return true;
        }

        return $this->language->text('Domain %domain already taken', array(
                    '%domain' => $value));
    }
    
    /**
     * Checks if a basepath is unique
     * @param string $value
     * @param array $options
     * @return boolean|string
     */
    public function basepathUnique($value, array $options = array())
    {
        if(!isset($value) || $value === ''){
            return true;
        }
        
        $store = $options['data'];
        $domain = $options['domain'];
        
        if (isset($store['basepath'])
                && $store['basepath'] === $value
                && $store['domain'] === $domain) {
            
            return true;
        }
        
        $stores = $this->store->getList(array(
            'domain' => $domain,
            'basepath' => $value
        ));

        foreach ($stores as $store_id => $data) {
            
            if (isset($store['store_id']) && $store['store_id'] == $store_id) {
                continue;
            }

            if ($data['domain'] === $domain && $data['basepath'] === $value) {
                return $this->language->text('Basepath %basepath already taken for domain %domain', array(
                    '%basepath' => $value, '%domain' => $domain));
            }
        }
        
        return true;
    }

}
