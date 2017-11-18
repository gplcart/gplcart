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
<?php if (!empty($items)) { ?>
<div class="panel panel-borderless panel-default collection collection-product">
  <h4 class="panel-title"><?php echo $this->e($title); ?></h4>
  <div class="panel-body">
    <div class="row row-no-padding">
      <?php foreach ($items as $item) { ?>
      <?php echo $item['rendered']; ?>
      <?php } ?>
    </div>
  </div>
</div>
<?php } ?>