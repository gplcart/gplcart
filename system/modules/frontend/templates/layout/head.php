<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<head>
  <?php foreach ($_meta_tags as $tag) { ?>
  <meta<?php echo $this->attributes($tag); ?>>
  <?php } ?>
  <title>
    <?php if(!empty($_store_title)) { ?>
    <?php echo $this->e($_store_title); ?>
    <?php } ?>
    <?php if(!empty($_head_title) && $_head_title !== $_store_title) { ?>
    <?php if(!empty($_store_title)) { ?>|<?php } ?>
    <?php echo $this->e($_head_title); ?>
    <?php } ?>
  </title>
  <?php if(!empty($_store_favicon)) { ?>
  <link rel="icon" href="<?php echo $this->e($_store_favicon); ?>">
  <?php } ?>
  <?php foreach ($_css as $css) { ?>
  <link href="<?php echo $this->url($css['asset'], array('v' => $css['version']), false, true); ?>" rel="stylesheet">
  <?php } ?>
  <?php foreach ($_js_top as $js) { ?>
  <?php if (!empty($js['text'])) { ?>
  <?php if (!empty($js['asset'])) { ?>
  <?php if (!empty($js['condition'])) { ?>
    <!--[<?php echo $this->e($js['condition']); ?>]>
    <script><?php echo $js['asset']; ?></script>
    <![endif]-->
  <?php } else { ?>
  <script><?php echo $js['asset']; ?></script>
  <?php } ?>
  <?php } ?>
  <?php } else { ?>
  <?php if (!empty($js['condition'])) { ?>
    <!--[<?php echo $this->e($js['condition']); ?>]>
    <script src="<?php echo $this->url($js['asset'], array('v' => $js['version']), false, true); ?>"></script>
    <![endif]-->
  <?php } else { ?>
  <script src="<?php echo $this->url($js['asset'], array('v' => $js['version']), false, true); ?>"></script>
  <?php } ?>
  <?php } ?>
  <?php } ?>
</head>

