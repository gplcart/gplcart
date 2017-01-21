<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\traits;

/**
 * Contains methods to validate dependencies
 */
trait Dependency
{

    /**
     * Validates dependency for an array of items
     * @param array $items
     * @return array
     */
    protected function validateDependenciesTrait(array &$items)
    {
        foreach ($items as &$item) {
            $this->validateDependencyTrait($items, $item);
        }
        
        return $items;
    }

    /**
     * Validates dependency for a single item
     * @param array $items
     * @param array $item
     * @return null
     */
    protected function validateDependencyTrait(array $items, array &$item)
    {
        if (empty($item['dependencies'])) {
            return null;
        }

        foreach ($item['dependencies'] as $id => $version) {

            if (!isset($items[$id])) {
                $item['errors'][] = array('Unknown dependency @id', array('@id' => $id));
                continue;
            }

            $components = $this->getVersionComponentsTrait($version);

            if (empty($components)) {
                $item['errors'][] = array('Unknown version of @id', array('@id' => $id));
                continue;
            }

            list($operator, $number) = $components;

            if (!version_compare($items[$id]['version'], $number, $operator)) {
                $item['errors'][] = array('Requires incompatible version of @id', array('@id' => $id));
            }
        }
    }

    /**
     * Extracts an array of components from strings like ">= 1.0.0"
     * @param string $data
     * @return array
     */
    protected function getVersionComponentsTrait($data)
    {
        $string = str_replace(' ', '', $data);

        $matches = array();
        preg_match_all('/(^(==|=|!=|<>|>|<|>=|<=)?(?=\d))(.*)/', $string, $matches);

        if (empty($matches[3][0])) {
            return array();
        }

        $operator = empty($matches[2][0]) ? '=' : $matches[2][0];
        return array($operator, $matches[3][0]);
    }

}