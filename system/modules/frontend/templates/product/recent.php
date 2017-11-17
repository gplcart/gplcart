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
<div id="panel-recent-products" class="panel panel-default panel-borderless recent-products<?php echo empty($pager) ? '' : ' has-pager'; ?>">
  <div class="panel-heading clearfix">
    <h4 class="panel-title pull-left"><?php echo $this->text('Recently viewed'); ?></h4>
    <?php if(!empty($pager)) { ?>
    <div class="pull-right">
      <?php echo $pager; ?>
    </div>
    <?php } ?>
  </div>
  <div class="panel-body">
    <div class="row products row-no-padding">
      <?php foreach($products as $product) { ?>
      <?php echo $product['rendered']; ?>
      <?php } ?>
    </div>
  </div>
</div>
<?php } ?>
