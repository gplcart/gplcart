<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<head>
  <?php if(!empty($_meta_tags)) { ?>
  <?php foreach ($_meta_tags as $tag) { ?>
  <meta<?php echo $this->attributes($tag); ?>>
  <?php } ?>
  <?php } ?>
  <?php if(!empty($_head_title)) { ?>
  <title><?php echo $_head_title; ?></title>
  <?php } ?>
  <?php if(!empty($_styles)) { ?>
  <?php foreach ($_styles as $data) { ?>
  <link href="<?php echo $this->e($data['key']); ?>" rel="stylesheet">
  <?php } ?>
  <?php } ?>
  <?php if(!empty($_scripts_top)) { ?>
  <?php foreach ($_scripts_top as $data) { ?>
    <?php if (!empty($data['text'])) { ?>
    <script><?php echo $data['asset']; ?></script>
    <?php } else { ?>
    <script src="<?php echo $this->e($data['key']); ?>"></script>
    <?php } ?>
  <?php } ?>
  <?php } ?>
</head>