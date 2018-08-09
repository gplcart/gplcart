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
<?php if(!empty($products)) { ?>
<div id="panel-recent-products" class="card borderless recent-products<?php echo empty($pager) ? '' : ' has-pager'; ?>">
  <div class="card-header clearfix">
    <h4 class="card-title float-left"><?php echo $this->text('Recently viewed'); ?></h4>
    <?php if(!empty($pager)) { ?>
    <div class="float-right">
      <?php echo $pager; ?>
    </div>
    <?php } ?>
  </div>
  <div class="card-body">
    <div class="row products row-no-padding">
      <?php foreach($products as $product) { ?>
      <?php echo $product['rendered']; ?>
      <?php } ?>
    </div>
  </div>
</div>
<?php } ?>
