<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Hook,
    gplcart\core\Module as ModuleCore;

/**
 * Manages basic behaviors and data related to UI translations
 */
class Translation
{

    /**
     * Hook class instance
     * @var \gplcart\core\Hook $hook
     */
    protected $hook;

    /**
     * Module class instance
     * @var \gplcart\core\Module $module
     */
    protected $module;

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
     * Array of processed translations
     * @var array
     */
    protected $written = array();

    /**
     * @param Hook $hook
     * @param Module $module
     */
    public function __construct(Hook $hook, ModuleCore $module)
    {
        $this->hook = $hook;
        $this->module = $module;
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
        $this->hook->attach('translation.set.before', $langcode, $context, $result, $this);

        if (isset($result)) {
            return $result;
        }

        $this->setContext($context);
        $this->setLangcode($langcode);

        $result = $this->prepareFiles($langcode);

        $this->hook->attach('translation.set.after', $langcode, $context, $result, $this);
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
     * Returns a language directory
     * @param string|null $langcode
     * @return string
     */
    public function getDirectory($langcode = null)
    {
        if (!isset($langcode)) {
            $langcode = $this->langcode;
        }

        return GC_DIR_TRANSLATION . "/$langcode";
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
        return $this->module->getDirectory($module_id) . "/translations";
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

        return $this->getCompiledDirectory($langcode) . "/$langcode.csv";
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
        $translations = &gplcart_static("translation.$file");

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
        foreach ($matches[1] as $strings) {
            foreach ((array) $strings as $string) {
                $results[] = implode('', preg_split('~(?<!\\\\)[\'"]\s*\+\s*[\'"]~s', substr($string, 1, -1)));
            }
        }

        return $results;
    }

    /**
     * Creates context JS translation
     * @param string $content
     * @param string|null $langcode
     * @return boolean
     */
    public function createJsTranslation($content, $langcode = null)
    {
        $extracted = $this->parseJs($content);

        if (empty($extracted)) {
            return false;
        }

        $file = $this->getContextJsFile($langcode);
        $translations = $this->loadTranslation($this->getCommonFile($langcode));
        foreach ($extracted as $string) {
            $this->addTranslation($string, $translations, $file);
        }

        return true;
    }

    /**
     * Removes cached translation files
     * @param string|null $langcode
     * @return bool
     */
    public function refresh($langcode = null)
    {
        $result = null;
        $this->hook->attach('translation.refresh.before', $langcode, $result, $this);

        if (isset($result)) {
            return $result;
        }

        gplcart_file_empty($this->getCompiledDirectory($langcode), array('csv'));
        gplcart_static_clear();

        $this->prepareFiles($langcode);
        $this->mergeModuleTranslations($langcode);

        $result = true;
        $this->hook->attach('translation.refresh.after', $langcode, $result, $this);
        return $result;
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
     * Add translations from all enabled modules to common file
     * @param string|null $langcode
     */
    protected function mergeModuleTranslations($langcode = null)
    {
        $modules = $this->module->getEnabled();

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

}
