<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace tests\unit\system\core\handlers\validator;

use tests\resources\UnitTest;

/**
 * Test cases for file validators
 */
class FileTypeTest extends UnitTest
{

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        /* @var $object \core\handlers\validator\FileType */
        $this->setInstance('core\\handlers\\validator\\FileType');
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        $this->clearFiles();
    }

    /**
     * @covers core\handlers\validator\FileType::image
     * @group validators
     */
    public function testImage()
    {
        $exts = array('jpeg', 'png', 'gif');
        foreach ($exts as $ext) {
            $this->createImage($ext);
        }

        $numreal = count($exts);
        $numfake = $this->createFakeFiles();
        $this->assertFileTypeTest($numreal, $numfake, 'image');
    }

    /**
     * @covers core\handlers\validator\FileType::csv
     * @group validators
     */
    public function testCsv()
    {
        $numreal = $this->createFiles(array('csv', 'tsv'));
        $numfake = $this->createFakeFiles();
        $this->assertFileTypeTest($numreal, $numfake, 'csv');
    }

    /**
     * @covers core\handlers\validator\FileType::zip
     * @group validators
     */
    public function testZip()
    {
        $this->createZip();
        $numreal = 1;
        $numfake = $this->createFakeFiles();
        $this->assertFileTypeTest($numreal, $numfake, 'zip');
    }

    /**
     * Helper. Performs test case assertion
     * @param integer $numreal
     * @param integer $numfake
     * @param string $method
     */
    protected function assertFileTypeTest($numreal, $numfake, $method)
    {
        $this->assertEquals($numfake + $numreal, count($this->files));

        $valid = $invalid = $index = 0;
        foreach ($this->files as $file) {

            $index++;
            $result = $this->object->{$method}($file, array());
            if ($result === true && $index <= $numreal) {
                $valid++;
                continue;
            }

            $invalid++;
        }

        $this->assertEquals($numreal, $valid);
        $this->assertEquals($numfake, $invalid);
    }

    /**
     * Helper. Creates a dummy image
     * @param string $ext
     * @return boolean|string
     */
    protected function createImage($ext)
    {
        $im = imagecreatetruecolor(100, 100);

        $file = $this->getRandomFileName($ext);
        call_user_func_array("image$ext", array($im, $file));
        imagedestroy($im);

        $this->addFile($file);
        return $file;
    }

    /**
     * Helper. Creates a dummy ZIP file
     * @return string
     */
    protected function createZip()
    {
        $file = $this->getRandomFileName('zip');

        $zip = new \ZipArchive();
        $zip->open($file, \ZipArchive::CREATE);
        $zip->addFromString('test', 'test');
        $zip->close();
        $this->addFile($file);
        return $file;
    }

    /**
     * Helper. Creates dummy files
     * @return array
     */
    protected function createFakeFiles()
    {
        $exts = array('exe', 'jar', 'php', 'js', 'swf');
        return $this->createFiles($exts);
    }

}
