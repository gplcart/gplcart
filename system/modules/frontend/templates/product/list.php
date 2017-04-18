<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<?php if (!empty($products)) { ?>
<div class="products row section">
  <?php foreach ($products as $product) { ?>
  <?php echo $product['rendered']; ?>
  <?php } ?>
</div>
<?php } ?>