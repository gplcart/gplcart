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
<?php if (!empty($products)) { ?>
<div class="products row section display-flex">
  <?php foreach ($products as $product) { ?>
  <?php echo $product['rendered']; ?>
  <?php } ?>
</div>
<?php } ?>