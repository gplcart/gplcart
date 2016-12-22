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
     * @param integer|array $filter
     * @return string
     */
    public function filter($text, $filter)
    {
        if (is_numeric($filter)) {
            $filter = $this->get($filter);
        }

        $config = array();
        if (!empty($filter['status']) && !empty($filter['config'])) {
            $config = $filter['config'];
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
     * Returns a filter for the given user role ID
     * @param integer $role_id
     * @return array
     */
    public function getByRole($role_id)
    {
        $filters = $this->getList();

        foreach ($filters as $filter) {
            if ($filter['role_id'] == $role_id) {
                return $filter;
            }
        }

        return array();
    }

    /**
     * Updates a filter
     * @param integer $filter_id
     * @param array $data
     * @return bool
     */
    public function update($filter_id, array $data)
    {
        $this->hook->fire('update.filter.before', $data);

        if (empty($data)) {
            return false;
        }

        $overridable = array('status', 'role_id', 'config');

        foreach ($overridable as $option) {
            if (isset($data[$option])) {
                $this->config->set("filter_{$filter_id}_{$option}", $data[$option]);
            }
        }

        $this->hook->fire('update.filter.after', $data);
        return true;
    }

    /**
     * Returns an array of defined filters
     * @param boolean $enabled
     * @return array
     */
    public function getList($enabled = false)
    {
        $filters = &gplcart_cache("filters.$enabled");

        if (isset($filters)) {
            return $filters;
        }

        $filters = $this->getDefault();
        $overridable = array('status', 'role_id', 'config');

        foreach ($filters as $filter_id => &$filter) {

            // Make sure that filter ID is set
            $filter['filter_id'] = $filter_id;

            // Check overridable options and set them accordingly
            foreach ($overridable as $option) {
                $value = $this->config->get("filter_{$filter_id}_{$option}");
                if (isset($value)) {
                    $filter[$option] = $value;
                }
            }
        }

        $this->hook->fire('filters', $filters);

        if ($enabled) {
            $filters = array_filter($filters, function ($filter) {
                return !empty($filter['status']);
            });
        }

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
            'description' => $this->language->text('Minimal configuration for untrusted users'),
            'status' => true,
            'role_id' => 0, // Anonymous
            'config' => array(
                'AutoFormat.DisplayLinkURI' => true,
                'AutoFormat.RemoveEmpty' => true,
                'HTML.Allowed' => 'strong,em,p,b,s,i,a[href|title],img[src|alt],'
                . 'blockquote,code,pre,del,ul,ol,li'
            )
        );

        $filters[2] = array(
            'name' => $this->language->text('Advanced'),
            'description' => $this->language->text('Advanced configuration for trusted users, e.g content managers'),
            'status' => true,
            'role_id' => 3, // Content manager
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
                'Output.FlashCompat' => true
            )
        );

        $filters[3] = array(
            'name' => $this->language->text('Maximum'),
            'description' => $this->language->text('Maximal configuration for experienced and trusted users, e.g superadmin'),
            'status' => true,
            'role_id' => 1, // Director
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
                'Attr.AllowedFrameTargets' => array('_blank', '_self', '_parent', '_top')
            )
        );

        return $filters;
    }

}
