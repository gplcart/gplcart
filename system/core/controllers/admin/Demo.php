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
        $this->checkConnection();

        // First create categories
        if ($this->request->post('install')) {
            $this->import('category');
        }
        
        $next = (string) $this->request->get('next');

        // ... then products
        if ($next === 'product') {
            $this->import('product');
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
        $this->setTitle($this->text('Install demo content'));
    }

    /**
     * Imports demo content from CSV files
     * @param string $operation_id
     * @return null
     */
    protected function import($operation_id)
    {
        $operation = $this->import->getOperation($operation_id);

        $data = array(
            'limit' => 1,
            'operation' => $operation,
            'filepath' => $operation['csv']['template'],
            'filesize' => filesize($operation['csv']['template'])
        );

        $job = array(
            'data' => $data,
            'id' => $operation['job_id'],
            'total' => $data['filesize'],
        );

        if ($operation_id == 'category') {

            $job['message'] = array(
                'start' => $this->text('Starting to create product categories'),
                'process' => $this->text('Creating categories...')
            );

            // Go to create products
            $job['redirect']['finish'] = $this->url('', array('next' => 'product'));
        }

        if ($operation_id == 'product') {

            $job['message'] = array(
                'start' => $this->text('Starting to create demo products...'),
                'process' => $this->text('Creating demo products. It may take some time to download images from an external site.'),
            );

            $job['redirect_message'] = array(
                'finish' => $this->text('Finished. <a href="!href">See demo products</a>', array(
                    '!href' => $this->url('admin/content/product'))),
                'errors' => $this->text('An error occurred while creating demo products. A possible reason might be you have duplicated category names.'),
            );
        }

        $this->job->submit($job);
    }

}
