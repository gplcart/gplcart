<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Hook,
    gplcart\core\Config;

/**
 * Manages basic behaviors and data related to languages and their translations
 */
class Language
{

    /**
     * Hook class instance
     * @var \gplcart\core\Hook $hook
     */
    protected $hook;

    /**
     * Config class instance
     * @var \gplcart\core\Config $config
     */
    protected $config;

    /**
     * @param Hook $hook
     * @param Config $config
     */
    public function __construct(Hook $hook, Config $config)
    {
        $this->hook = $hook;
        $this->config = $config;
    }

    /**
     * Returns a language
     * @param string $code
     * @return array
     */
    public function get($code)
    {
        $result = null;
        $this->hook->attach('language.get.before', $code, $result, $this);

        if (isset($result)) {
            return (array) $result;
        }

        $list = $this->getList();
        $result = isset($list[$code]) ? $list[$code] : array();
        $this->hook->attach('language.get.after', $code, $result, $this);
        return (array) $result;
    }

    /**
     * Returns an array of languages
     * @param array $options
     * @return array
     */
    public function getList(array $options = array())
    {
        $options += array(
            'enabled' => false,
            'in_database' => false
        );

        $languages = &gplcart_static(gplcart_array_hash(array('language.list' => $options)));

        if (isset($languages)) {
            return $languages;
        }

        $this->hook->attach('language.list.before', $options, $languages, $this);

        if (isset($languages)) {
            return (array) $languages;
        }

        $iso = $this->getIso();
        $default_code = $this->getDefault();
        $default_data = $this->getDefaultData();
        $saved = $this->config->get('languages', array());
        $languages = array_replace_recursive($iso, $saved);

        $this->hook->attach('language.list.after', $options, $languages, $this);

        foreach ($languages as $code => &$language) {

            $language['code'] = $code;
            $language += $default_data;
            $language['default'] = ($code == $default_code);
            $language['in_database'] = isset($saved[$code]);

            if (empty($language['native_name'])) {
                $language['native_name'] = $language['name'];
            }

            if ($code === 'en') {
                $language['status'] = true;
            }

            if ($options['enabled'] && empty($language['status'])) {
                unset($languages[$code]);
                continue;
            }

            if ($options['in_database'] && empty($language['in_database'])) {
                unset($languages[$code]);
            }
        }

        gplcart_array_sort($languages);
        return $languages;
    }

    /**
     * Adds a language
     * @param array $data
     * @return boolean
     */
    public function add(array $data)
    {
        $result = null;
        $this->hook->attach('language.add.before', $data, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        if (!empty($data['default'])) {
            $data['status'] = true;
            $this->setDefault($data['code']);
        }

        $default = $this->getDefaultData($data['code']);
        $data += $default;

        $languages = $this->config->select('languages', array());
        $languages[$data['code']] = array_intersect_key($data, $default);
        $this->config->set('languages', $languages);

        $result = true;
        $this->hook->attach('language.add.after', $data, $result, $this);
        return (bool) $result;
    }

    /**
     * Updates a language
     * @param string $code
     * @param array $data
     * @return boolean
     */
    public function update($code, array $data)
    {
        $result = null;
        $this->hook->attach('language.update.before', $code, $data, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        if (!empty($data['default'])) {
            $data['status'] = true;
            $this->setDefault($code);
        }

        if ($this->isDefault($code)) {
            $data['status'] = true;
        }

        $iso = $this->getIso();

        if (!empty($iso[$code])) {
            $data += $iso[$code];
        }

        $default = $this->getDefaultData($code);
        $languages = $this->config->select('languages', array());

        if (empty($languages[$code])) {
            $data += $default;
        } else {
            $data += $languages[$code];
        }

        $languages[$code] = array_intersect_key($data, $default);
        $this->config->set('languages', $languages);

        $result = true;
        $this->hook->attach('language.update.after', $code, $data, $result, $this);
        return (bool) $result;
    }

    /**
     * Deletes a language
     * @param string $code
     * @param bool $check
     * @return boolean
     */
    public function delete($code, $check = true)
    {
        $result = null;
        $this->hook->attach('language.delete.before', $code, $check, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        if ($check && !$this->canDelete($code)) {
            return false;
        }

        $languages = $this->config->select('languages', array());
        unset($languages[$code]);
        $this->config->set('languages', $languages);

        if ($this->isDefault($code)) {
            $this->config->reset('language');
        }

        $result = true;
        $this->hook->attach('language.delete.after', $code, $check, $result, $this);
        return (bool) $result;
    }

    /**
     * Whether the language can be deleted
     * @param string $code
     * @return bool
     */
    public function canDelete($code)
    {
        $languages = $this->config->select('languages', array());
        return isset($languages[$code]);
    }

    /**
     * Sets default language
     * @param string $code
     * @return boolean
     */
    public function setDefault($code)
    {
        return $this->config->set('language', $code);
    }

    /**
     * Returns a default language code
     * @return string
     */
    public function getDefault()
    {
        return $this->config->get('language', 'en');
    }

    /**
     * Whether the language code is default
     * @param string $code
     * @return bool
     */
    public function isDefault($code)
    {
        return $code === $this->getDefault();
    }

    /**
     * Transliterate a string
     * @param string $string
     * @param string $language
     * @return string
     */
    public function translit($string, $language)
    {
        $result = null;
        $this->hook->attach('language.translit.before', $string, $language, $result, $this);

        if (isset($result)) {
            return $result;
        }

        $result = $string;
        if (function_exists('transliterator_transliterate')) {
            $result = transliterator_transliterate('Any-Latin; Latin-ASCII; [\u0100-\u7fff] remove', $string);
        } else if (function_exists('iconv')) {
            $result = iconv('UTF-8', 'ASCII//IGNORE//TRANSLIT', $string);
        }

        if (trim($result) === '') {
            $result = $string;
        }

        $this->hook->attach('language.translit.after', $string, $language, $result, $this);
        return $result;
    }

    /**
     * Returns an array of common languages with their English and native names
     * @param null|string $code
     * @return array
     */
    public function getIso($code = null)
    {
        $data = (array) gplcart_config_get(GC_FILE_CONFIG_LANGUAGE);

        if (isset($code)) {
            return isset($data[$code]) ? (array) $data[$code] : array();
        }

        return $data;
    }

    /**
     * Returns an array of default language data
     * @param string $code
     * @return array
     */
    protected function getDefaultData($code = '')
    {
        return array(
            'code' => $code,
            'name' => $code,
            'weight' => 0,
            'rtl' => false,
            'status' => false,
            'default' => false,
            'native_name' => $code,
        );
    }

}
