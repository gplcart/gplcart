<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
return array(
    'thumbnail' => array(
        'name' => 'Thumbnail',
        'description' => 'Trim and resize to exact width and height. Parameters: two integers, width and height, separated by comma',
        'handlers' => array(
            'process' => array('gplcart\\core\\handlers\\image\\Action', 'thumbnail'),
            'validate' => array('gplcart\\core\\handlers\\validator\\components\\Image', 'validateThumbnailAction')
        )
    ),
    'crop' => array(
        'name' => 'Crop',
        'description' => 'Crop a portion of image from x1, y1 to x2, y2. Parameters: Four integers, separated by comma',
        'handlers' => array(
            'process' => array('gplcart\\core\\handlers\\image\\Action', 'crop'),
            'validate' => array('gplcart\\core\\handlers\\validator\\components\\Image', 'validateCropAction')
        )
    ),
    'resize' => array(
        'name' => 'Resize',
        'description' => 'Resize to fixed width and height. Parameters: Two integers, width and height, separated by comma',
        'handlers' => array(
            'process' => array('gplcart\\core\\handlers\\image\\Action', 'resize'),
            'validate' => array('gplcart\\core\\handlers\\validator\\components\\Image', 'validateResizeAction')
        )
    ),
    'fit_width' => array(
        'name' => 'Fit to width',
        'description' => 'Shrink to the specified width while maintaining proportion. Parameters: Integer',
        'handlers' => array(
            'process' => array('gplcart\\core\\handlers\\image\\Action', 'fitWidth'),
            'validate' => array('gplcart\\core\\handlers\\validator\\components\\Image', 'validateFitWidthAction')
        )
    ),
    'fit_height' => array(
        'name' => 'Fit to height',
        'description' => 'Shrink to the specified height while maintaining proportion. Parameters: Integer',
        'handlers' => array(
            'process' => array('gplcart\\core\\handlers\\image\\Action', 'fitHeight'),
            'validate' => array('gplcart\\core\\handlers\\validator\\components\\Image', 'validateFitHeightAction')
        )
    ),
);
