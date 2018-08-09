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
<div class="card order-shipping-address">
  <div class="card-header clearfix"><?php echo $this->text('Shipping address'); ?></div>
  <div class="card-body">
    <?php if(empty($order['address_translated']['shipping'])) { ?>
    <?php echo $this->text('Unknown'); ?>
    <?php } else { ?>
    <table class="table table-sm">
      <tbody>
        <?php foreach($order['address_translated']['shipping'] as $name => $value) { ?>
        <tr>
          <td><?php echo $this->e($name); ?></td>
          <td><?php echo $this->e($value); ?></td>
        </tr>
        <?php } ?>
      </tbody>
    </table>
    <?php } ?>
  </div>
</div>


