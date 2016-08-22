<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers\admin;

use core\Controller;
use core\classes\Tool;

/**
 * Handles incoming requests and outputs data related to help documentation
 */
class Help extends Controller
{

    /**
     * Controller
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Displays the help sections overview page
     */
    public function listHelp()
    {
        $items = $this->getListHelp();
        $this->setData('help_list', $items);

        $this->setTitleListHelp();
        $this->setBreadcrumbListHelp();
        $this->outputListHelp();
    }

    /**
     * Renders the help sections overview page
     */
    protected function outputListHelp()
    {
        $this->output('help/list');
    }

    /**
     * Sets breadcrumbs on the help sections overview page
     */
    protected function setBreadcrumbListHelp()
    {
        $this->setBreadcrumb(array(
            'text' => $this->text('Dashboard'),
            'url' => $this->url('admin')));
    }

    /**
     * Sets titles on the help sections overview page
     */
    protected function setTitleListHelp()
    {
        $this->setTitle('Help');
    }

    /**
     * Displays the help page
     * @param string $filename
     */
    public function pageHelp($filename)
    {
        $contents = $this->getPageContentsHelp($filename);
        $text = end($contents);
        $body = $this->getBodyHelp($text);

        $this->setData('text', $body);

        $this->setTitlePageHelp($text);
        $this->setBreadcrumbPageHelp();
        $this->outputPageHelp($text);
    }

    /**
     * Renders a help page
     * @param string $text
     */
    protected function outputPageHelp($text)
    {
        $this->output('help/page');
    }

    /**
     * Sets titles on the help page
     */
    protected function setTitlePageHelp($text)
    {
        $header = $this->getHeaderHelp($text);

        if (empty($header)) {
            return $this->setTitle('Help');
        }

        $this->setTitle($header);
    }

    /**
     * Sets breadcrumbs on the help page
     */
    protected function setBreadcrumbPageHelp()
    {
        $this->setBreadcrumb(array(
            'text' => $this->text('Dashboard'),
            'url' => $this->url('admin')));

        $this->setBreadcrumb(array(
            'text' => $this->text('Help'),
            'url' => $this->url('admin/help')));
    }

    /**
     * Returns an array containing help body and optional annotation
     * @param string $filename
     * @return array
     */
    protected function getPageContentsHelp($filename)
    {
        $folder = $this->langcode ? $this->langcode : 'en';
        $file = GC_HELP_DIR . "/$folder/$filename.php";

        if (!is_readable($file)) {
            $this->outputError(404);
        }

        $contents = $this->getContentsHelp($file);

        if (empty($contents)) {
            $this->outputError(404);
        }

        return $contents;
    }

    /**
     * Extracts a string containing page header from the first H1 tag
     * @param string $text
     * @return string
     */
    protected function getHeaderHelp($text)
    {
        preg_match('#<h1[^>]*>(.*?)</h1>#i', $text, $match);
        return isset($match[1]) ? trim($match[1]) : '';
    }

    /**
     * Removes first H1 tag and its content from the main text
     * @param string $text
     * @return string
     */
    protected function getBodyHelp($text)
    {
        return preg_replace('~<h1>.*?</h1>~is', '', $text, 1);
    }

    /**
     * Explodes help text by divider to define a summary
     * @param string $file
     * @return array
     */
    protected function getContentsHelp($file)
    {
        $text = $this->render($file, array(), true);
        return $this->explodeText($text);
    }

    /**
     * Scans files in the current help directory and returns an array of headers
     * @return array
     */
    protected function getListHelp()
    {
        $folder = $this->langcode ? $this->langcode : 'en';
        $directory = GC_HELP_DIR . "/$folder";

        if (!is_readable($directory)) {
            return array();
        }

        $files = Tool::scanFiles($directory, array('php'));

        if (empty($files)) {
            return array();
        }

        $list = array();

        $i = 1;
        foreach ($files as $file) {
            $contents = $this->getContentsHelp($file);

            if (empty($contents)) {
                continue;
            }

            $text = end($contents);
            $header = $this->getHeaderHelp($text);

            if (empty($header)) {
                $header = $this->text('Page @num', array('@num' => $i));
            }

            $filename = pathinfo($file, PATHINFO_FILENAME);
            $list[$header] = $this->url("admin/help/$filename");
            $i++;
        }

        ksort($list);
        return $list;
    }

}
