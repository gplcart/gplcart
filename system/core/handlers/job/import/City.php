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
use core\models\City as ModelsCity;
use core\models\State as ModelsState;
use core\models\Import as ModelsImport;
use core\models\Language as ModelsLanguage;

/**
 * Provides methods to import cities
 */
class City
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
     * City model instance
     * @var \core\models\City $city
     */
    protected $city;

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
     * @param ModelsState $state
     * @param ModelsCity $city
     * @param ModelsUser $user
     * @param Csv $csv
     */
    public function __construct(ModelsImport $import, ModelsLanguage $language,
            ModelsState $state, ModelsCity $city, ModelsUser $user, Csv $csv)
    {
        $this->csv = $csv;
        $this->user = $user;
        $this->city = $city;
        $this->state = $state;
        $this->import = $import;
        $this->language = $language;
    }

    /**
     *
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
     * Performs import
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
            $update = (isset($data['city_id']) && is_numeric($data['city_id']));

            if ($update && !$this->user->access('city_edit')) {
                continue;
            }

            if (!$update && !$this->user->access('city_add')) {
                continue;
            }

            if (!$this->validateName($data, $errors, $line)) {
                continue;
            }

            $this->validateStatus($data, $errors, $line);

            if ($update) {
                $updated += $this->update($data);
                continue;
            }

            if (!$this->validateState($data, $errors, $line)) {
                continue;
            }

            if (!$this->validateCity($data, $errors, $line)) {
                continue;
            }

            $inserted += $this->city->add($data);
        }

        return array('inserted' => $inserted, 'updated' => $updated, 'errors' => $errors);
    }

    /**
     * Whether the city exists
     * @param string $name
     * @param string $state_code
     * @param string $country
     * @return boolean
     */
    protected function cityExists($name, $state_code, $country)
    {
        $cities = $this->city->getList(array(
            'state_code' => $state_code,
            'country' => $country,
            'name' => $name));

        foreach ($cities as $city) {
            if ($city['name'] === $name) {
                return true;
            }
        }

        return false;
    }

    /**
     * Validates city name
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
                '@error' => $this->language->text('City name must not be longer than 255 characters')));
            return false;
        }

        return true;
    }

    /**
     * Validates city status
     * @param array $data
     * @param array $errors
     * @param integer $line
     */
    protected function validateStatus(array &$data, array &$errors, $line)
    {
        if (isset($data['status'])) {
            $data['status'] = $this->import->toBool($data['status']);
        }
    }

    /**
     * Validates country state
     * @param array $data
     * @param array $errors
     * @param integer $line
     * @return boolean
     */
    protected function validateState(array &$data, array &$errors, $line)
    {
        $state = $this->state->getByCode($data['state_code'], $data['country']);

        if (empty($state['state_id'])) {
            $errors[] = $this->language->text('Line @num: @error', array(
                '@num' => $line,
                '@error' => $this->language->text('State code @code does not exist for country @country', array(
                    '@code' => $data['state_code'],
                    '@country' => $data['country']))));
            return false;
        }

        $data['state_id'] = $state['state_id'];
        return true;
    }

    /**
     * Validates a city
     * @param array $data
     * @param array $errors
     * @param integer $line
     * @return boolean
     */
    protected function validateCity(array &$data, array &$errors, $line)
    {
        $exists = $this->cityExists($data['name'], $data['state_code'], $data['country']);

        if (!$exists) {
            return true;
        }

        $errors[] = $this->language->text('Line @num: @error', array(
            '@num' => $line,
            '@error' => $this->language->text('City @name already exists for state @state and country @country', array(
                '@name' => $data['name'],
                '@state' => $data['state_code'],
                '@country' => $data['country']))));

        return false;
    }

    /**
     * Updates a city
     * @param array $data
     * @return integer
     */
    protected function update(array $data)
    {
        return (int) $this->city->update($data['city_id'], $data);
    }

    /**
     * Adds a new city to the database
     * @param array $data
     * @return integer
     */
    protected function add(array $data)
    {
        $result = $this->city->add($data);
        return empty($result) ? 0 : 1;
    }

}
