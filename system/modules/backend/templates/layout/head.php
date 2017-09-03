<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 * @var $this \gplcart\core\controllers\backend\Controller
 * To see available variables <?php print_r(get_defined_vars()); ?>
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
  <?php if(!empty($_css)) { ?>
  <?php foreach ($_css as $css) { ?>
  <link href="<?php echo $this->url($css['asset'], array('v' => $css['version']), false, true); ?>" rel="stylesheet">
  <?php } ?>
  <?php } ?>
  <?php if(!empty($_js_top)) { ?>
  <?php foreach ($_js_top as $js) { ?>
    <?php if (empty($js['text'])) { ?>
    <script src="<?php echo $this->url($js['asset'], array('v' => $js['version']), false, true); ?>"></script>
    <?php } else { ?>
    <script><?php echo $js['asset']; ?></script>
    <?php } ?>
  <?php } ?>
  <?php } ?>
</head>