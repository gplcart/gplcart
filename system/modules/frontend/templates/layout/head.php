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
    <?php echo $this->e($_store_title); ?>
    <?php if(!empty($_head_title)) { ?>
    | <?php echo $this->e($_head_title); ?>
    <?php } ?>
  </title>
  <?php if(!empty($_store_favicon)) { ?>
  <link rel="icon" href="<?php echo $this->e($_store_favicon); ?>">
  <?php } ?>
  <?php foreach ($_styles as $data) { ?>
  <link href="<?php echo $this->e($data['key']); ?>" rel="stylesheet">
  <?php } ?>
  <?php foreach ($_scripts_top as $data) { ?>
  <?php if (!empty($data['text'])) { ?>
  <?php if (!empty($data['asset'])) { ?>
  <?php if (!empty($data['condition'])) { ?>
    <!--[<?php echo $this->e($data['condition']); ?>]>
    <script><?php echo $data['asset']; ?></script>
    <![endif]-->
  <?php } else { ?>
  <script><?php echo $data['asset']; ?></script>
  <?php } ?>
  <?php } ?>
  <?php } else { ?>
  <?php if (!empty($data['condition'])) { ?>
    <!--[<?php echo $this->e($data['condition']); ?>]>
    <script src="<?php echo $this->e($data['key']); ?>"></script>
    <![endif]-->
  <?php } else { ?>
  <script src="<?php echo $this->e($data['key']); ?>"></script>
  <?php } ?>
  <?php } ?>
  <?php } ?>
</head>

