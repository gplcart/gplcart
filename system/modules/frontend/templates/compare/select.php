<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 * 
 * To see available variables: <?php print_r(get_defined_vars()); ?>
 * To see the current controller object: <?php print_r($this); ?>
 * To call a controller method: <?php $this->exampleMethod(); ?>
 */
?>
<?php if ($products) { ?>
<?php foreach ($products as $product_class_id => $items) { ?>
<div class="row products">
<?php foreach ($items as $product_id => $product) { ?>
<?php echo $product['rendered']; ?>
<?php } ?>
</div>
<div class="row">
  <div class="col-md-12">
    <?php if (count($items) > 1) { ?>
    <a href="<?php echo $this->url('compare/' . implode(',', array_keys($items))); ?>" class="btn btn-default">
      <i class="fa fa-balance-scale"></i> <?php echo $this->text('Compare'); ?>
    </a>
    <?php } ?>
  </div>
</div>
<?php } ?>
<?php } else { ?>
<?php echo $this->text('Nothing to compare'); ?>
<?php } ?>