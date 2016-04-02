<?php

/**
 * @package GPL Cart core
 * @version $Id$
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace core\handlers\job\import;

use core\models\Import;
use core\models\Language;
use core\models\User;
use core\models\State as S;
use core\models\Country;
use core\classes\Csv;

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
     * @param Import $import
     * @param Language $language
     * @param User $user
     * @param S $state
     * @param Country $country
     * @param Csv $csv
     */
    public function __construct(Import $import, Language $language, User $user, S $state, Country $country, Csv $csv)
    {
        $this->import = $import;
        $this->language = $language;
        $this->state = $state;
        $this->country = $country;
        $this->user = $user;
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

            if ($this->state->add($data)) {
                $inserted++;
            }
        }

        return array('inserted' => $inserted, 'updated' => $updated, 'errors' => $errors);
    }

    /**
     *
     * @param type $name
     * @param type $code
     * @param type $country
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
