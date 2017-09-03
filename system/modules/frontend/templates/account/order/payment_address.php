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
<?php if (!empty($order['payment_address']) && $order['payment_address'] != $order['shipping_address']) { ?>
<div class="panel panel-default order-payment-address" data-equal-height="true">
  <div class="panel-heading clearfix"><?php echo $this->text('Payment address'); ?></div>
  <div class="panel-body">
    <?php if (empty($order['address_translated']['payment'])) { ?>
    <?php echo $this->text('Unknown'); ?>
    <?php } else { ?>
    <table class="table table-condensed">
      <tbody>
        <?php foreach ($order['address_translated']['payment'] as $name => $value) { ?>
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
<?php } ?>


