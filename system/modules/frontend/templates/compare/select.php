<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<?php if (!empty($products)) { ?>
<?php foreach ($products as $items) { ?>
<div class="row products">
  <?php foreach ($items as $product) { ?>
  <?php echo $product['rendered']; ?>
  <?php } ?>
</div>
<div class="row">
  <div class="col-md-12">
    <?php if (count($items) > 1) { ?>
    <a href="<?php echo $this->url('compare/' . implode(',', array_keys($items))); ?>" class="btn btn-default">
      <?php echo $this->text('Compare'); ?>
    </a>
    <?php } else { ?>
    <a class="btn btn-default disabled">
      <?php echo $this->text('Add more to compare'); ?>
    </a>
    <?php } ?>
    <hr>
  </div>
</div>
<?php } ?>
<?php } else { ?>
<?php echo $this->text('Nothing to compare'); ?>
<?php } ?>