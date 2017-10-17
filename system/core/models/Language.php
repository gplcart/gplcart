<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Model;

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
     * The current language code
     * @var string
     */
    protected $langcode;

    /**
     * The current translation context
     * @var string
     */
    protected $context;

    /**
     * Whether translation files are prepared
     * @var bool
     */
    protected $prepared = false;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Set up a language
     * @param string|null $langcode
     * @param string|null $context
     * @return bool
     */
    public function set($langcode, $context)
    {
        $result = null;
        $this->hook->attach('language.set.before', $langcode, $context, $result, $this);

        if (isset($result)) {
            return $result;
        }

        $this->setContext($context);
        $this->setLangcode($langcode);
        $result = $this->prepareFiles($langcode);

        $this->hook->attach('language.set.after', $langcode, $context, $result, $this);
        return (bool) $result;
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
     * Set a language code
     * @param string $langcode
     * @return $this
     */
    public function setLangcode($langcode)
    {
        $this->langcode = $langcode;
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
     * Returns the current language code
     * @return string
     */
    public function getLangcode()
    {
        return $this->langcode;
    }

    /**
     * Prepare all necessary files for the language
     * @param string $langcode
     * @return bool
     */
    protected function prepareFiles($langcode)
    {
        if (empty($langcode) || $langcode === 'en') {
            return $this->prepared = false;
        }

        $main_file = $this->getFile($langcode);

        if (!is_file($main_file)) {
            return $this->prepared = false;
        }

        $dir = $this->getCompiledDirectory();

        if (!file_exists($dir) && !mkdir($dir, 0775, true)) {
            return $this->prepared = false;
        }

        $common_file = $this->getCommonFile($langcode);

        if (is_file($common_file)) {
            return $this->prepared = true;
        }

        $directory = dirname($common_file);
        if (!file_exists($directory) && !mkdir($directory, 0775, true)) {
            return $this->prepared = false;
        }

        return $this->prepared = copy($main_file, $common_file);
    }

    /**
     * Returns an array of languages
     * @param bool $enabled Return only enabled languages
     * @param bool $in_database Returns only languages that saved in the database
     * @return array
     */
    public function getList($enabled = false, $in_database = false)
    {
        $languages = &gplcart_static(gplcart_array_hash(array('language.list' => array($enabled, $in_database))));

        if (isset($languages)) {
            return $languages;
        }

        $iso = $this->getIso();
        $default_code = $this->getDefault();
        $default_data = $this->getDefaultData();
        $saved = $this->config->get('languages', array());
        $languages = array_replace_recursive($iso, $saved);

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
        }

        unset($language);

        $this->hook->attach('language.list', $languages, $this);

        foreach ($languages as $code => $language) {
            if ($enabled && empty($language['status'])) {
                unset($languages[$code]);
                continue;
            }

            if ($in_database && empty($language['in_database'])) {
                unset($languages[$code]);
            }
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
        return $this->config->get('language', 'en');
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
     * Returns a language directory
     * @param string|null $langcode
     * @return string
     */
    public function getDirectory($langcode = null)
    {
        if (!isset($langcode)) {
            $langcode = $this->langcode;
        }

        return GC_TRANSLATION_DIR . "/$langcode";
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
            $directory = $this->getModuleDirectory($module_id);
        }

        return "$directory/$langcode.csv";
    }

    /**
     * Returns the module translation directory
     * @param string $module_id
     * @return string
     */
    public function getModuleDirectory($module_id)
    {
        $directory = $this->config->getModuleDirectory($module_id);
        return "$directory/translations";
    }

    /**
     * Returns the path to a common translation file
     * @param string|null $langcode
     * @return string
     */
    public function getCommonFile($langcode = null)
    {
        if (!isset($langcode)) {
            $langcode = $this->langcode;
        }

        $directory = $this->getCompiledDirectory($langcode);
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
        $data += $default;

        $languages = $this->config->select('languages', array());
        $languages[$code] = array_intersect_key($data, $default);
        $this->config->set('languages', $languages);

        $result = true;
        $this->hook->attach('language.update.after', $code, $data, $result, $this);

        return (bool) $result;
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
        if (empty($this->langcode) || $this->langcode === 'en') {
            return $this->formatString($string, $arguments);
        }

        $context_file = $this->getContextFile($this->context, $this->langcode);
        $context_translations = $this->loadTranslation($context_file);

        if (isset($context_translations[$string])) {
            return $this->formatString($string, $arguments, $context_translations[$string]);
        }

        $common_file = $this->getCommonFile();
        $common_translations = $this->loadTranslation($common_file);

        if (isset($common_translations[$string])) {
            $this->addTranslation($string, $common_translations, $context_file);
            return $this->formatString($string, $arguments, $common_translations[$string]);
        }

        $this->addTranslation($string, $common_translations, $context_file);
        $this->addTranslation($string, $common_translations, $common_file);

        return $this->formatString($string, $arguments);
    }

    /**
     * Returns a context translation file
     * @param string $context
     * @param string $langcode
     * @return string
     */
    public function getContextFile($context, $langcode)
    {
        static $files = array();

        if (!isset($files["$context$langcode"])) {
            $filename = $this->getFilenameFromContext($context);
            $directory = $this->getCompiledDirectory($langcode);
            $files["$context$langcode"] = "$directory/$filename.csv";
        }

        return $files["$context$langcode"];
    }

    /**
     * Returns the path to a compiled JS translation file
     * @param string|null $langcode
     * @return string
     */
    public function getContextJsFile($langcode = null)
    {
        return $this->getContextFile('js', $langcode);
    }

    /**
     * Converts context into filename
     * @param string $context
     * @return string
     */
    protected function getFilenameFromContext($context)
    {
        $clean = gplcart_file_sanitize(strtolower(str_replace('/*', '_', "$context")), '-');
        return str_replace(array('-_', '_-'), '_', $clean);
    }

    /**
     * Returns a path to a directory containing compiled translations
     * @param string|null $langcode
     * @return string
     */
    public function getCompiledDirectory($langcode = null)
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
        $translations = &gplcart_static("language.translation.$file");

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
    public function loadJsTranslation($langcode = null)
    {
        $strings = $this->loadTranslation($this->getContextJsFile($langcode));

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
        $common_file = $this->getCommonFile($langcode);
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
        if ($this->prepared && !isset($this->written[$file][$string]) && !empty($this->context)) {

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
            $file = $this->getContextJsFile($langcode);
            $translations = $this->loadTranslation($this->getCommonFile($langcode));
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
        $result = null;
        $this->hook->attach('language.refresh.before', $langcode, $result, $this);

        if (isset($result)) {
            return $result;
        }

        gplcart_file_empty($this->getCompiledDirectory($langcode), array('csv'));

        gplcart_static_clear();

        $this->prepareFiles($langcode);
        $this->mergeModuleTranslations($langcode);

        $result = true;
        $this->hook->attach('language.refresh.after', $langcode, $result, $this);
        return $result;
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
        $data = gplcart_config_get(GC_CONFIG_LANGUAGE);

        if (isset($code)) {
            return isset($data[$code]) ? (array) $data[$code] : array();
        }

        return $data;
    }

}
