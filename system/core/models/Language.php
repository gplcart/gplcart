<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\models;

use core\Model;
use core\Route;
use core\classes\Tool;
use core\classes\Cache;
use core\models\Translit;

/**
 * Manages basic behaviors and data related to languages and their translations
 */
class Language extends Model
{

    /**
     * Route class instance
     * @var \core\Route $route;
     */
    protected $route;

    /**
     * Translit library instance
     * @var \libraries\translit\Translit $translit;
     */
    protected $translit;

    /**
     * Current language code
     * @var boolean
     */
    protected $langcode = '';

    /**
     * Directory that holds main translation file for the current language
     * @var string
     */
    protected $language_directory = '';

    /**
     * Path to directory that keeps complied .csv translations
     * for the current language
     * @var string
     */
    protected $compiled_directory_csv = '';

    /**
     * Path to directory that keeps complied js translations
     * for the current language
     * @var string
     */
    protected $compiled_directory_js = '';

    /**
     * Constructor
     * @param Document $document
     * @param Translit $translit
     * @param Route $route
     */
    public function __construct(Translit $translit, Route $route)
    {
        parent::__construct();

        $this->route = $route;
        $this->translit = $translit;
        $this->langcode = $this->route->getLangcode();

        if (!empty($this->langcode)) {

            $this->language_directory = GC_LOCALE_DIR . "/{$this->langcode}";
            $this->compiled_directory_csv = "{$this->language_directory}/compiled";
            $this->compiled_directory_js = GC_LOCALE_JS_DIR . "/{$this->langcode}";

            if (!file_exists($this->compiled_directory_csv)) {
                mkdir($this->compiled_directory_csv, 0755, true);
            }

            if (!file_exists($this->compiled_directory_js)) {
                mkdir($this->compiled_directory_js, 0755, true);
            }
        }
    }

    /**
     * Returns a sorted array of available languages
     * @param boolean $enabled If true disabled languages will be excluded
     * @return array
     */
    public function getlist($enabled = false)
    {
        $languages = $this->getAll();

        if ($enabled) {
            $languages = array_filter($languages, function ($language) {
                return !empty($language['status']);
            });
        }

        Tool::sortWeight($languages);
        return $languages;
    }

    /**
     * Returns an array of all languages
     * including default and added/updated languages
     * @return array
     */
    public function getAll()
    {
        $languages = &Cache::memory('languages');

        if (isset($languages)) {
            return $languages;
        }

        $default = $this->getDefault();
        $available = $this->getAvailable();
        $saved = $this->config->get('languages', array());

        $languages = Tool::merge($available, $saved);

        foreach ($languages as $code => &$language) {
            $language['code'] = $code;
            $language['default'] = ($code == $default);
            $language['weight'] = isset($language['weight']) ? (int) $language['weight'] : 0;
        }

        $this->hook->fire('languages', $languages);
        return $languages;
    }

    /**
     * Scans language folders and returns an array of available languages
     * It assumes that each language folder name matches a valid language code
     * @return array
     */
    public function getAvailable()
    {
        $languages = array();
        foreach (glob(GC_LOCALE_DIR . '/*', GLOB_ONLYDIR) as $directory) {

            $langcode = basename($directory);

            // Skip invalid language codes
            if (!preg_match('/^[a-z]{2}(_[A-Z]{2})?$/', $langcode)) {
                continue;
            }

            $languages[$langcode] = array(
                'status' => false,
                'default' => false,
                'code' => $langcode,
                'name' => $langcode,
                'weight' => 0,
                'native_name' => $langcode
            );
        }

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
     * Returns a language
     * @param string $code
     * @return array
     */
    public function get($code)
    {
        $languages = $this->getAll();
        return isset($languages[$code]) ? $languages[$code] : array();
    }

    /**
     * Adds a language
     * @param array $data
     * @return boolean
     */
    public function add(array $data)
    {
        $this->hook->fire('add.language.before', $data);

        if (empty($data['code'])) {
            return false;
        }

        $values = array(
            'code' => $data['code'],
            'status' => !empty($data['status']),
            'default' => !empty($data['default']),
            'weight' => isset($data['weight']) ? (int) $data['weight'] : 0,
            'name' => empty($data['name']) ? $data['code'] : $data['name'],
            'native_name' => empty($data['native_name']) ? $data['code'] : $data['native_name']
        );

        $languages = $this->getAll();

        $languages[$data['code']] = $values;
        $this->config->set('languages', $languages);

        if (!empty($values['default'])) {
            $this->config->set('language', $data['code']);
        }

        $this->hook->fire('add.language.after', $data);
        return true;
    }

    /**
     * Updates a language
     * @param string $code
     * @param array $data
     * @return boolean
     */
    public function update($code, array $data)
    {
        $this->hook->fire('update.language.before', $code, $data);

        $languages = $this->getAll();

        if (empty($languages[$code])) {
            return false;
        }

        $languages[$code] = $data + $languages[$code];
        $this->config->set('languages', $languages);

        if (!empty($data['default'])) {
            $this->config->set('language', $code);
        }

        $this->hook->fire('update.language.after', $code, $data);
        return true;
    }

    /**
     * Deletes a language
     * @param string $code
     * @return boolean
     */
    public function delete($code)
    {
        $this->hook->fire('delete.language.before', $code);

        if (empty($code)) {
            return false;
        }

        $languages = $this->getAll();
        unset($languages[$code]);
        $this->config->set('languages', $languages);

        if ($code == $this->getDefault()) {
            $this->config->reset('language');
        }

        $this->hook->fire('delete.language.after', $code, $languages);
        return true;
    }

    /**
     * Returns an array of translations from CSV files
     * @param string|null $filename
     * @return array
     */
    public function load($filename = null)
    {
        $cache_key = "translations.{$this->langcode}";

        if (isset($filename)) { // !empty() doesn't work on redirects
            $cache_key .= ".$filename";
        }

        $translations = &Cache::memory($cache_key);

        if (isset($translations)) {
            return (array) $translations;
        }

        $file = "{$this->language_directory}/common.csv";

        if (isset($filename)) {
            $file = "{$this->compiled_directory_csv}/$filename.csv";
        }

        if (!file_exists($file)) {
            return array();
        }

        $rows = array_map('str_getcsv', file($file));

        if (empty($rows)) {
            return array();
        }

        // Reindex the array
        // First column (source string) becomes a key
        foreach ($rows as $row) {
            $key = array_shift($row);
            $translations[$key] = $row;
        }

        return $translations;
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
        if (empty($this->langcode)) {
            return $string;
        }

        if (empty($class)) {
            $class = __CLASS__;
        }

        $filename = strtolower(str_replace('\\', '-', $class));
        $class_translations = $this->load($filename);

        if (!empty($class_translations[$string][0])) {
            return Tool::formatString($class_translations[$string][0], $arguments);
        }

        $all_translations = $this->load();

        if (!empty($all_translations[$string][0])) {
            $this->addString($string, $all_translations[$string], $filename);
            return Tool::formatString($all_translations[$string][0], $arguments);
        }

        $this->addString($string, array($string), $filename);
        return Tool::formatString($string, $arguments);
    }

    /**
     * Writes one line to CSV and JS translation files
     * @param string $string
     * @param array $data
     * @param null|string $filename
     */
    protected function addString($string, $data = array(), $filename = null)
    {
        $file = "{$this->language_directory}/common.csv";

        if (isset($filename)) {
            $file = "{$this->compiled_directory_csv}/$filename.csv";
            $this->addStringJs($string, $data, $filename);
        }

        array_unshift($data, $string);

        $fp = fopen($file, 'a');
        fputcsv($fp, $data);
        fclose($fp);
    }

    /**
     * Writes one line of JS code to JS translation file
     * @param string $string
     * @param array $data
     * @param string $filename
     */
    protected function addStringJs($string, array $data, $filename)
    {
        $jsfile = "{$this->compiled_directory_js}/$filename.js";
        $json = 'GplCart.translations[' . json_encode($string) . ']=' . json_encode($data) . ';' . PHP_EOL;
        file_put_contents($jsfile, $json, FILE_APPEND);
    }

    /**
     * Removes cached translation files
     * @param string $langcode
     */
    public function refresh($langcode)
    {
        Tool::deleteFiles(GC_LOCALE_DIR . "/$langcode/compiled", array('csv'));
        Tool::deleteFiles(GC_LOCALE_JS_DIR . "/$langcode", array('js'));
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
     * Transliterates a string
     * @param string $string
     * @param string $language
     * @return string
     */
    public function translit($string, $language)
    {
        $this->hook->fire('translit.before', $string, $language);

        if (empty($string)) {
            return '';
        }

        $translit = $this->translit->translit($string, '?', $language);
        $this->hook->fire('translit.after', $string, $language, $translit);
        return $translit;
    }

}
