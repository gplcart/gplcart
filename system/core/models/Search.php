<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Model,
    gplcart\core\Handler,
    gplcart\core\Config,
    gplcart\core\Hook;
use gplcart\core\models\Language as LanguageModel;

/**
 * Manages basic behaviors and data related to the search system
 */
class Search extends Model
{

    /**
     * Language model instance
     * @var \gplcart\core\models\Language $language
     */
    protected $language;

    /**
     * @param Config $config
     * @param Hook $hook
     * @param LanguageModel $language
     */
    public function __construct(Config $config, Hook $hook, LanguageModel $language)
    {
        parent::__construct($config, $hook);

        $this->language = $language;
    }

    /**
     * Adds an item to the search index
     * @param string $text
     * @param string $id_key
     * @param integer $id_value
     * @param string $language
     * @return boolean
     */
    public function addIndex($text, $id_key, $id_value, $language)
    {
        $values = array(
            'text' => $text,
            'id_key' => $id_key,
            'id_value' => $id_value,
            'language' => $language
        );

        return (bool) $this->db->insert('search_index', $values);
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
     * @return boolean
     */
    public function setIndex($text, $id_key, $id_value, $language)
    {
        $this->deleteIndex($id_key, $id_value, $language);

        if (empty($text)) {
            return false;
        }

        return $this->addIndex($text, $id_key, $id_value, $language);
    }

    /**
     * Indexes an item
     * @param string $handler_id
     * @param array|string $data
     * @return mixed
     */
    public function index($handler_id, $data)
    {
        $result = null;
        $this->hook->attach('search.index', $handler_id, $data, $result, $this);

        if (isset($result)) {
            return $result;
        }

        return $this->callHandler($handler_id, 'index', array($data, $this));
    }

    /**
     * Returns an array of items found for the search query
     * @param string $handler_id
     * @param string $query
     * @param array $options
     * @return mixed
     */
    public function search($handler_id, $query, array $options = array())
    {
        if (!isset($options['language'])) {
            $options['language'] = 'und';
        }

        $result = null;
        $this->hook->attach('search', $handler_id, $query, $options, $result, $this);

        if (isset($result)) {
            return $result;
        }

        $filtered = $this->filterStopwords($query, $options['language']);

        if (!empty($filtered)) {
            return $this->callHandler($handler_id, 'search', array($filtered, $options, $this));
        }

        return array();
    }

    /**
     * Calls a search handler
     * @param string $handler_id
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public function callHandler($handler_id, $method, array $args)
    {
        try {
            $handlers = $this->getHandlers();
            return Handler::call($handlers, $handler_id, $method, $args);
        } catch (\Exception $ex) {
            return null;
        }
    }

    /**
     * Returns a text string to be saved in the index table
     * @param array $data
     * @param string $language
     * @return string
     */
    public function getSnippet(array $data, $language)
    {
        $parts = array();
        if (isset($data['title'])) {
            // Repeat title twice to make it more important
            $parts = array($data['title'], $data['title']);
        }

        if (isset($data['description'])) {
            $parts[] = strip_tags($data['description']);
        }

        $snippet = $this->filterStopwords(implode(' ', $parts), $language);
        $this->hook->attach('search.index.snippet', $data, $language, $snippet);
        return $snippet;
    }

    /**
     * Returns an array of handlers
     * @return array
     */
    public function getHandlers()
    {
        $handlers = &gplcart_static('search.handlers');

        if (isset($handlers)) {
            return $handlers;
        }

        $handlers = $this->getDefaultHandlers();
        $this->hook->attach('search.handlers', $handlers, $this);
        return $handlers;
    }

    /**
     * Returns an array of default handlers
     * @return array
     */
    protected function getDefaultHandlers()
    {
        $handlers = array();

        $handlers['product'] = array(
            'name' => $this->language->text('Products'),
            'handlers' => array(
                'search' => array('gplcart\\core\\handlers\\search\\Product', 'search'),
                'index' => array('gplcart\\core\\handlers\\search\\Product', 'index')
        ));

        return $handlers;
    }

    /**
     * Filters out stop-words for a given language
     * @param string $string
     * @param string $language
     * @return string
     */
    public function filterStopwords($string, $language)
    {
        $prepared = trim(strip_tags($string));

        if ($prepared === '') {
            return '';
        }

        $stopwords = array();
        $path = GC_DIR_PRIVATE . "/stopwords/$language.txt";

        if (is_readable($path)) {
            $stopwords = array_map('trim', file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
        }

        return implode(' ', array_diff(explode(' ', $prepared), $stopwords));
    }

}
