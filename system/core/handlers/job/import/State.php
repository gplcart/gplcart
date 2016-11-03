<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\handlers\job\import;

use core\classes\Csv;
use core\classes\Tool;
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
     * @return array
     */
    public function process(array $job, $done, array $context)
    {
        $operation = $job['data']['operation'];
        $this->header = $operation['csv']['header'];

        $this->csv->setFile($job['data']['filepath'], $job['data']['filesize'])
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
        $result = $this->import($rows, $line, $job);
        $line += count($rows);
        $bytes = empty($position) ? $job['total'] : $position;

        $errors = $this->import->getErrors($result['errors'], $operation);

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
     * @param array $job
     * @return array
     */
    public function import(array $rows, $line, array $job)
    {
        $inserted = 0;
        $updated = 0;
        $errors = array();

        foreach ($rows as $index => $row) {

            $line += $index;
            $data = array_filter(array_map('trim', $row));
            $update = (isset($data['state_id']) && is_numeric($data['state_id']));

            if ($update && !$this->user->access('state_edit')) {
                continue;
            }

            if (!$update && !$this->user->access('state_add')) {
                continue;
            }

            if (!$this->validateName($data, $errors, $line)) {
                continue;
            }

            if (!$this->validateCode($data, $errors, $line)) {
                continue;
            }

            if (!$this->validateCountry($data, $errors, $line)) {
                continue;
            }

            $this->validateStatus($data, $errors, $line);

            if (!empty($job['data']['unique']) && !$this->validateUnique($data, $errors, $line)) {
                continue;
            }

            if ($update) {
                $updated += $this->update($data);
                continue;
            }

            $inserted += $this->add($data);
        }

        return array('inserted' => $inserted, 'updated' => $updated, 'errors' => $errors);
    }

    /**
     * Validates status
     * @param array $data
     * @param array $errors
     * @param integer $line
     */
    protected function validateStatus(array &$data, array &$errors, $line)
    {
        if (isset($data['status'])) {
            $data['status'] = Tool::toBool($data['status']);
        }
    }

    /**
     * Validates state country
     * @param array $data
     * @param array $errors
     * @param integer $line
     * @return boolean
     */
    protected function validateCountry(array &$data, array &$errors, $line)
    {
        if (isset($data['country']) && !$this->country->get($data['country'])) {
            $errors[] = $this->language->text('Line @num: @error', array(
                '@num' => $line,
                '@error' => $this->language->text('Country @code not found', array('@code' => $data['country']))));
            return false;
        }

        return true;
    }

    /**
     * Validates state code
     * @param array $data
     * @param array $errors
     * @param integer $line
     */
    protected function validateCode(array &$data, array &$errors, $line)
    {
        if (isset($data['code']) && mb_strlen($data['code']) > 255) {
            $errors[] = $this->language->text('Line @num: @error', array(
                '@num' => $line,
                '@error' => $this->language->text('State code must not be longer than 255 characters')));
            return false;
        }

        return true;
    }

    /**
     * Validates state name
     * @param array $data
     * @param array $errors
     * @param integer $line
     * @return boolean
     */
    protected function validateName(array &$data, array &$errors, $line)
    {
        if (isset($data['name']) && mb_strlen($data['name']) > 255) {
            $errors[] = $this->language->text('Line @num: @error', array(
                '@num' => $line,
                '@error' => $this->language->text('State name must not be longer than 255 characters')));
            return false;
        }

        return true;
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

    /**
     * Validates the state is unique
     * @param array $data
     * @param array $errors
     * @param integer $line
     */
    protected function validateUnique(array &$data, array &$errors, $line)
    {
        if ($this->stateExists($data['name'], $data['code'], $data['country'])) {
            $errors[] = $this->language->text('Line @num: @error', array(
                '@num' => $line,
                '@error' => $this->language->text('State @name already exists', array('@name' => $data['name']))));
            return false;
        }

        return true;
    }

    /**
     * Adds a new country state
     * @param array $data
     */
    protected function add(array $data)
    {
        $result = $this->state->add($data);
        return empty($result) ? 0 : 1;
    }

    /**
     * Updates a state
     * @param array $data
     */
    protected function update(array $data)
    {
        return (int) $this->state->update($data['state_id'], $data);
    }

}
