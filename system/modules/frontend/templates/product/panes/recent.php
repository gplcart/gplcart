<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<?php if(!empty($products)) { ?>
<div class="panel panel-default panel-borderless recent-products">
  <div class="panel-heading"><?php echo $this->text('Recently viewed'); ?></div>
  <div class="panel-body">
    <div class="row products">
      <?php foreach($products as $product) { ?>
      <?php echo $product['rendered']; ?>
      <?php } ?>
    </div>
  </div>
</div>
<?php } ?>
