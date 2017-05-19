<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<?php if(!empty($products)) { ?>
<div id="panel-recent-products" class="panel panel-default panel-borderless recent-products">
  <div class="panel-heading"><h4 class="panel-title"><?php echo $this->text('Recently viewed'); ?></h4></div>
  <div class="panel-body">
    <div class="row products">
      <?php foreach($products as $product) { ?>
      <?php echo $product['rendered']; ?>
      <?php } ?>
    </div>
    <?php if(!empty($pager)) { ?>
    <div class="row">
      <div class="col-md-12">
        <?php echo $pager; ?>
      </div>
    </div>
    <?php } ?>
  </div>
</div>
<?php } ?>
