<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 * @var $this \gplcart\core\controllers\frontend\Controller
 * To see available variables <?php print_r(get_defined_vars()); ?>
 */
?>
<?php if (!empty($images) || !empty($category['description_1'])) { ?>
<div class="row section">
  <?php $class_description_1 = 'col-md-12'; ?>
  <?php if(!empty($images)) { ?>
  <?php $class_description_1 = 'col-md-10'; ?>
  <div class="col-md-2 images"><?php echo $images; ?></div>
  <?php } ?>
<?php if (!empty($category['description_1'])) { ?>
  <div class="description-1 <?php echo $class_description_1; ?>"><?php echo $this->filter($category['description_1']); ?></div>
<?php } ?>
</div>
<?php } ?>
<?php if (!empty($children)) { ?>
<?php echo $children; ?>
<?php } ?>
<?php if (empty($products)) { ?>
<?php if (empty($children)) { ?>
<div class="section empty">
  <?php echo $this->text('This category has no content'); ?>
</div>
<?php } ?>
<?php } else { ?>
<?php if(!empty($navbar)) { ?>
<?php echo $navbar; ?>
<?php } ?>
<?php echo $products; ?>
<?php if (!empty($_pager)) { ?>
<?php echo $_pager; ?>
<?php } ?>
<?php } ?>
<?php if (!empty($category['description_2'])) { ?>
<div class="row section description-2">
  <div class="col-md-12"><?php echo $this->filter($category['description_2']); ?></div>
</div>
<?php } ?>