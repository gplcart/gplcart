<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\handlers\ga;

class Query
{

    public function __construct()
    {
        //
    }

    /**
     * Returns an array of query parameters for popular pages statistic
     * @param string $from
     * @param string $to
     * @param null|insteger $limit
     * @return array
     */
    public function topPages($from, $to, $limit)
    {
        $fields = array(
            'ga:pageviews',
            'ga:uniquePageviews',
            'ga:timeOnPage',
            'ga:bounces',
            'ga:entrances',
            'ga:exits'
        );

        $query = array($from, $to, implode(',', $fields), array(
                'dimensions' => 'ga:hostname, ga:pagePath', 'sort' => '-ga:pageviews',
        ));

        if (!empty($limit)) {
            $query[3]['max-results'] = (int) $limit;
        }

        return $query;
    }

    /**
     * Returns an array of query parameters for source statistic
     * @param string $from
     * @param string $to
     * @param null|integer $limit
     * @return array
     */
    public function sources($from, $to, $limit)
    {
        $fields = array(
            'ga:sessions',
            'ga:pageviews',
            'ga:sessionDuration',
            'ga:exits'
        );

        $query = array($from, $to, implode(',', $fields), array(
                'dimensions' => 'ga:source,ga:medium', 'sort' => '-ga:sessions',
        ));

        if (!empty($limit)) {
            $query[3]['max-results'] = (int) $limit;
        }

        return $query;
    }

    /**
     * Returns an array of query parameters for keyword statistic
     * @param string $from
     * @param string $to
     * @param null|integer $limit
     * @return array
     */
    public function keywords($from, $to, $limit)
    {
        $query = array($from, $to, 'ga:sessions', array(
                'dimensions' => 'ga:keyword', 'sort' => '-ga:sessions',
        ));

        if (!empty($limit)) {
            $query[3]['max-results'] = (int) $limit;
        }

        return $query;
    }

    /**
     * Returns an array of query parameters for traffic statistic
     * @param string $from
     * @param string $to
     * @param null|integer $limit
     * @return array
     */
    public function traffic($from, $to, $limit)
    {
        $fields = array('ga:sessions', 'ga:pageviews');
        $query = array($from, $to, implode(',', $fields), array('dimensions' => 'ga:date'));

        if (!empty($limit)) {
            $query[3]['max-results'] = (int) $limit;
        }

        return $query;
    }

    /**
     * Returns an array of query parameters for software statistic
     * @param string $from
     * @param string $to
     * @param integer|null $limit
     * @return array
     */
    public function software($from, $to, $limit)
    {
        $fields = array(
            'ga:operatingSystem',
            'ga:operatingSystemVersion',
            'ga:browser',
            'ga:browserVersion'
        );

        $query = array($from, $to, 'ga:sessions', array(
                'dimensions' => implode(',', $fields),
                'sort' => '-ga:sessions',
        ));

        if (!empty($limit)) {
            $query[3]['max-results'] = (int) $limit;
        }

        return $query;
    }

}
