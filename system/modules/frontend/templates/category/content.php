<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
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
<div class="row section empty">
  <div class="col-md-12">
    <?php echo $this->text('This category has no content'); ?>
  </div>
</div>
<?php } ?>
<?php } else { ?>
<?php if(!empty($navbar)) { ?>
<?php echo $navbar; ?>
<?php } ?>
<?php echo $products; ?>
<?php if (!empty($pager)) { ?>
<div class="row">
  <div class="col-md-12 text-right">
    <?php echo $pager; ?>
  </div>
</div>
<?php } ?>
<?php } ?>
<?php if (!empty($category['description_2'])) { ?>
<div class="row section description-2">
  <div class="col-md-12"><?php echo $this->filter($category['description_2']); ?></div>
</div>
<?php } ?>