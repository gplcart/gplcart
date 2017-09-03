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
     * The current context
     * @var string
     */
    protected $context;

    /**
     * @param Route $route
     */
    public function __construct(Route $route)
    {
        parent::__construct();

        $this->route = $route;
        $this->context = __CLASS__;

        $this->set($this->route->getLangcode());
    }

    /**
     * Sets the current translation context
     * @param string $context
     * @return $this
     */
    public function setContext($context)
    {
        $this->context = $context;
        return $this;
    }

    /**
     * Returns the current translation context
     * @return string
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Set a language code
     * @param string $langcode
     */
    public function set($langcode)
    {
        if ($langcode && $this->get($langcode)) {
            $this->langcode = $langcode;
            $this->prepareFiles();
        }
    }

    /**
     * Prepare all necessary files for the current language
     */
    protected function prepareFiles()
    {
        $csv = $this->getDirectoryCompiled($this->langcode);
        $js = $this->getDirectoryCompiled($this->langcode, true);

        if (!file_exists($csv)) {
            mkdir($csv, 0775, true);
        }

        if (!file_exists($js)) {
            mkdir($js, 0775, true);
        }

        $this->copyFile($this->langcode);
    }

    /**
     * Create common compiled file using primary translation as a source
     * @param string $langcode
     * @return boolean
     */
    protected function copyFile($langcode)
    {
        $main_file = $this->getFile($langcode);
        $common_file = $this->getFileCommon($langcode);

        if (!is_file($common_file) && is_file($main_file)) {
            return copy($main_file, $common_file);
        }

        return false;
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
     * Returns a language directory
     * @param string $langcode
     * @param bool $js
     * @return string
     */
    public function getDirectory($langcode, $js = false)
    {
        $base = $js ? GC_LOCALE_JS_DIR : GC_LOCALE_DIR;
        return "$base/$langcode";
    }

    /**
     * Returns a path to a translation file
     * @param string $langcode
     * @param string $module_id
     * @return string
     */
    public function getFile($langcode, $module_id = '')
    {
        if (empty($module_id)) {
            $directory = $this->getDirectory($langcode);
        } else {
            $directory = $this->getDirectoryModule($module_id);
        }

        return "$directory/$langcode.csv";
    }

    /**
     * Returns the module translation directory
     * @param string $module_id
     * @return string
     */
    public function getDirectoryModule($module_id)
    {
        $directory = $this->config->getModuleDirectory($module_id);
        return "$directory/locale";
    }

    /**
     * Returns the path to a common translation file
     * @param string $langcode
     * @return string
     */
    public function getFileCommon($langcode)
    {
        $directory = $this->getDirectoryCompiled($langcode);
        return "$directory/$langcode.csv";
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
     * @return string
     */
    public function text($string, array $arguments = array())
    {
        if (isset($this->processed[$string])) {
            return $this->processed[$string];
        }

        if (empty($this->langcode)) {
            return $this->processed[$string] = $this->formatString($string, $arguments);
        }

        $context_file = $this->getFileContext($this->context, $this->langcode);
        $context_translations = $this->loadTranslation($context_file);

        if (isset($context_translations[$string])) {
            $this->addJsTranslation($string, $context_translations);
            return $this->processed[$string] = $this->formatString($string, $arguments, $context_translations[$string]);
        }

        $this->toTranslate($string, $context_file);

        $common_file = $this->getFileCommon($this->langcode);
        $common_translations = $this->loadTranslation($common_file);

        if (isset($common_translations[$string])) {
            $this->addJsTranslation($string, $common_translations);
            return $this->processed[$string] = $this->formatString($string, $arguments, $common_translations[$string]);
        }

        $this->toTranslate($string, $common_file);
        return $this->processed[$string] = $this->formatString($string, $arguments);
    }

    /**
     * Returns a context translation file
     * @param string $context
     * @param string $langcode
     * @param bool $js
     * @return string
     */
    public function getFileContext($context, $langcode, $js = false)
    {
        $extension = $js ? 'js' : 'csv';
        $filename = strtolower(str_replace('\\', '-', $context));
        $directory = $this->getDirectoryCompiled($langcode, $js);

        return "$directory/$filename.$extension";
    }

    /**
     * Returns a path to a directory containing compiled translations
     * @param string $langcode
     * @param bool $js
     * @return string
     */
    public function getDirectoryCompiled($langcode, $js = false)
    {
        $directory = $this->getDirectory($langcode, $js);
        return "$directory/compiled";
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
     * @param string $file
     * @return array
     */
    public function loadTranslation($file)
    {
        $translations = &gplcart_static(__METHOD__ . $file);

        if (isset($translations)) {
            return (array) $translations;
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
    public function parseCsv($file)
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
     * Add translations from a file to the common file
     * @param string $merge_file
     * @param string $langcode
     */
    protected function mergeTranslation($merge_file, $langcode)
    {
        $common_file = $this->getFileCommon($langcode);
        $merge_translations = $this->loadTranslation($merge_file);
        $common_translations = $this->loadTranslation($common_file);

        if (!empty($merge_translations)) {
            foreach ($merge_translations as $source => $translation) {
                if (!isset($common_translations[$source])) {
                    array_unshift($translation, $source);
                    gplcart_file_csv($common_file, $translation);
                }
            }
        }
    }

    /**
     * Append a string to be translated to the translation file
     * @param string $string
     * @param string $file
     */
    protected function toTranslate($string, $file)
    {
        if (!isset($this->processed[$string])) {
            gplcart_file_csv($file, array($string));
        }
    }

    /**
     * Writes one line of JS code to JS translation file
     * @param string $string
     * @param array $translations
     */
    protected function addJsTranslation($string, array $translations)
    {
        if (!isset($this->processed[$string]) && isset($translations[$string][0]) && $translations[$string][0] !== '') {
            $key = gplcart_json_encode($string);
            $translation = gplcart_json_encode($translations[$string]);
            $file = $this->getFileContext($this->context, $this->langcode, true);
            file_put_contents($file, "GplCart.translations[$key]=$translation;\n", FILE_APPEND);
        }
    }

    /**
     * Returns an array of compiled translations for the given language
     * @param string $langcode
     * @return array
     */
    public function getCompiledFiles($langcode)
    {
        $directory = $this->getDirectoryCompiled($langcode);
        return is_dir($directory) ? gplcart_file_scan($directory, array('csv')) : array();
    }

    /**
     * Removes cached translation files
     * @param string $langcode
     */
    public function refresh($langcode)
    {
        gplcart_file_delete($this->getDirectoryCompiled($langcode), array('csv'));
        gplcart_file_delete($this->getDirectoryCompiled($langcode, true), array('js'));

        $this->copyFile($langcode);
        $this->mergeModuleTranslations($langcode);
    }

    /**
     * Add translations from all enabled modules to the common file
     * @param string $langcode
     */
    protected function mergeModuleTranslations($langcode)
    {
        $modules = $this->config->getEnabledModules();

        // Sort modules in descending order
        // More important modules go first so their translations will be added earlier
        gplcart_array_sort($modules, 'weight', false);

        foreach ($modules as $module) {
            $file = $this->getFile($langcode, $module['module_id']);
            if (is_file($file)) {
                $this->mergeTranslation($file, $langcode);
            }
        }
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
