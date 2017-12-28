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
     * @param bool $enabled
     * @return array
     */
    public function validateDependencies(array &$items, $enabled = false)
    {
        foreach ($items as &$item) {
            $this->validateDependency($item, $items, $enabled);
        }

        return $items;
    }

    /**
     * Validates dependency for a single item
     * @param array $item
     * @param array $items
     * @param bool $enabled
     */
    protected function validateDependency(&$item, $items, $enabled = false)
    {
        if (!empty($item['dependencies'])) {
            foreach ($item['dependencies'] as $id => $version) {

                if (!isset($items[$id])) {
                    $item['errors'][] = array('Unknown dependency @id', array('@id' => $id));
                    continue;
                }

                if ($enabled && empty($items[$id]['status'])) {
                    $item['errors'][] = array('Requires @id to be enabled', array('@id' => $items[$id]['name']));
                    continue;
                }

                $components = $this->getVersionComponents($version);

                if (empty($components)) {
                    $item['errors'][] = array('Unknown version of @name', array('@name' => $id));
                    continue;
                }

                list($operator, $number) = $components;

                if ($operator === '=' && strpos($number, 'x') !== false) {
                    $allowed = version_compare($items[$id]['version'], $number);
                } else {
                    $allowed = version_compare($items[$id]['version'], $number, $operator);
                }

                if (!$allowed) {
                    $item['errors'][] = array('Requires incompatible version of @name', array('@name' => $id));
                }
            }
        }
    }

    /**
     * Extracts an array of components from strings like ">= 1.0.0"
     * @param string $data
     * @return array
     */
    public function getVersionComponents($data)
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
