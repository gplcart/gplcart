<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<?php if (!empty($order['payment_address']) && $order['payment_address'] != $order['shipping_address']) { ?>
<div class="panel panel-default payment-address">
  <div class="panel-heading"><?php echo $this->text('Payment address'); ?></div>
  <div class="panel-body">
  <?php if (empty($order['address_translated']['payment'])) { ?>
  <?php echo $this->text('Unknown'); ?>
  <?php } else { ?>
    <div class="row">
      <div class="col-md-12">
        <table class="table table-condensed">
          <?php foreach ($order['address_translated']['payment'] as $key => $value) { ?>
          <tr>
            <td><?php echo $this->e($key); ?></td>
            <td><?php echo $this->e($value); ?></td>
          </tr>
          <?php } ?>
        </table>
      </div>
    </div>
    <?php } ?>
  </div>
</div>
<?php } ?>