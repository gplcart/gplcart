<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 * @var $this \gplcart\core\controllers\backend\Controller
 * To see available variables <?php print_r(get_defined_vars()); ?>
 */
?>
<?php if (!empty($products)) { ?>
<div class="panel panel-default bundled-items">
  <div class="panel-heading clearfix">
    <h4 class="panel-title"><?php echo $this->text('Bundled items'); ?></h4>
  </div>
  <div class="panel-body">
    <div class="bundled-items">
      <?php foreach ($products as $product) { ?>
      <?php echo $product['rendered']; ?>
      <?php } ?>
    </div>
  </div>
</div>
<?php } ?>