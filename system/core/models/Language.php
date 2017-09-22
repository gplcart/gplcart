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
    protected $written = array();

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
     * The current translation context
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

        $current_route = $this->route->getCurrent();
        if (isset($current_route['simple_pattern'])) {
            $this->context = $current_route['simple_pattern'];
        }

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
        $dir = $this->getDirectoryCompiled();

        if (!file_exists($dir)) {
            mkdir($dir, 0775, true);
        }

        $this->copyFile();
    }

    /**
     * Create common compiled file using primary translation as a source
     * @param string|null $langcode
     * @return boolean
     */
    protected function copyFile($langcode = null)
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
     * @param string|null $langcode
     * @return string
     */
    public function getDirectory($langcode = null)
    {
        if (!isset($langcode)) {
            $langcode = $this->langcode;
        }

        return GC_LOCALE_DIR . "/$langcode";
    }

    /**
     * Returns a path to a translation file
     * @param string|null $langcode
     * @param string $module_id
     * @return string
     */
    public function getFile($langcode = null, $module_id = '')
    {
        if (!isset($langcode)) {
            $langcode = $this->langcode;
        }

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
     * @param string|null $langcode
     * @return string
     */
    public function getFileCommon($langcode = null)
    {
        if (!isset($langcode)) {
            $langcode = $this->langcode;
        }

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
     * @param bool $format
     * @return string|array
     */
    public function text($string, array $arguments = array(), $format = true)
    {
        if (empty($this->langcode) || empty($this->context)) {
            return $format ? $this->formatString($string, $arguments) : array();
        }

        $context_file = $this->getFileContext();
        $context_translations = $this->loadTranslation($context_file);

        if (isset($context_translations[$string])) {
            return $format ? $this->formatString($string, $arguments, $context_translations[$string]) : $context_translations[$string];
        }

        $common_file = $this->getFileCommon();
        $common_translations = $this->loadTranslation($common_file);

        if (isset($common_translations[$string])) {
            $this->addTranslation($string, $common_translations, $context_file);
            return $format ? $this->formatString($string, $arguments, $common_translations[$string]) : $common_translations[$string];
        }

        $this->addTranslation($string, $common_translations, $context_file);
        $this->addTranslation($string, $common_translations, $common_file);

        return $format ? $this->formatString($string, $arguments) : $string;
    }

    /**
     * Returns a context translation file
     * @param string|null $context
     * @param string|null $langcode
     * @return string
     */
    public function getFileContext($context = null, $langcode = null)
    {
        $filename = $this->getFilenameFromContext($context);
        $directory = $this->getDirectoryCompiled($langcode);

        return "$directory/$filename.csv";
    }

    /**
     * Returns the path to a compiled JS translation file
     * @param string|null $langcode
     * @return string
     */
    public function getFileJs($langcode = null)
    {
        return $this->getFileContext('js', $langcode);
    }

    /**
     * Converts context into filename
     * @param string|null $context
     * @return string
     */
    protected function getFilenameFromContext($context = null)
    {
        if (!isset($context)) {
            $context = $this->context;
        }

        return strtolower(str_replace(array('\\', '/', '-*'), array('-', '-', '~'), $context));
    }

    /**
     * Returns a path to a directory containing compiled translations
     * @param string|null $langcode
     * @return string
     */
    public function getDirectoryCompiled($langcode = null)
    {
        return $this->getDirectory($langcode) . '/compiled';
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
        $translations = &gplcart_static(__METHOD__ . "$file");

        if (isset($translations)) {
            return (array) $translations;
        }

        $translations = array();

        if (!is_file($file)) {
            return $translations = array();
        }

        foreach ($this->parseCsv($file) as $row) {
            $key = array_shift($row);
            $translations[$key] = $row;
        }

        return $translations;
    }

    /**
     * Returns an array of JS translations
     * @param string|null $langcode
     * @return array
     */
    public function loadTranslationJs($langcode = null)
    {
        $strings = $this->loadTranslation($this->getFileJs($langcode));

        $translations = array();
        foreach ($strings as $source => $translation) {
            if (isset($translation[0]) && $translation[0] !== '') {
                $translations[$source] = $translation;
            }
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
        $handle = fopen($file, 'r');

        if ($handle === false) {
            return array();
        }

        $content = array();
        while (($data = fgetcsv($handle)) !== false) {
            $content[] = $data;
        }

        fclose($handle);
        return $content;
    }

    /**
     * Merge two translations
     * @param string $merge_file
     * @param string|null $langcode
     */
    protected function mergeTranslation($merge_file, $langcode = null)
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
     * Append the string to the translation file
     * @param string $string
     * @param array $translations
     * @param string $file
     */
    protected function addTranslation($string, array $translations, $file)
    {
        if (!isset($this->written[$file][$string])) {

            $data = array($string);
            if (isset($translations[$string][0]) && $translations[$string][0] !== '') {
                $data = $translations[$string];
                array_unshift($data, $string);
            }

            gplcart_file_csv($file, $data);
            $this->written[$file][$string] = true;
        }
    }

    /**
     * Extracts strings wrapped in Gplcart.text()
     * @param string $content
     * @return array
     */
    public function parseJs($content)
    {
        $matches = array();
        $pattern = '~[^\w]Gplcart\s*\.\s*text\s*\(\s*((?:(?:\'(?:\\\\\'|[^\'])*\'|"(?:\\\\"|[^"])*")(?:\s*\+\s*)?)+)\s*[,\)]~s';
        preg_match_all($pattern, $content, $matches);

        $results = array();
        foreach ($matches[1] as $key => $strings) {
            foreach ((array) $strings as $key => $string) {
                $results[] = implode('', preg_split('~(?<!\\\\)[\'"]\s*\+\s*[\'"]~s', substr($string, 1, -1)));
            }
        }

        return $results;
    }

    /**
     * Creates context JS translation
     * @param string $content
     * @param string $langcode
     * @return boolean
     */
    public function createJsTranslation($content, $langcode = null)
    {
        $extracted = $this->parseJs($content);

        if (!empty($extracted)) {
            $file = $this->getFileJs($langcode);
            $translations = $this->loadTranslation($this->getFileCommon($langcode));
            foreach ($extracted as $string) {
                $this->addTranslation($string, $translations, $file);
            }
        }
    }

    /**
     * Removes cached translation files
     * @param string|null $langcode
     */
    public function refresh($langcode = null)
    {
        gplcart_file_delete($this->getDirectoryCompiled($langcode), array('csv'));

        $this->copyFile($langcode);
        $this->mergeModuleTranslations($langcode);
    }

    /**
     * Add translations from all enabled modules to common file
     * @param string|null $langcode
     */
    protected function mergeModuleTranslations($langcode = null)
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
