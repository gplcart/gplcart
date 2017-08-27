<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Route,
    gplcart\core\Model;

/**
 * Manages basic behaviors and data related to languages and their translations
 */
class Language extends Model
{

    /**
     * Array of processed translations
     * @var array
     */
    protected $processed = array();

    /**
     * Route class instance
     * @var \gplcart\core\Route $route
     */
    protected $route;

    /**
     * The current language code
     * @var string
     */
    protected $langcode = '';

    /**
     * A path to the directory that contains context .csv translations for the current language
     * @var string
     */
    protected $directory_csv = '';

    /**
     * A path to the directory that contains context .js translations for the current language
     * @var string
     */
    protected $directory_js = '';

    /**
     * @param Route $route
     */
    public function __construct(Route $route)
    {
        parent::__construct();

        $this->route = $route;
        $this->set($this->route->getLangcode());
    }

    /**
     * Set a language code
     * @param string $langcode
     */
    public function set($langcode)
    {
        if ($langcode && $this->get($langcode)) {

            $this->langcode = $langcode;
            $this->directory_csv = $this->getContextDirectory($langcode);
            $this->directory_js = $this->getContextDirectory($langcode, true);

            if (!file_exists($this->directory_csv)) {
                mkdir($this->directory_csv, 0775, true);
            }

            if (!file_exists($this->directory_js)) {
                mkdir($this->directory_js, 0775, true);
            }
        }
    }

    /**
     * Returns an array of languages
     * @param bool $enabled
     * @return array
     */
    public function getList($enabled = false)
    {
        $languages = &gplcart_static(__METHOD__ . "$enabled");

        if (isset($languages)) {
            return $languages;
        }

        $default = $this->getDefault();
        $available = $this->getAvailable();
        $saved = $this->config->get('languages', $this->getDefaultList());
        $languages = gplcart_array_merge($available, $saved);

        foreach ($languages as $code => &$language) {
            $language['code'] = $code;
            $language['default'] = ($code == $default);
        }

        $this->hook->attach('language.list', $languages, $this);

        if ($enabled) {
            $languages = array_filter($languages, function ($language) {
                return !empty($language['status']);
            });
        }

        gplcart_array_sort($languages);

        return $languages;
    }

    /**
     * Returns a default language code
     * @return string
     */
    public function getDefault()
    {
        return $this->config->get('language', '');
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
     * Returns an array of default languages
     * @return array
     */
    public function getDefaultList()
    {
        return array(
            'en' => array(
                'weight' => 0,
                'status' => true,
                'default' => false,
                'code' => 'en',
                'name' => 'English',
                'native_name' => 'English'
            )
        );
    }

    /**
     * Scans language folders and returns an array of available languages
     * @return array
     */
    public function getAvailable()
    {
        $iso = $this->getIso();

        $languages = array();
        foreach (scandir(GC_LOCALE_DIR) as $langcode) {

            if (preg_match('/^[a-z]{2}(_[A-Z]{2})?$/', $langcode) !== 1) {
                continue;
            }

            $name = $native_name = $langcode;

            if (!empty($iso[$langcode][0])) {
                $name = $native_name = $iso[$langcode][0];
            }

            if (!empty($iso[$langcode][1])) {
                $native_name = $iso[$langcode][1];
            }

            $languages[$langcode] = array(
                'weight' => 0,
                'name' => $name,
                'status' => false,
                'default' => false,
                'code' => $langcode,
                'native_name' => $native_name
            );
        }

        return $languages;
    }

    /**
     * Returns a path to the common translation file
     * @param string $langcode
     * @return string
     */
    public function getFile($langcode)
    {
        return GC_LOCALE_DIR . "/$langcode/common.csv";
    }

    /**
     * Returns a path to the directory containing context translations
     * @param string $langcode
     * @param bool $js
     * @return boolean|string
     */
    public function getContextDirectory($langcode, $js = false)
    {
        $base = $js ? GC_LOCALE_JS_DIR : GC_LOCALE_DIR;
        return "$base/$langcode/context";
    }

    /**
     * Returns a language
     * @param string $code
     * @return array
     */
    public function get($code)
    {
        $languages = $this->getList();
        return isset($languages[$code]) ? $languages[$code] : array();
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

        $iso = $this->getIso($data['code']);

        $native_name = $name = $data['code'];

        if (empty($data['name']) && !empty($iso[0])) {
            $native_name = $name = $iso[0];
        }

        if (empty($data['native_name']) && !empty($iso[1])) {
            $native_name = $iso[1];
        }

        $values = array(
            'name' => $name,
            'code' => $data['code'],
            'native_name' => $native_name,
            'status' => !empty($data['status']),
            'default' => !empty($data['default']),
            'weight' => isset($data['weight']) ? (int) $data['weight'] : 0
        );

        $languages = $this->getList();

        if (!empty($values['default'])) {
            $values['status'] = true;
            $this->setDefault($data['code']);
        }

        $languages[$data['code']] = $values;
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

        $languages = $this->getList();

        if (empty($languages[$code])) {
            return false;
        }

        if (!empty($data['default']) && !$this->isDefault($code)) {
            $data['status'] = true;
            $this->setDefault($code);
        }

        if ($this->isDefault($code)) {
            $data['status'] = true;
        }

        $languages[$code] = $data + $languages[$code];
        $this->config->set('languages', $languages);

        $result = true;
        $this->hook->attach('language.update.after', $code, $data, $result, $this);

        return (bool) $result;
    }

    /**
     * Deletes a language
     * @param string $code
     * @return boolean
     */
    public function delete($code)
    {
        $result = null;
        $this->hook->attach('language.delete.before', $code, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        $languages = $this->getList();
        unset($languages[$code]);
        $this->config->set('languages', $languages);

        if ($this->isDefault($code)) {
            $this->config->reset('language');
        }

        $result = true;
        $this->hook->attach('language.delete.after', $code, $result, $this);
        return (bool) $result;
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
     * Translates a string
     * @param string $string
     * @param array $arguments
     * @param string $class
     * @return string
     */
    public function text($string, array $arguments = array(), $class = '')
    {
        if (isset($this->processed[$string])) {
            return $this->processed[$string];
        }

        if (empty($this->langcode)) {
            return $this->processed[$string] = $this->formatString($string, $arguments);
        }

        $filename = $this->getContextFileName($class);
        $class_translations = $this->loadTranslations($filename);

        if (isset($class_translations[$string])) {
            return $this->processed[$string] = $this->formatString($string, $arguments, $class_translations[$string]);
        }

        $common_translations = $this->loadTranslations();

        if (isset($common_translations[$string])) {
            $this->addString($string, $common_translations[$string], $filename);
            return $this->processed[$string] = $this->formatString($string, $arguments, $common_translations[$string]);
        }

        $this->addString($string);
        return $this->processed[$string] = $this->formatString($string, $arguments);
    }

    /**
     * Returns a filename of context translation file
     * @param string $class
     * @return string
     */
    protected function getContextFileName($class = '')
    {
        if (empty($class)) {
            $class = __CLASS__;
        }

        return strtolower(str_replace('\\', '-', $class));
    }

    /**
     * Returns a translated and formated string
     * @param string $source
     * @param array $args
     * @param array $data
     * @return string
     */
    protected function formatString($source, array $args, array $data = array())
    {
        if (!isset($data[0]) || $data[0] === '') {
            return gplcart_string_format($source, $args);
        }

        return gplcart_string_format($data[0], $args);
    }

    /**
     * Returns an array of translations from CSV files
     * @param string $filename
     * @return array
     */
    public function loadTranslations($filename = '')
    {
        $translations = &gplcart_static(__METHOD__ . ".{$this->langcode}.$filename");

        if (isset($translations)) {
            return (array) $translations;
        }

        if (empty($filename)) {
            $file = $this->getFile($this->langcode);
        } else {
            $file = "{$this->directory_csv}/$filename.csv";
        }

        $translations = array();
        foreach ($this->parseCsv($file) as $row) {
            $key = array_shift($row);
            $translations[$key] = $row;
        }

        return $translations;
    }

    /**
     * Parse a translation file
     * @param string $file
     * @return array
     */
    protected function parseCsv($file)
    {
        if (!is_file($file)) {
            return array();
        }

        $content = file($file);

        if (empty($content)) {
            return array();
        }

        return array_map('str_getcsv', $content);
    }

    /**
     * Append translations from a file to the common file
     * @staticvar array $added
     * @param string $langcode
     * @param string $merge_file
     * @return array
     */
    public function mergeTranslations($langcode, $merge_file)
    {
        $common_file = $this->getFile($langcode);
        $common_content = $this->parseCsv($common_file);
        $merge_content = $this->parseCsv($merge_file);

        if (empty($merge_content)) {
            return $common_content;
        }

        $existing = array();
        foreach ($common_content as $line) {
            $existing[$line[0]] = true;
        }

        foreach ($merge_content as $line) {
            if (!isset($existing[$line[0]])) {
                $common_content[] = $line;
                gplcart_file_csv($common_file, $line);
            }
        }

        return $common_content;
    }

    /**
     * Writes one line to CSV and JS translation files
     * @param string $string
     * @param array $data
     * @param string $filename
     * @return bool
     */
    protected function addString($string, $data = array(), $filename = '')
    {
        if (isset($this->processed[$string])) {
            return false;
        }

        $file = $this->getFile($this->langcode);

        if (!empty($filename)) {
            $file = "{$this->directory_csv}/$filename.csv";
            $this->addStringJs($string, $data, $filename);
        }

        array_unshift($data, $string);
        return gplcart_file_csv($file, $data);
    }

    /**
     * Writes one line of JS code to JS translation file
     * @param string $string
     * @param array $data
     * @param string $filename
     * @return bool
     */
    protected function addStringJs($string, array $data, $filename)
    {
        $file = "{$this->directory_js}/$filename.js";

        $key = gplcart_json_encode($string);
        $translation = gplcart_json_encode($data);

        return (bool) file_put_contents($file, "GplCart.translations[$key]=$translation;\n", FILE_APPEND);
    }

    /**
     * Removes cached translation files
     * @param string $langcode
     */
    public function refresh($langcode)
    {
        gplcart_file_delete(GC_LOCALE_DIR . "/$langcode/context", array('csv'));
        gplcart_file_delete(GC_LOCALE_JS_DIR . "/$langcode", array('js'));
    }

    /**
     * Returns the current language
     * @return string
     */
    public function current()
    {
        return $this->langcode;
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
        $this->hook->attach('language.translit', $string, $language, $result, $this);

        if (isset($result)) {
            return $result;
        }

        if (function_exists('transliterator_transliterate')) {
            return transliterator_transliterate('Any-Latin; Latin-ASCII; [\u0100-\u7fff] remove', $string);
        }

        return $string;
    }

    /**
     * Returns an array of common languages with their English and native names
     * @param null|string $code
     * @return array
     */
    public function getIso($code = null)
    {
        static $data = null;

        if (!isset($data)) {
            $data = require GC_CONFIG_LANGUAGE;
        }

        if (isset($code)) {
            return isset($data[$code]) ? (array) $data[$code] : array();
        }

        return $data;
    }

}
