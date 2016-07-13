<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\handlers\job\import;

use core\classes\Csv;
use core\models\User as ModelsUser;
use core\models\State as ModelsState;
use core\models\Import as ModelsImport;
use core\models\Country as ModelsCountry;
use core\models\Language as ModelsLanguage;

/**
 * Imports country states
 */
class State
{

    /**
     * Import model instance
     * @var \core\models\Import $import
     */
    protected $import;

    /**
     * Language model instance
     * @var \core\models\Language $language
     */
    protected $language;

    /**
     * CSV class instance
     * @var \core\classes\Csv $csv
     */
    protected $csv;

    /**
     * State model instance
     * @var \core\models\State $state
     */
    protected $state;

    /**
     * Country model instance
     * @var \core\models\Country $country
     */
    protected $country;

    /**
     * User model instance
     * @var \core\models\User $user
     */
    protected $user;

    /**
     * Header mapping
     * @var array
     */
    protected $header = array();

    /**
     * Constructor
     * @param ModelsImport $import
     * @param ModelsLanguage $language
     * @param ModelsUser $user
     * @param ModelsState $state
     * @param ModelsCountry $country
     * @param Csv $csv
     */
    public function __construct(ModelsImport $import, ModelsLanguage $language,
            ModelsUser $user, ModelsState $state, ModelsCountry $country,
            Csv $csv)
    {
        $this->csv = $csv;
        $this->user = $user;
        $this->state = $state;
        $this->import = $import;
        $this->country = $country;
        $this->language = $language;
    }

    /**
     * Processes one AJAX requests
     * @param array $job
     * @param integer $done
     * @param array $context
     * @param array $options
     * @return array
     */
    public function process(array $job, $done, array $context, array $options)
    {
        $import_operation = $options['operation'];
        $this->header = $import_operation['csv']['header'];

        $this->csv->setFile($options['filepath'], $options['filesize'])
                ->setHeader($this->header)
                ->setLimit($this->import->getLimit())
                ->setDelimiter($this->import->getCsvDelimiter());

        $offset = isset($context['offset']) ? $context['offset'] : 0;
        $line = isset($context['line']) ? $context['line'] : 2; // 2 - skip 0 and header row

        if (empty($offset)) {
            $this->csv->skipHeader();
        } else {
            $this->csv->setOffset($offset);
        }

        $rows = $this->csv->parse();

        if (empty($rows)) {
            return array('done' => $job['total']);
        }

        $position = $this->csv->getOffset();
        $result = $this->import($rows, $line, $options);
        $line += count($rows);
        $bytes = empty($position) ? $job['total'] : $position;

        $errors = $this->import->getErrors($result['errors'], $import_operation);

        return array(
            'done' => $bytes,
            'increment' => false,
            'errors' => $errors['count'],
            'updated' => $result['updated'],
            'inserted' => $result['inserted'],
            'context' => array('offset' => $position, 'line' => $line));
    }

    /**
     * Imports country states
     * @param array $rows
     * @param integer $line
     * @param array $options
     * @return array
     */
    public function import(array $rows, $line, array $options)
    {
        $inserted = 0;
        $updated = 0;
        $errors = array();

        foreach ($rows as $index => $row) {
            $line += $index;
            $data = array_filter(array_map('trim', $row));

            // Validate/prepare values
            if (isset($data['name']) && mb_strlen($data['name']) > 255) {
                $errors[] = $this->language->text('Line @num: @error', array(
                    '@num' => $line,
                    '@error' => $this->language->text('State name must not be longer than 255 characters')));
                continue;
            }

            if (isset($data['code']) && mb_strlen($data['code']) > 255) {
                $errors[] = $this->language->text('Line @num: @error', array(
                    '@num' => $line,
                    '@error' => $this->language->text('State code must not be longer than 255 characters')));
                continue;
            }

            if (isset($data['country']) && !$this->country->get($data['country'])) {
                $errors[] = $this->language->text('Line @num: @error', array(
                    '@num' => $line,
                    '@error' => $this->language->text('Country @code not found', array('@code' => $data['country']))));
                continue;
            }

            if (isset($data['status'])) {
                $data['status'] = $this->import->toBool($data['status']);
            }

            if (isset($data['state_id'])) {
                if (is_numeric($data['state_id']) && $this->user->access('state_edit')) {
                    if ($this->state->update($data['state_id'], $data)) {
                        $updated++;
                    }
                }
                continue;
            }

            if (!$this->user->access('state_add')) {
                continue;
            }

            // Add a new record
            if ((count($data) + 1) != count($this->header)) {
                $errors[] = $this->language->text('Line @num: @error', array(
                    '@num' => $line,
                    '@error' => $this->language->text('Wrong format')));
                continue;
            }

            if ($this->stateExists($data['name'], $data['code'], $data['country'])) {
                $errors[] = $this->language->text('Line @num: @error', array(
                    '@num' => $line,
                    '@error' => $this->language->text('State @name already exists', array('@name' => $data['name']))));
                continue;
            }

            $added = $this->state->add($data);

            if (!empty($added)) {
                $inserted++;
            }
        }

        return array('inserted' => $inserted, 'updated' => $updated, 'errors' => $errors);
    }

    /**
     * Check if a state already exists in the database
     * @param string $name
     * @param string $code
     * @param string $country
     * @return boolean
     */
    protected function stateExists($name, $code, $country)
    {
        $states = $this->state->getList(array(
            'name' => $name,
            'code' => $code,
            'country' => $country));

        foreach ($states as $state) {
            if ($state['name'] === $name) {
                return true;
            }
        }

        return false;
    }

}
