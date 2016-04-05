<?php

/**
 * @package GPL Cart core
 * @version $Id$
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace core\models;

use core\Hook;
use core\Config;
use core\Handler;
use core\classes\Cache;
use core\models\Language as ModelsLanguage;

/**
 * Manages basic behaviors and data related to the search system
 */
class Search
{

    /**
     * Hook class instance
     * @var \core\Hook $hook
     */
    protected $hook;

    /**
     * Language model instance
     * @var \core\models\Language $language
     */
    protected $language;

    /**
     * PDO instance
     * @var \core\classes\Database $db
     */
    protected $db;

    /**
     * Constructor
     * @param ModelsLanguage $language
     * @param Hook $hook
     * @param Config $config
     */
    public function __construct(ModelsLanguage $language, Hook $hook,
                                Config $config)
    {
        $this->hook = $hook;
        $this->db = $config->db();
        $this->language = $language;
    }

    /**
     * Adds in item to search index
     * @param string $text
     * @param string $id_key
     * @param integer $id_value
     * @param string $language
     * @return integer
     */
    public function addIndex($text, $id_key, $id_value, $language)
    {
        $result = $this->db->insert('search_index', array(
            'text' => $text,
            'id_key' => $id_key,
            'id_value' => $id_value,
            'language' => $language
        ));

        return $result;
    }

    /**
     * Deletes an item from the search index
     * @param string $id_key
     * @param integer $id_value
     * @param string $language
     * @return boolean
     */
    public function deleteIndex($id_key, $id_value, $language)
    {
        $result = $this->db->delete('search_index', array(
            'id_key' => $id_key,
            'id_value' => $id_value,
            'language' => $language));

        return (bool) $result;
    }

    /**
     * Sets an item to the search index
     * @param string $text
     * @param string $id_key
     * @param integer $id_value
     * @param string $language
     * @return integer
     */
    public function setIndex($text, $id_key, $id_value, $language)
    {
        $this->deleteIndex($id_key, $id_value, $language);
        return $this->addIndex($text, $id_key, $id_value, $language);
    }

    /**
     * Indexes an item
     * @param string $id_key
     * @param integer $id_value
     * @param array $options
     * @return mixed
     */
    public function index($id_key, $id_value, array $options = array())
    {
        return Handler::call($this->getHandlers(), $id_key, 'index', array($id_value, $options));
    }

    /**
     * Returns number of items available for indexing
     * @param string $id_key
     * @param integer $id_value
     * @param array $options
     * @return mixed
     */
    public function total($id_key, array $options = array())
    {
        return Handler::call($this->getHandlers(), $id_key, 'total', array($options));
    }

    /**
     * Returns an array of items found for the search query
     * @param string $id_key
     * @param string $query
     * @param array $options
     * @return array
     */
    public function search($id_key, $query, array $options = array())
    {
        if (!isset($options['language'])) {
            $options['language'] = 'und';
        }

        $filtered_query = $this->filterStopwords($query, $options['language']);

        if (!empty($filtered_query)) {
            return Handler::call($this->getHandlers(), $id_key, 'search', array($filtered_query, $options));
        }

        return array();
    }

    /**
     * Returns an array of handlers
     * @return array
     */
    public function getHandlers()
    {
        $handlers = &Cache::memory('search.handles');

        if (isset($handlers)) {
            return $handlers;
        }

        $handlers = array(
            'product_id' => array(
                'name' => $this->language->text('Products'),
                'handlers' => array(
                    'search' => array('core\\handlers\\search\\Product', 'search'),
                    'index' => array('core\\handlers\\search\\Product', 'index'),
                    'total' => array('core\\handlers\\search\\Product', 'total')
                )
            ),
            'order_id' => array(
                'name' => $this->language->text('Orders'),
                'handlers' => array(
                    'search' => array('core\\handlers\\search\\Order', 'search'),
                    'index' => array('core\\handlers\\search\\Order', 'index'),
                    'total' => array('core\\handlers\\search\\Order', 'total')
                )
            )
        );

        $this->hook->fire('search.handlers', $handlers);
        return $handlers;
    }

    /**
     * Filters out stopwords for a given language
     * @param string $string
     * @param string $language
     * @return string
     */
    public function filterStopwords($string, $language)
    {
        $string = trim($string);

        if ($string === '') {
            return '';
        }

        $stopwords = array();
        $path = GC_PRIVATE_DIR . "/stopwords/$language.txt";

        if (is_readable($path)) {
            $stopwords = array_map('trim', file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
        }

        return implode(' ', array_diff(explode(' ', $string), $stopwords));
    }
}
