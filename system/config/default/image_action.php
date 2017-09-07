<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
return array(
    'thumbnail' => array(
        'name' => /* @text */'Thumbnail',
        'description' => /* @text */'Trim and resize to exact width and height. Parameters: two integers, width and height, separated by comma',
        'handlers' => array(
            'process' => array('gplcart\\core\\handlers\\image\\Action', 'thumbnail'),
            'validate' => array('gplcart\\core\\handlers\\validator\\components\\Image', 'validateActionThumbnailImageStyle')
        )
    ),
    'crop' => array(
        'name' => /* @text */'Crop',
        'description' => /* @text */'Crop a portion of image from x1, y1 to x2, y2. Parameters: Four integers, separated by comma',
        'handlers' => array(
            'process' => array('gplcart\\core\\handlers\\image\\Action', 'crop'),
            'validate' => array('gplcart\\core\\handlers\\validator\\components\\Image', 'validateActionCropImageStyle')
        )
    ),
    'resize' => array(
        'name' => /* @text */'Resize',
        'description' => /* @text */'Resize to fixed width and height. Parameters: Two integers, width and height, separated by comma',
        'handlers' => array(
            'process' => array('gplcart\\core\\handlers\\image\\Action', 'resize'),
            'validate' => array('gplcart\\core\\handlers\\validator\\components\\Image', 'validateActionResizeImageStyle')
        )
    ),
    'fit_width' => array(
        'name' => /* @text */'Fit to width',
        'description' => /* @text */'Shrink to the specified width while maintaining proportion. Parameters: Integer',
        'handlers' => array(
            'process' => array('gplcart\\core\\handlers\\image\\Action', 'fitWidth'),
            'validate' => array('gplcart\\core\\handlers\\validator\\components\\Image', 'validateActionResizeImageStyle')
        )
    ),
    'fit_height' => array(
        'name' => /* @text */'Fit to height',
        'description' => /* @text */'Shrink to the specified height while maintaining proportion. Parameters: Integer',
        'handlers' => array(
            'process' => array('gplcart\\core\\handlers\\image\\Action', 'fitHeight'),
            'validate' => array('gplcart\\core\\handlers\\validator\\components\\Image', 'validateActionResizeImageStyle')
        )
    ),
);
