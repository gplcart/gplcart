<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\tests\phpunit\support;

use UnexpectedValueException;
use gplcart\core\exceptions\File as FileException;

/**
 * Helper methods to test files
 */
class File
{

    /**
     * An array of created files
     * @var array
     */
    protected $created = array();

    /**
     * Temporary system file directory
     * @var string
     */
    protected $temp;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->temp = __DIR__ . '/tmp';
    }

    /**
     * Set a file
     * @param string $filename
     * @param string $content
     * @param null|string $group
     * @throws FileException
     * @throws UnexpectedValueException
     */
    public function setFile($filename, $content, $group = null)
    {
        $file = "$this->temp/$filename";

        if (file_exists($file) && !unlink($file)) {
            throw new FileException("File $file already exists");
        }

        $pathinfo = pathinfo($file);

        if (!isset($group)) {
            $group = $pathinfo['extension'];
        }

        if (!empty($pathinfo['dirname'])) {
            if (!file_exists($pathinfo['dirname']) && !mkdir($pathinfo['dirname'])) {
                throw new FileException("Failed to create directory {$pathinfo['dirname']}");
            }
        }

        if (file_put_contents($file, $content) === false) {
            throw new UnexpectedValueException("Failed to write a string to file $file");
        }

        $this->created[$group][] = $file;
    }

    /**
     * Create temporary images
     */
    public function setImage()
    {
        $formats = array('png', 'gif', 'jpeg');

        foreach ($formats as $format) {
            $im = imagecreate(100, 100);
            imagecolorallocate($im, 255, 255, 255);
            $path = "$this->temp/image.$format";
            call_user_func("image$format", $im, $path);
            imagedestroy($im);
            $this->created['image'][] = $path;
        }
    }

    /**
     * Creates CSV files
     * @param string $filename
     * @param array $data
     */
    public function setCsv($filename = 'test.csv', array $data = array())
    {
        $data += array(
            'header' => array('Column 1', 'Column 2', 'Column 3', 'Column 4', 'Column 5'),
            'body' => array(array('Data 11', 'Data 12', 'Data 13', 'Data 14', 'Data 15'))
        );

        $content = implode(',', $data['header']) . "\r\n";
        foreach ($data['body'] as $array) {
            $content .= implode(',', $array) . "\r\n";
        }

        $this->setFile($filename, $content);
    }

    /**
     * Creates .json files
     * @param string $filename
     * @param array $data
     */
    public function setJson($filename = 'test.json', array $data = array('test'))
    {
        $this->setFile($filename, json_encode($data));
    }

    /**
     * Creates ZIP files
     * @throws UnexpectedValueException
     */
    public function setZip()
    {
        $zip = new \ZipArchive;
        $file = "$this->temp/test.zip";

        if (!$zip->open($file, \ZipArchive::CREATE)) {
            throw new UnexpectedValueException("Failed to open ZIP file $file");
        }

        $zip->addFromString('test.txt', 'test');
        $zip->close();
        $this->created['zip'][] = $file;
    }

    /**
     * Returns an array of created files
     * @param string $type
     * @return array
     */
    public function getCreated($type)
    {
        return empty($this->created[$type]) ? array() : $this->created[$type];
    }

    /**
     * Delete all created files
     * @param string|null $type
     */
    public function deleteCreated($type = null)
    {
        if (!isset($type)) {
            foreach (array_keys($this->created) as $type) {
                $this->deleteCreated($type);
            }
        } else if (!empty($this->created[$type])) {
            foreach ($this->created[$type] as $file) {
                unlink($file);
            }
        }
    }

}
