<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\tests\phpunit\support;

/**
 * Helper class to mock file system
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
        $this->temp = $this->getTempDir();
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
     */
    public function setCsv($name = 'test', array $data = array())
    {
        $data += array(
            'header' => array('Column 1', 'Column 2', 'Column 3', 'Column 4', 'Column 5'),
            'body' => array(array('Data 11', 'Data 12', 'Data 13', 'Data 14', 'Data 15'))
        );

        $file = "$this->temp/$name.csv";
        $fp = fopen($file, 'w');
        fputcsv($fp, $data['header']);

        foreach ($data['body'] as $row) {
            fputcsv($fp, $row);
        }

        fclose($fp);
        $this->created['csv'][] = $file;
    }

    /**
     * Creates .json files
     */
    public function setJson()
    {
        $file = "$this->temp/test.json";
        file_put_contents($file, json_encode(array('test')));
        $this->created['json'][] = $file;
    }

    /**
     * Creates ZIP files
     */
    public function setZip()
    {
        if (class_exists('ZipArchive')) {

            $zip = new \ZipArchive;
            $file = "$this->temp/test.zip";

            if ($zip->open($file, \ZipArchive::CREATE)) {
                $zip->addFromString('test.txt', 'test');
                $zip->close();
                $this->created['zip'][] = $file;
            }
        }
    }

    /**
     * Returns an array of created files
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

    /**
     * Returns the temporary file directory
     * @return string
     */
    public function getTempDir()
    {
        return ini_get('upload_tmp_dir') ?: sys_get_temp_dir();
    }

}
