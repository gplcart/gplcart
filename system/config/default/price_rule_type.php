<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
return array(
    'percent' => array(
        'title' => 'Percent', // @text
        'description' => 'Add a percent value to the original amount. To subtract use negative values', // @text
        'handlers' => array(
            'calculate' => array('gplcart\\core\\handlers\\price_rule\\Type', 'percent'),
            'validate' => array('gplcart\\core\\handlers\\validator\\components\\PriceRule', 'validateValuePercentPriceRule')
        ),
    ),
    'fixed' => array(
        'title' => 'Fixed', // @text
        'description' => 'Add a fixed value to the original amount. To subtract use negative values', // @text
        'handlers' => array(
            'calculate' => array('gplcart\\core\\handlers\\price_rule\\Type', 'fixed'),
            'validate' => array('gplcart\\core\\handlers\\validator\\components\\PriceRule', 'validateValueFixedPriceRule')
        ),
    ),
    'final' => array(
        'title' => 'Final', // @text
        'description' => 'Replace an original amount with the price rule value', // @text
        'handlers' => array(
            'calculate' => array('gplcart\\core\\handlers\\price_rule\\Type', 'finalAmount'),
            'validate' => array('gplcart\\core\\handlers\\validator\\components\\PriceRule', 'validateValueFinalPriceRule')
        ),
    )
);

