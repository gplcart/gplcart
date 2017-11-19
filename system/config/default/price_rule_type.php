<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
return array(
    'percent' => array(
        'title' => /* @text */'Percent',
        'description' => 'Add a percent value to the original amount. To subtract use negative values',
        'handlers' => array(
            'calculate' => array('gplcart\\core\\handlers\\price_rule\\Type', 'percent'),
            'validate' => array('gplcart\\core\\handlers\\validator\\components\\PriceRule', 'validateValuePercentPriceRule')
        ),
    ),
    'fixed' => array(
        'title' => /* @text */'Fixed',
        'description' => 'Add a fixed value to the original amount. To subtract use negative values',
        'handlers' => array(
            'calculate' => array('gplcart\\core\\handlers\\price_rule\\Type', 'fixed'),
            'validate' => array('gplcart\\core\\handlers\\validator\\components\\PriceRule', 'validateValueFixedPriceRule')
        ),
    ),
    'final' => array(
        'title' => /* @text */'Final',
        'description' => 'Replace an original amount with the price rule value',
        'handlers' => array(
            'calculate' => array('gplcart\\core\\handlers\\price_rule\\Type', 'finalAmount'),
            'validate' => array('gplcart\\core\\handlers\\validator\\components\\PriceRule', 'validateValueFinalPriceRule')
        ),
    )
);

