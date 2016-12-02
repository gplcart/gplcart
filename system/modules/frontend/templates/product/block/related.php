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
<?php if (!empty($products)) { ?>
<div class="panel panel-default related-products">
  <div class="panel-heading"><?php echo $this->text('Related'); ?></div>
  <div class="panel-body">
    <div class="row products" data-slider="true" data-slider-settings='{
        "item": 4,
        "pager": false,
        "autoWidth": false,
        "slideMargin": 0
    }'>
      <?php foreach ($products as $product) { ?>
      <?php echo $product; ?>
      <?php } ?>
    </div>
  </div>
</div>
<?php } ?>
