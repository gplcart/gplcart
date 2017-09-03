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
<?php if(empty($products)) { ?>
<?php echo $this->text('Your wishlist is empty. <a href="@url">Continue shopping</a>', array('@url' => $this->url('catalog'))); ?>
<?php } else { ?>
<?php echo $products; ?>
<?php } ?>