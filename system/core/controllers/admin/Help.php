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
     * Displays help page(s)
     * @param null|string $filename
     */
    public function help($filename = null)
    {
        if (empty($filename)) {
            $this->index();
        }

        $this->page($filename);
    }

    /**
     * Displays the help sections overview page
     */
    protected function index()
    {
        $this->data['help_list'] = $this->getList();

        $this->setTitleIndex();
        $this->setBreadcrumbIndex();
        $this->outputIndex();
    }

    /**
     * Renders the help sections overview page
     */
    protected function outputIndex()
    {
        $this->output('help/list');
    }

    /**
     * Sets breadcrumbs on the help sections overview page
     */
    protected function setBreadcrumbIndex()
    {
        $this->setBreadcrumb(array(
            'text' => $this->text('Dashboard'),
            'url' => $this->url('admin')));
    }

    /**
     * Sets titles on the help sections overview page
     */
    protected function setTitleIndex()
    {
        $this->setTitle('Help');
    }

    /**
     * Displays the help page
     * @param string $filename
     */
    protected function page($filename)
    {
        $contents = $this->getPageContents($filename);
        $text = end($contents);
        $this->data['text'] = $this->removeHeader($text);

        $this->setTitlePage($text);
        $this->setBreadcrumbPage();
        $this->outputPage($text);
    }

    /**
     * Renders a help page
     * @param string $text
     */
    protected function outputPage($text)
    {
        $this->output('help/page');
    }

    /**
     * Sets titles on the help page
     */
    protected function setTitlePage($text)
    {
        $header = $this->getHeader($text);

        if (empty($header)) {
            $this->setTitle('Help');
        } else {
            $this->setTitle($header);
        }
    }

    /**
     * Sets breadcrumbs on the help page
     */
    protected function setBreadcrumbPage()
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
    protected function getPageContents($filename)
    {
        $folder = $this->langcode ? $this->langcode : 'en';
        $file = GC_HELP_DIR . "/$folder/$filename.php";

        if (!is_readable($file)) {
            $this->outputError(404);
        }

        $contents = $this->getContents($file);

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
    protected function getHeader($text)
    {
        preg_match('#<h1[^>]*>(.*?)</h1>#i', $text, $match);
        return isset($match[1]) ? trim($match[1]) : '';
    }

    /**
     * Removes first H1 tag and its content from the main text
     * @param string $text
     * @return string
     */
    protected function removeHeader($text)
    {
        return preg_replace('~<h1>.*?</h1>~is', '', $text, 1);
    }

    /**
     * Explodes help text by divider to define a summary
     * @param string $file
     * @return array
     */
    protected function getContents($file)
    {
        return array_filter(array_map('trim', explode('<!--summary-->', $this->render($file, array(), true), 2)));
    }

    /**
     * Scans files in the current help directory and returns an array of headers
     * @return array
     */
    protected function getList()
    {
        $folder = $this->langcode ? $this->langcode : 'en';
        $directory = GC_HELP_DIR . "/$folder";

        if (!is_readable($directory)) {
            return array();
        }

        $list = array();

        $i = 1;
        foreach (Tool::scanFiles($directory, array('php')) as $file) {
            $contents = $this->getContents($file);

            if (empty($contents)) {
                continue;
            }

            $text = end($contents);
            $header = $this->getHeader($text);

            if (empty($header)) {
                $header = $this->text('Section @num', array('@num' => $i));
            }

            $filename = pathinfo($file, PATHINFO_FILENAME);
            $list[$header] = $this->url("admin/help/$filename");
            $i++;
        }

        ksort($list);
        return $list;
    }

}
