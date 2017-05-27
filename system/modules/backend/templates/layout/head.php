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
  <title><?php echo $_head_title; ?></title>
  <?php foreach ($_styles as $data) { ?>
  <link href="<?php echo $this->e($data['key']); ?>" rel="stylesheet">
  <?php } ?>
  <?php foreach ($_scripts_top as $data) { ?>
    <?php if (!empty($data['text'])) { ?>
    <script><?php echo $data['asset']; ?></script>
    <?php } else { ?>
    <script src="<?php echo $this->e($data['key']); ?>"></script>
    <?php } ?>
  <?php } ?>
</head>