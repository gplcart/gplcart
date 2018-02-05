<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
return array(
    'thumbnail' => array(
        'name' => 'Thumbnail', // @text
        'description' => 'Trim and resize to exact width and height. Parameters: two integers, width and height, separated by comma', // @text
        'handlers' => array(
            'process' => array('gplcart\\core\\handlers\\image\\Action', 'thumbnail'),
            'validate' => array('gplcart\\core\\handlers\\validator\\components\\ImageStyle', 'validateActionThumbnailImageStyle')
        )
    ),
    'crop' => array(
        'name' => 'Crop', // @text
        'description' => 'Crop a portion of image from x1, y1 to x2, y2. Parameters: Four integers, separated by comma', // @text
        'handlers' => array(
            'process' => array('gplcart\\core\\handlers\\image\\Action', 'crop'),
            'validate' => array('gplcart\\core\\handlers\\validator\\components\\ImageStyle', 'validateActionCropImageStyle')
        )
    ),
    'resize' => array(
        'name' => 'Resize', // @text
        'description' => 'Resize to fixed width and height. Parameters: Two integers, width and height, separated by comma', // @text
        'handlers' => array(
            'process' => array('gplcart\\core\\handlers\\image\\Action', 'resize'),
            'validate' => array('gplcart\\core\\handlers\\validator\\components\\ImageStyle', 'validateActionResizeImageStyle')
        )
    ),
    'fit_width' => array(
        'name' => 'Fit to width', // @text
        'description' => 'Shrink to the specified width while maintaining proportion. Parameters: Integer', // @text
        'handlers' => array(
            'process' => array('gplcart\\core\\handlers\\image\\Action', 'fitWidth'),
            'validate' => array('gplcart\\core\\handlers\\validator\\components\\ImageStyle', 'validateActionResizeImageStyle')
        )
    ),
    'fit_height' => array(
        'name' => 'Fit to height', // @text
        'description' => 'Shrink to the specified height while maintaining proportion. Parameters: Integer', // @text
        'handlers' => array(
            'process' => array('gplcart\\core\\handlers\\image\\Action', 'fitHeight'),
            'validate' => array('gplcart\\core\\handlers\\validator\\components\\ImageStyle', 'validateActionResizeImageStyle')
        )
    ),
);
