<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<?php if (!empty($items)) { ?>
<div class="panel panel-borderless panel-default collection collection-product">
  <div class="panel-heading"><?php echo $this->e($title); ?></div>
  <div class="panel-body">
    <div class="row">
      <?php foreach ($items as $item) { ?>
      <?php echo $item['rendered']; ?>
      <?php } ?>
    </div>
  </div>
</div>
<?php } ?>