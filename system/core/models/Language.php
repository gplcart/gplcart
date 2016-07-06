<?php

/**
 * @package GPL Cart core
 * @version $Id$
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\models;

use core\Hook;
use core\Route;
use core\Config;
use core\classes\Po;
use core\classes\Tool;
use core\classes\Cache;
use core\classes\translit\Translit;

/**
 * Manages basic behaviors and data related to languages and their translations
 */
class Language
{

    /**
     * Translit library instance
     * @var \libraries\translit\Translit $translit;
     */
    protected $translit;

    /**
     * Hook class instance
     * @var \core\Hook $hook
     */
    protected $hook;

    /**
     * PO class instance
     * @var \core\classes\Po $po
     */
    protected $po;

    /**
     * Config class instance
     * @var \core\Config $config
     */
    protected $config;

    /**
     * PDO class instance
     * @var \core\classes\Database $db
     */
    protected $db;

    /**
     * Array of trings to be translated
     * @var array
     */
    protected $translatable = array();

    /**
     * Array of translated strings
     * @var array
     */
    protected $translation = array();

    /**
     * Array of untranslated strings
     * @var array
     */
    protected $untranslated = array();

    /**
     * Current language code
     * @var boolean
     */
    protected $langcode = '';

    /**
     * Path to directory that keeps complied php translations
     * for the current language
     * @var string
     */
    protected $compiled_directory_php = '';

    /**
     * Path to directory that keeps complied js translations
     * for the current language
     * @var string
     */
    protected $compiled_directory_js = '';

    /**
     * Path to a php file that contains the current compiled translation
     * @var string
     */
    protected $compiled_file_php = '';

    /**
     * Path to a js file that contains the current compiled translation in JSON
     * @var string
     */
    protected $compiled_file_js = '';

    /**
     * Compiled translation filename made from the current route pattern
     * @var string
     */
    protected $compiled_filename = '';

    /**
     * Constructor
     * @param Translit $translit
     * @param Hook $hook
     * @param Po $po
     * @param Route $route
     * @param Config $config
     */
    public function __construct(Translit $translit, Hook $hook, Po $po,
                                Route $route, Config $config)
    {
        $this->po = $po;
        $this->hook = $hook;
        $this->route = $route;
        $this->config = $config;
        $this->translit = $translit;
        $this->db = $this->config->getDb();
        $this->langcode = $this->route->getLangcode();

        if (!empty($this->langcode)) {
            $this->compiled_directory_php = GC_LOCALE_DIR . "/{$this->langcode}/compiled";
            $this->compiled_directory_js = GC_LOCALE_JS_DIR . "/{$this->langcode}";

            if (!file_exists($this->compiled_directory_php)) {
                mkdir($this->compiled_directory_php, 0755, true);
            }

            if (!file_exists($this->compiled_directory_js)) {
                mkdir($this->compiled_directory_js, 0755, true);
            }

            $current_route = $this->route->getCurrent();
            $this->compiled_filename = md5($current_route['pattern']);
            $this->compiled_file_php = "{$this->compiled_directory_php}/{$this->compiled_filename}.php";
            $this->compiled_file_js = "{$this->compiled_directory_js}/{$this->compiled_filename}.js";
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

        $available = $this->getAvailable();
        $saved = $this->config->get('languages', array());
        $default = $this->getDefault();

        $languages = Tool::merge($available, $saved);

        foreach ($languages as $code => &$language) {
            $language['default'] = ($code == $default);
            $language['code'] = $code;
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
            'name' => !empty($data['name']) ? $data['name'] : $data['code'],
            'native_name' => !empty($data['native_name']) ? $data['native_name'] : $data['code'],
            'status' => !empty($data['status']),
            'default' => !empty($data['default'])
        );

        $languages = $this->getAll();

        $languages[$data['code']] = $values;
        $this->config->set('languages', $languages);

        if ($values['default']) {
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
     * Loads a translation for the current language
     * @return array
     */
    public function load()
    {
        if (empty($this->langcode)) {
            return array();
        }

        if (file_exists($this->compiled_file_php)) {
            $this->translation = include $this->compiled_file_php;
            return $this->translation;
        }

        $this->translation = $this->loadPo($this->langcode);
        return $this->translation;
    }

    /**
     * Converts .po file into PHP array
     * @param string $langcode
     * @return array
     */
    public function loadPo($langcode)
    {
        $compiled_file = GC_LOCALE_DIR . "/$langcode/compiled/po.php";

        if (file_exists($compiled_file)) {
            return include $compiled_file;
        }

        $file = GC_LOCALE_DIR . "/$langcode/LC_MESSAGES/$langcode.po";

        if (file_exists($file)) {
            $po = $this->po->read($file);
            file_put_contents($compiled_file, '<?php return ' . var_export($po, true) . ';');
            chmod($compiled_file, 0644);
            return $po;
        }

        return array();
    }

    /**
     * Translates a string
     * @param string $string
     * @param array $arguments
     * @return string
     */
    public function text($string = null, array $arguments = array())
    {
        if (!isset($string)) {
            $this->compile();
            return;
        }

        $this->translatable[] = $string;

        if (isset($this->translation[$string])) {
            if (isset($this->translation[$string]['msgstr'][0])) {
                $string = $this->translation[$string]['msgstr'][0];
            }
        } else {
            $this->untranslated[] = $string;
        }

        return Tool::formatString($string, $arguments);
    }

    /**
     * Converts .po file into .php file
     * @return boolean
     */
    public function compile()
    {
        if (empty($this->langcode) || empty($this->untranslated) || empty($this->translatable)) {
            return false;
        }

        if (file_exists($this->compiled_file_php)) {
            // Cannot use automatic recompilation. Redirect issue
            return false;
        }

        $po = $this->loadPo($this->langcode);
        $strings = array_intersect_key($po, array_flip($this->translatable));

        $untranslated = array_flip($this->untranslated);
        $save = Tool::merge($untranslated, $strings);

        file_put_contents($this->compiled_file_php, '<?php return ' . var_export($save, true) . ';');
        file_put_contents($this->compiled_file_js, 'GplCart.translations = ' . json_encode($save) . ';');

        chmod($this->compiled_file_php, 0644);
        chmod($this->compiled_file_js, 0644);
        return true;
    }

    /**
     * Returns a path to the current compiled php translation file
     * @return string
     */
    public function getCompiledPhp()
    {
        return $this->compiled_file_php;
    }

    /**
     * Returns a path (either full or relative to root) to the current compiled js translation file
     * @param boolean $full
     * @return string
     */
    public function getCompiledJs($full = false)
    {
        if ($full) {
            return $this->compiled_file_js;
        }

        return trim(str_replace(GC_ROOT_DIR, '', $this->compiled_file_js), '/');
    }

    /**
     * Resets cached translations
     * @param string $langcode
     */
    public function refresh($langcode)
    {
        Tool::deleteFiles($this->compiled_directory_php, array('php'));
        Tool::deleteFiles($this->compiled_directory_js, array('js'));
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
