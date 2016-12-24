<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\models;

use core\Model;
use core\models\Language as LanguageModel;

/**
 * Provides methods to work with 3-d party libraries
 */
class Library extends Model
{

    /**
     * An array of validation errors
     * @var array
     */
    protected $errors = array();

    /**
     * Language model instance
     * @var \core\models\Language $language
     */
    protected $language;

    /**
     * Constructor
     * @param LanguageModel $language
     */
    public function __construct(LanguageModel $language)
    {
        parent::__construct();

        $this->language = $language;
    }

    /**
     * Returns an array of libraries
     * @return array
     */
    public function getList()
    {
        $libraries = &gplcart_cache('libraries');

        if (isset($libraries)) {
            return $libraries;
        }
        
        $_libraries = include_once GC_CONFIG_LIBRARY;

        $libraries = array();
        foreach ($_libraries as $library_id => $library) {

            $version = $this->getVersion($library);

            if (!isset($version)) {
                $vars = array('%name' => $library['name']);
                $error = $this->language->text('Unknown version for library %name', $vars);
                $this->errors[$library_id][] = $error;
                continue;
            }

            $library['version']['number'] = $version;

            if (!$this->checkDependencies($libraries, $library)) {
                continue;
            }
            
            $libraries[$library_id] = $library;

        }

        $this->hook->fire('library', $libraries);
        return $libraries;
    }

    /**
     * Returns library validation errors
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Checks library dependencies
     * @param array $libraries
     * @param array $library
     * @return boolean
     */
    protected function checkDependencies(array $libraries, array $library)
    {
        if (empty($library['dependencies'])) {
            return true;
        }

        foreach ($library['dependencies'] as $library_id => $version) {

            if (!isset($libraries[$library_id])) {
                $vars = array('%id' => $library_id);
                $error = $this->language->text('Missing required library %id', $vars);
                $this->errors[$library_id][] = $error;
                continue;
            }

            $components = $this->getVersionComponents($version);

            if (empty($components)) {
                $vars = array('%version' => $version, '%name' => $libraries[$library_id]['name']);
                $error = $this->language->text('Unknown version %version for required library %name', $vars);
                $this->errors[$library_id][] = $error;
                continue;
            }

            list($operator, $number) = $components;

            if (!$this->isCompatibleVersion($library, $number, $operator)) {
                $vars = array('%required' => $libraries[$library_id]['name'], '%dependent' => $library['name']);
                $error = $this->language->text('Required library %required is not compatible with %dependent', $vars);
                $this->errors[$library_id][] = $error;
            }
        }

        return !isset($error);
    }

    /**
     * Checks version compatibility using a version number and comparison operator
     * @param array $library
     * @param string $version
     * @param string $operator
     * @return boolean
     */
    protected function isCompatibleVersion(array $library, $version, $operator)
    {
        return version_compare($library['version']['number'], $version, $operator);
    }

    /**
     * Extracts an array of components from strings like ">= 1.0.0"
     * @param string $data
     * @return array
     */
    protected function getVersionComponents($data)
    {
        $string = str_replace(' ', '', $data);
        preg_match('/^.*?(?=\d)/', $string, $matches);

        $operatop = empty($matches[0]) ? '==' : $matches[0];

        $allowed = array('==', '=', '!=', '<>', '>', '<', '>=', '<=');

        if (!in_array($operatop, $allowed, true)) {
            return array();
        }

        $version = substr($string, strlen($operatop));

        if (preg_match('/^(\*|\d+(\.\d+){0,2}(\.\*)?)$/', $version) === 1) {
            return array($operatop, $version);
        }

        return array();
    }

    /**
     * Parses source code and returns a version number
     * @param array $library
     * @return null|string
     */
    public function getVersion(array $library)
    {
        if (isset($library['version']['number'])) {
            return $library['version']['number'];
        }

        if (empty($library['version']['file'])) {
            return null;
        }

        $file = "{$library['basepath']}/{$library['version']['file']}";

        if (!is_readable($file)) {
            return null;
        }

        // Check package.json, bower.json etc
        if (pathinfo($file, PATHINFO_EXTENSION) === 'json') {

            $json = file_get_contents($file);

            if (empty($json)) {
                return null;
            }

            $data = json_decode($json, true);

            if (isset($data['version'])) {
                return $data['version'];
            }
            return null;
        }

        $library['version'] += array(
            'pattern' => '',
            'lines' => 20, // Default depth
            'cols' => 200, // Default length
        );

        // Parse source code from the top to bottom
        $handle = fopen($file, 'r');

        while ($library['version']['lines'] && $line = fgets($handle, $library['version']['cols'])) {
            if (preg_match($library['version']['pattern'], $line, $version)) {
                fclose($handle);
                return $version[1];
            }
            $library['version']['lines'] --;
        }

        fclose($handle);
        return null;
    }

}
