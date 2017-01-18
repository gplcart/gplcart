<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\traits;

use gplcart\core\Container;

/**
 * CRUD methods for entity URL aliases
 */
trait EntityAlias
{

    /**
     * Returns Alias model instance
     * @return \gplcart\core\models\Alias
     */
    protected function getAliasModel()
    {
        return Container::get('gplcart\\core\\models\\Alias');
    }

    /**
     * Deletes and/or adds an alias
     * @param array $data
     * @param string $entity
     * @param boolean $delete
     * @return null
     */
    protected function setAlias(array $data, $entity, $delete = true)
    {
        if (empty($data['form']) && empty($data['alias'])) {
            return null;
        }

        $alias = $this->getAliasModel();

        if ($delete) {
            $alias->delete("{$entity}_id", $data["{$entity}_id"]);
        }

        $alias->add("{$entity}_id", $data["{$entity}_id"], $data['alias']);
    }

    /**
     * Creates an alias
     * @param array $data
     * @param string $entity
     * @param bool $translit
     * @return string
     */
    public function createAlias(array $data, $entity, $translit = true)
    {
        $pattern = $this->config->get("{$entity}_alias_pattern", '%t.html');
        $placeholders = $this->config->get("{$entity}_alias_placeholder", array('%t' => 'title'));

        return $this->getAliasModel()->generate($pattern, $placeholders, $data, $translit);
    }

}
