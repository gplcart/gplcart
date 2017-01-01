<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\tests\resources;

use ReflectionClass;
use ReflectionException;
use PHPUnit_Framework_TestCase;

class UnitTest extends PHPUnit_Framework_TestCase
{

    /**
     * The current testing object
     * @var object
     */
    protected $object;

    /**
     * An array of temporary created files
     * @var array
     */
    protected $files = array();

    /**
     * Constructor
     * @param null|string $name
     * @param array $data
     * @param staring $dname
     */
    public function __construct($name = null, array $data = array(), $dname = '')
    {
        parent::__construct($name, $data, $dname);
    }

    /**
     * Sets the testing object
     * @param string $class
     */
    protected function setInstance($class)
    {
        $this->object = $this->getInstance($class);
    }

    /**
     * Returns a class instance
     * @return object
     */
    protected function getInstance($class)
    {
        if (!class_exists($class)) {
            throw new ReflectionException("Class $class is not callable");
        }

        $reflection = new ReflectionClass($class);
        $constructor = $reflection->getConstructor();

        if (empty($constructor)) {
            return new $class;
        }

        $parameters = $constructor->getParameters();

        if (empty($parameters)) {
            return new $class;
        }

        $dependencies = array();
        foreach ($parameters as $parameter) {
            $parameter_class = $parameter->getClass();
            if (!empty($parameter_class)) {
                $dependencies[] = $this->getInstance($parameter_class->getName());
            }
        }

        return $reflection->newInstanceArgs($dependencies);
    }

    /**
     * Outputs debugging data
     * @param mixed $data
     */
    protected function dump($data)
    {
        $this->expectOutputString('');
        print_r($data);
    }

    /**
     * Creates a dummy file
     * @param string $ext
     * @param string $content
     * @return string
     */
    protected function createFile($ext, $content = null)
    {
        if (!isset($content)) {
            $content = $this->getRandomString(mt_rand(1, 1000));
        }

        $file = $this->getRandomFileName($ext);
        file_put_contents($file, $content);
        chmod($file, 0600);

        $this->addFile($file);
        return $file;
    }

    /**
     * Creates multiple files
     * @param array $exts
     */
    protected function createFiles(array $exts)
    {
        $created = 0;
        foreach ($exts as $ext) {
            $created++;
            $this->createFile($ext);
        }

        return $created;
    }

    /**
     * Returns a random temporary file name
     * @param string $ext
     * @return string
     */
    protected function getRandomFileName($ext)
    {
        $random = $this->getRandomString();
        return GC_TEST_DIR . "/files/unit-test-$random.$ext";
    }

    /**
     * Clears all temporary created files
     */
    protected function clearFiles()
    {
        foreach ($this->files as $file) {
            unlink($file);
        }

        $this->files = array();
    }

    /**
     * Adds a file to an array of created files
     * @param string $file
     */
    protected function addFile($file)
    {
        $this->files[] = $file;
    }

    /**
     * Returns a randomized string
     * @param integer $length
     * @return string
     */
    protected function getRandomString($length = 16)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $clength = strlen($characters);
        $random = '';
        for ($i = 0; $i < $length; $i++) {
            $random .= $characters[rand(0, $clength - 1)];
        }
        return $random;
    }

}
