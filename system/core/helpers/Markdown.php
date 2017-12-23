<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\helpers;

/**
 * Markdown parser
 */
class Markdown
{

    /**
     * An array of replacement rules
     * @var array
     */
    protected $rules = array();

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->rules = $this->getDefaultRules();
    }

    /**
     * Returns an array of default replacement rules
     * @return array
     */
    protected function getDefaultRules()
    {
        return array(
            '/(#+)(.*)/' => 'static::h', // headers
            '/(\*\*|__)(.*?)\1/' => '<strong>\2</strong>', // bold
            '/(\*|_)(.*?)\1/' => '<em>\2</em>', // emphasis
            '/\~\~(.*?)\~\~/' => '<del>\1</del>', // del
            '/\:\"(.*?)\"\:/' => '<q>\1</q>', // quote
            '/`(.*?)`/' => '<code>\1</code>', // inline code
            '/\n\*(.*)/' => 'static::ul', // ul lists
            '/\n[0-9]+\.(.*)/' => 'static::ol', // ol lists
            '/\n(&gt;|\>)(.*)/' => 'static::blockquote', // blockquotes
            '/\n-{5,}/' => "\n<hr />", // horizontal rule
            '/\n([^\n]+)\n/' => 'static::p', // add paragraphs
            '/<\/ul>\s?<ul>/' => '', // fix extra ul
            '/<\/ol>\s?<ol>/' => '', // fix extra ol
            '/<\/blockquote><blockquote>/' => "\n", // fix extra blockquote
            '/!\[(.*?)\]\((.*?)\)/' => '<img src=\'\2\'>', // images
            '/\[([^\[]+)\]\(([^\)]+)\)/' => '<a href=\'\2\'>\1</a>', // links
        );
    }

    /**
     * Adds a rule
     * @param string|array $rule A single RegExp pattern or array of rules
     * @param string $replacement A replacement HTML string or callable method
     * @return $this
     */
    public function setRule($rule, $replacement)
    {
        if (is_array($rule)) {
            $this->rules = $rule;
            return $this;
        }

        $this->rules[$rule] = $replacement;
        return $this;
    }

    /**
     * Remove a rule
     * @param string $name
     * @return $this
     */
    public function unsetRule($name)
    {
        unset($this->rules[$name]);
        return $this;
    }

    /**
     * Returns an array of replacement rules
     * @return array
     */
    public function getRules()
    {
        return $this->rules;
    }

    /**
     * Renders Markdown text into HTML
     * @param string $text
     * @return string
     */
    public function render($text)
    {
        $text = trim(str_replace(array("\r\n", "\r"), "\n", $text), "\n");

        foreach ($this->rules as $regex => $replacement) {
            if (is_callable($replacement)) {
                $text = preg_replace_callback($regex, $replacement, $text);
            } else {
                $text = preg_replace($regex, $replacement, $text);
            }
        }

        return trim($text);
    }

    /**
     * Renders "P" tag
     * @param array $matches
     * @return string
     */
    protected static function p(array $matches)
    {
        $line = $matches[1];
        $trimmed = trim($line);
        if (preg_match('/^<\/?(ul|ol|li|h|p|bl)/', $trimmed)) {
            return "\n$line\n";
        }

        return sprintf("\n<p>%s</p>\n", $trimmed);
    }

    /**
     * Renders "UL" tag
     * @param array $matches
     * @return string
     */
    protected static function ul(array $matches)
    {
        return sprintf("\n<ul>\n\t<li>%s</li>\n</ul>", trim($matches[1]));
    }

    /**
     * Renders "OL" tag
     * @param array $matches
     * @return string
     */
    protected static function ol(array $matches)
    {
        return sprintf("\n<ol>\n\t<li>%s</li>\n</ol>", trim($matches[1]));
    }

    /**
     * Renders "BLOCKQUOTE" tag
     * @param array $matches
     * @return string
     */
    protected static function blockquote(array $matches)
    {
        return sprintf("\n<blockquote>%s</blockquote>", trim($matches[2]));
    }

    /**
     * Renders "H" tag
     * @param array $matches
     * @return string
     */
    protected static function h(array $matches)
    {
        $level = strlen($matches[1]);
        return sprintf('<h%d>%s</h%d>', $level, trim($matches[2]), $level);
    }

}
