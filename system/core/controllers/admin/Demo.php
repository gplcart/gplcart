<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers\admin;

use core\Controller;
use core\classes\Curl;
use core\models\Job as ModelsJob;
use core\models\Import as ModelsImport;

/**
 * Handles incoming requests and outputs data related to demo content installation
 */
class Demo extends Controller
{

    /**
     * Import model instance
     * @var \core\models\Import $import
     */
    protected $import;

    /**
     * Job model instance
     * @var \core\models\Job $job
     */
    protected $job;

    /**
     * Curl class instance
     * @var \core\classes\Curl $curl
     */
    protected $curl;

    /**
     * Constructor
     * @param ModelsImport $import
     * @param ModelsJob $job
     * @param Curl $curl
     */
    public function __construct(ModelsImport $import, ModelsJob $job, Curl $curl)
    {
        parent::__construct();

        $this->job = $job;
        $this->curl = $curl;
        $this->import = $import;
    }

    /**
     * Displays the demo installation page
     */
    public function demo()
    {
        $this->controlAccessSuperAdmin();
        $this->config->reset('notification_demo');
        $this->checkConnection();

        if ($this->request->post('install')) {
            $this->importCategories();
        }

        if ($this->request->get('next') == 'product') {
            $this->importProducts();
        }

        $this->data['job'] = $this->getJob();

        $this->setTitleDemo();
        $this->outputDemo();
    }

    /**
     * Checks demo site connected and sets warning message if not
     */
    protected function checkConnection()
    {
        $header = $this->curl->header(GC_DEMO_URL);

        if (empty($header['header_size'])) {
            $this->setMessage('Unable to connect to external server that provides demo images. Check your internet connection', 'warning');
        }
    }

    /**
     * Renders the demo installation page
     */
    protected function outputDemo()
    {
        $this->output('tool/demo');
    }

    /**
     * Sets titles on the demo installation page
     */
    protected function setTitleDemo()
    {
        $this->setTitle($this->text('Install demo content'), false);
    }

    /**
     * Imports product categories from CSV files
     */
    protected function importCategories()
    {
        $message = array(
            'start' => $this->text('Starting to create product categories'),
            'process' => $this->text('Creating categories...')
        );

        $this->import('category', $message);
    }

    /**
     * Imports products from CSV file
     */
    protected function importProducts()
    {
        $message = array(
            'start' => $this->text('Starting to create products...'),
            'process' => $this->text('Creating demo products. It may take some time to download images from external site.')
        );

        $this->import('product', $message);
    }

    /**
     * Imports demo content from CSV files
     * @param string $operation_id
     * @param array $messages
     * @return null
     */
    protected function import($operation_id, array $messages = array())
    {
        $messages += array(
            'start' => $this->text('Starting'),
            'process' => $this->text('Processing'),
            'finish' => $this->text('Finished')
        );

        $operation = $this->import->getOperation($operation_id);

        if (empty($operation['csv']['template'])) {
            $this->data['form_errors']['operation'] = $this->text('Failed to load required import operations');
            return;
        }

        $options = array(
            'limit' => 10,
            'operation' => $operation,
            'filepath' => $operation['csv']['template'],
            'filesize' => filesize($operation['csv']['template'])
        );

        $job_id = $operation['job_id'];

        $this->job->delete($job_id);

        if (!empty($operation['log']['errors'])) {
            file_put_contents($operation['log']['errors'], '');
        }

        $job = array(
            'id' => $job_id,
            'total' => $options['filesize'],
            'message' => $messages,
            'redirect_message' => array('finish' => $this->text('Finished')),
            'operations' => array($job_id => array('arguments' => array($options)),
        ));

        if ($operation_id == 'category') {
            $job['redirect']['finish'] = $this->url('', array('next' => 'product'));
            //$job['redirect']['errors'] = $this->url('', array('next' => 'product'));
        }

        $this->job->set($job);
        $this->url->redirect('', array('job_id' => $job_id));
    }

}
