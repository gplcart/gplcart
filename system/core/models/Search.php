<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\models;

use core\Model;
use core\Handler;
use core\classes\Cache;
use core\models\Language as ModelsLanguage;

/**
 * Manages basic behaviors and data related to the search system
 */
class Search extends Model
{

    /**
     * Language model instance
     * @var \core\models\Language $language
     */
    protected $language;

    /**
     * Constructor
     * @param ModelsLanguage $language
     */
    public function __construct(ModelsLanguage $language)
    {
        parent::__construct();

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
        $values = array(
            'text' => $text,
            'id_key' => $id_key,
            'id_value' => $id_value,
            'language' => $language
        );

        return $this->db->insert('search_index', $values);
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
        $values = array(
            'id_key' => $id_key,
            'id_value' => $id_value,
            'language' => $language
        );

        return (bool) $this->db->delete('search_index', $values);
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
     * @return mixed
     */
    public function search($id_key, $query, array $options = array())
    {
        if (!isset($options['language'])) {
            $options['language'] = 'und';
        }

        $filtered_query = $this->filterStopwords($query, $options['language']);

        if (!empty($filtered_query)) {
            $handlers = $this->getHandlers();
            return Handler::call($handlers, $id_key, 'search', array($filtered_query, $options));
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

        $handlers = array();

        $handlers['product_id'] = array(
            'name' => $this->language->text('Products'),
            'handlers' => array(
                'search' => array('core\\handlers\\search\\Product', 'search'),
                'index' => array('core\\handlers\\search\\Product', 'index'),
                'total' => array('core\\handlers\\search\\Product', 'total')
        ));

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
            $array = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $stopwords = array_map('trim', $array);
        }

        return implode(' ', array_diff(explode(' ', $string), $stopwords));
    }
}
