<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\models;

use core\Model;
use core\helpers\Filter as FilterHelper;
use core\models\Language as LanguageModel;

/**
 * Manages basic behaviors and data related to HTML filters
 */
class Filter extends Model
{

    /**
     * Language model instance
     * @var \core\models\Language $language
     */
    protected $language;

    /**
     * Filter helper instance
     * @var \core\helpers\Filter $filter
     */
    protected $filter;

    /**
     * Constructor
     * @param LanguageModel $language
     * @param FilterHelper $filter
     */
    public function __construct(LanguageModel $language, FilterHelper $filter)
    {
        parent::__construct();

        $this->filter = $filter;
        $this->language = $language;
    }

    /**
     * Filters a text string
     * @param string $text
     * @param integer|null $filter_id
     * @return string
     */
    public function filter($text, $filter_id = null)
    {
        $config = array();

        if (isset($filter_id)) {
            $filter = $this->get($filter_id);
            $config = empty($filter['config']) ? array() : $filter['config'];
        }

        return $this->filter->filter($text, $config);
    }

    /**
     * Returns a filter
     * @param integer $filter_id
     * @return array
     */
    public function get($filter_id)
    {
        $filters = $this->getList();
        return empty($filters[$filter_id]) ? array() : $filters[$filter_id];
    }

    /**
     * Returns an array of defined filters
     * @param boolean $only_enabled
     * @return array
     */
    public function getList($only_enabled = false)
    {
        $filters = &gplcart_cache('filters');

        if (isset($filters)) {
            return $filters;
        }

        $default = $this->getDefault();

        $filters = array();
        foreach (array(1, 2, 3) as $level) {
            $filters[$level] = $this->config->get("filter_$level", $default[$level]);
        }

        $this->hook->fire('filters', $filters);
        return $filters;
    }

    /**
     * Returns an array of default filters
     */
    public function getDefault()
    {
        $filters = array();

        $filters[1] = array(
            'name' => $this->language->text('Minimal'),
            'description' => '',
            'status' => true,
            'config' => array(
                'AutoFormat.DisplayLinkURI' => true,
                'AutoFormat.RemoveEmpty' => true,
                'AutoFormat.RemoveSpansWithoutAttributes' => true,
                'HTML.Allowed' => 'strong,em,p,b,s,i,a[href|title],img[src|alt],'
                . 'blockquote,code,pre,del,ul,ol,li',
                'HTML.Nofollow' => true
            )
        );

        $filters[2] = array(
            'name' => $this->language->text('Advanced'),
            'description' => '',
            'status' => true,
            'config' => array(
                'AutoFormat.Linkify' => true,
                'AutoFormat.RemoveEmpty.RemoveNbsp' => true,
                'AutoFormat.RemoveEmpty' => true,
                'HTML.Nofollow' => true,
                'HTML.Allowed' => 'div,table,tr,td,tbody,tfoot,thead,th,strong,'
                . 'em,p[style],b,s,i,h2,h3,h4,h5,hr,br,span[style],a[href|title],'
                . 'img[width|height|alt|src],blockquote,code,pre,del,kbd,'
                . 'cite,dt,dl,dd,sup,sub,ul,ol,li',
                'CSS.AllowedProperties' => 'font,font-size,font-weight,font-style,'
                . 'font-family,text-decoration,padding-left,color,'
                . 'background-color,text-align',
                'HTML.FlashAllowFullScreen' => true,
                'HTML.SafeObject' => true,
                'HTML.SafeEmbed' => true,
                'HTML.Trusted' => true,
                'Output.FlashCompat' => true,
                'Filter.YouTube' => true
            )
        );

        $filters[3] = array(
            'name' => $this->language->text('Maximum'),
            'description' => '',
            'status' => true,
            'config' => array(
                'AutoFormat.Linkify' => true,
                'AutoFormat.RemoveEmpty.RemoveNbsp' => false,
                'AutoFormat.RemoveEmpty' => true,
                'HTML.Allowed' => 'div,table,tr,td,tbody,tfoot,thead,th,strong,'
                . 'em,p[style],b,s,i,h2,h3,h4,h5,hr,br,span[style],a[href|title],'
                . 'img[width|height|alt|src],blockquote,code,pre,del,kbd,'
                . 'cite,dt,dl,dd,sup,sub,ul,ol,li',
                'CSS.AllowedProperties' => 'font,font-size,font-weight,font-style,'
                . 'font-family,text-decoration,padding-left,color,'
                . 'background-color,text-align',
                'HTML.FlashAllowFullScreen' => true,
                'HTML.SafeObject' => true,
                'HTML.SafeEmbed' => true,
                'HTML.Trusted' => true,
                'Output.FlashCompat' => true,
                'Filter.YouTube' => true,
                'Attr.AllowedFrameTargets' => array('_blank', '_self', '_parent', '_top')
            )
        );

        return $filters;
    }

}
