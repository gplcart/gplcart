<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\backend;

use gplcart\core\models\Help as HelpModel;

/**
 * Handles incoming requests and outputs data related to help information
 */
class Help extends Controller
{

    /**
     * Help model instance
     * @var \gplcart\core\models\Help $help
     */
    protected $help;

    /**
     * Help file path
     * @var string
     */
    protected $data_file;

    /**
     * @param HelpModel $help
     */
    public function __construct(HelpModel $help)
    {
        parent::__construct();

        $this->help = $help;
    }

    /**
     * View a help item
     * @param string $hash
     */
    public function viewHelp($hash)
    {
        $this->setFileHelp($hash);
        $this->setTitleViewHelp();
        $this->setBreadcrumbViewHelp();

        $this->setData('help', $this->renderHelp());
        $this->outputViewHelp();
    }

    /**
     * Sets titles on the view help page
     */
    protected function setTitleViewHelp()
    {
        $meta = $this->help->getMeta($this->data_file);
        $this->setTitle($meta['title']);
    }

    /**
     * Sets breadcrumbs on the view help page
     */
    protected function setBreadcrumbViewHelp()
    {
        $breadcrumbs = array();

        $breadcrumbs[] = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')
        );

        $breadcrumbs[] = array(
            'url' => $this->url('admin/help'),
            'text' => $this->text('Help')
        );

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Render and output the view help page
     */
    protected function outputViewHelp()
    {
        $this->output('help/help');
    }

    /**
     * Sets help file path
     * @param string $hash
     */
    protected function setFileHelp($hash)
    {
        $this->data_file = gplcart_path_absolute(gplcart_string_decode($hash));

        if (!is_file($this->data_file)) {
            $this->outputHttpStatus(404);
        }
    }

    /**
     * Render a file
     * @return string
     */
    protected function renderHelp()
    {
        $rendered = $this->help->parse($this->data_file);
        return preg_replace('/<!--(.*)-->/Uis', '', $rendered);
    }

    /**
     * Displays the help list page
     */
    public function listHelp()
    {
        $this->setTitleListHelp();
        $this->setBreadcrumbListHelp();

        $this->setData('items', $this->getListHelp());
        $this->outputListHelp();
    }

    /**
     * Returns an array of help items
     * @return array
     */
    protected function getListHelp()
    {
        return $this->help->getList($this->langcode);
    }

    /**
     * Sets titles on the help list page
     */
    protected function setTitleListHelp()
    {
        $this->setTitle($this->text('Help'));
    }

    /**
     * Sets breadcrumbs on the help list page
     */
    protected function setBreadcrumbListHelp()
    {
        $breadcrumbs = array();

        $breadcrumbs[] = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')
        );

        $breadcrumbs[] = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')
        );

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Render and output the help list page
     */
    protected function outputListHelp()
    {
        $this->output('help/list');
    }

}
