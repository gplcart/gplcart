<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<head>
  <?php foreach ($meta as $tag) { ?>
  <meta<?php echo $this->attributes($tag); ?>>
  <?php } ?>
  <title><?php echo $head_title; ?></title>
  <?php foreach ($css as $data) { ?>
  <link href="<?php echo $data['key']; ?>" rel="stylesheet">
  <?php } ?>
  <?php foreach ($js_top as $data) { ?>
    <?php if (!empty($data['text'])) { ?>
    <script><?php echo $data['asset']; ?></script>
    <?php } else { ?>
    <script src="<?php echo $data['key']; ?>"></script>
    <?php } ?>
  <?php } ?>
</head>