<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers\admin;

use core\helpers\Tool;
use core\controllers\admin\Controller as BackendController;

/**
 * Handles incoming requests and outputs data related to help documentation
 */
class Help extends BackendController
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
     * Sets titles on the help sections overview page
     */
    protected function setTitleListHelp()
    {
        $this->setTitle('Help');
    }

    /**
     * Sets breadcrumbs on the help sections overview page
     */
    protected function setBreadcrumbListHelp()
    {
        $breadcrumb = array(
            'text' => $this->text('Dashboard'),
            'url' => $this->url('admin')
        );

        $this->setBreadcrumb($breadcrumb);
    }

    /**
     * Renders the help sections overview page
     */
    protected function outputListHelp()
    {
        $this->output('help/list');
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
        $this->outputPageHelp();
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
     * Removes first H1 tag and its content from the main text
     * @param string $text
     * @return string
     */
    protected function getBodyHelp($text)
    {
        return preg_replace('~<h1>.*?</h1>~is', '', $text, 1);
    }

    /**
     * Sets titles on the help page
     * @param $text
     * @return string
     */
    protected function setTitlePageHelp($text)
    {
        $header = $this->getHeaderHelp($text);

        if (empty($header)) {
            return $this->setTitle('Help');
        }

        return $this->setTitle($header);
    }

    /**
     * Sets breadcrumbs on the help page
     */
    protected function setBreadcrumbPageHelp()
    {
        $breadcrumbs = array();

        $breadcrumbs[] = array(
            'text' => $this->text('Dashboard'),
            'url' => $this->url('admin')
        );

        $breadcrumbs[] = array(
            'text' => $this->text('Help'),
            'url' => $this->url('admin/help')
        );

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Renders a help page
     */
    protected function outputPageHelp()
    {
        $this->output('help/page');
    }

}
