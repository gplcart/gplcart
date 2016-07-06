<?php

/**
 * @package GPL Cart core
 * @version $Id$
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\handlers\job\import;

use core\models\Import;
use core\models\Language;
use core\models\State;
use core\models\User;
use core\models\City as C;
use core\classes\Csv;

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
     * @param Import $import
     * @param Language $language
     * @param State $state
     * @param C $city
     * @param User $user
     * @param Csv $csv
     */
    public function __construct(Import $import, Language $language, State $state, C $city, User $user, Csv $csv)
    {
        $this->import = $import;
        $this->language = $language;
        $this->state = $state;
        $this->user = $user;
        $this->city = $city;
        $this->csv = $csv;
    }

    /**
     *
     * @param array $job
     * @param string $operation_id
     * @param integer $done
     * @param array $context
     * @param array $options
     * @return array
     */
    public function process($job, $operation_id, $done, $context, $options)
    {
        $import_operation = $options['operation'];
        $this->header = $import_operation['csv']['header'];

        $this->csv->setFile($options['filepath'], $options['filesize'])
                ->setHeader($this->header)
                ->setLimit($this->import->getLimit())
                ->setDelimiter($this->import->getCsvDelimiter());

        $offset = isset($context['offset']) ? $context['offset'] : 0;
        $line = isset($context['line']) ? $context['line'] : 2; // 2 - skip 0 and header row

        if ($offset) {
            $this->csv->setOffset($offset);
        } else {
            $this->csv->skipHeader();
        }

        $rows = $this->csv->parse();

        if (!$rows) {
            return array('done' => $job['total']);
        }

        $position = $this->csv->getOffset();
        $result = $this->import($rows, $line, $options);
        $line += count($rows);
        $bytes = $position ? $position : $job['total'];

        $errors = $this->import->getErrors($result['errors'], $import_operation);

        return array(
            'done' => $bytes,
            'increment' => false,
            'inserted' => $result['inserted'],
            'updated' => $result['updated'],
            'errors' => $errors['count'],
            'context' => array('offset' => $position, 'line' => $line));
    }

    /**
     *
     * @param type $rows
     * @param type $line
     * @param type $options
     * @return type
     */
    public function import($rows, $line, $options)
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
                    '@error' => $this->language->text('City name must not be longer than 255 characters')));
                continue;
            }

            if (isset($data['status'])) {
                $data['status'] = $this->import->toBool($data['status']);
            }

            if (isset($data['city_id'])) {
                if (is_numeric($data['city_id']) && $this->user->access('city_edit')) {
                    if ($this->city->update($data['city_id'], $data)) {
                        $updated++;
                    }
                }
                continue;
            }

            if (!$this->user->access('city_add')) {
                continue;
            }

            // Add a new record
            if ((count($data) + 1) != count($this->header)) {
                $errors[] = $this->language->text('Line @num: @error', array(
                    '@num' => $line,
                    '@error' => $this->language->text('Wrong format')));
                continue;
            }

            $state = $this->state->getByCode($data['state_code'], $data['country']);

            if (empty($state['state_id'])) {
                $errors[] = $this->language->text('Line @num: @error', array(
                    '@num' => $line,
                    '@error' => $this->language->text('State code @code does not exist for country @country', array(
                        '@code' => $data['state_code'],
                        '@country' => $data['country']))));
                continue;
            }

            $data['state_id'] = $state['state_id'];

            if ($this->cityExists($data['name'], $data['state_code'], $data['country'])) {
                $errors[] = $this->language->text('Line @num: @error', array(
                    '@num' => $line,
                    '@error' => $this->language->text('City @name already exists for state @state and country @country', array(
                        '@name' => $data['name'],
                        '@state' => $data['state_code'],
                        '@country' => $data['country']))));
                continue;
            }

            if ($this->city->add($data)) {
                $inserted++;
            }
        }

        return array('inserted' => $inserted, 'updated' => $updated, 'errors' => $errors);
    }

    /**
     * Whether the city exists
     * @param type $name
     * @param type $state_code
     * @param type $country
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
}
