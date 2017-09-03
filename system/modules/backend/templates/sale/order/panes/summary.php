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
<div class="panel panel-default">
  <div class="panel-heading clearfix"><?php echo $this->text('Summary'); ?></div>
  <div class="panel-body">
    <table class="table table-condensed">
      <tr>
        <td class="col-md-3"><?php echo $this->text('Order ID'); ?></td>
        <td class="col-md-9"><?php echo $this->e($order['order_id']); ?></td>
      </tr>
      <tr>
        <td class="middle"><?php echo $this->text('Status'); ?></td>
        <td>
          <?php if ($this->access('order_edit')) { ?>
          <div class="input-group hidden-print">
            <select class="form-control" name="order[status]">
              <?php foreach ($statuses as $code => $name) { ?>
              <option value="<?php echo $code; ?>"<?php echo $order['status'] == $code ? ' selected' : ''; ?>>
                <?php echo $this->e($name); ?>
              </option>
              <?php } ?>
            </select>
            <span class="input-group-btn">
              <button class="btn btn-default hidden-js" name="status" value="1"><?php echo $this->text('Update status'); ?></button>
            </span>
          </div>
          <span class="visible-print"><?php echo $this->e($order['status_name']); ?></span>
          <?php } else { ?>
          <?php echo $this->e($order['status_name']); ?>
          <?php } ?>
        </td>
      </tr>
      <tr>
        <td><?php echo $this->text('Shipping'); ?></td>
        <td><?php echo $this->e($order['shipping_name']); ?></td>
      </tr>
      <tr>
        <td><?php echo $this->text('Payment'); ?></td>
        <td><?php echo $this->e($order['payment_name']); ?></td>
      </tr>
      <tr>
        <td><?php echo $this->text('Created'); ?></td>
        <td><?php echo $this->date($order['created']); ?></td>
      </tr>
      <?php if (!empty($order['modified'])) { ?>
      <tr>
        <td><?php echo $this->text('Last modified'); ?></td>
        <td><?php echo $this->date($order['modified']); ?></td>
      </tr>
      <?php } ?>
      <tr>
        <td><?php echo $this->text('Store'); ?></td>
        <td>
          <?php echo $this->e($order['store_name']); ?>
        </td>
      </tr>
      <tr>
        <td><?php echo $this->text('Creator'); ?></td>
        <td><?php echo $this->e($order['creator_formatted']); ?></td>
      </tr>
      <tr>
        <td><?php echo $this->text('IP'); ?></td>
        <td>
          <?php if (empty($order['data']['user']['ip'])) { ?>
          <?php echo $this->text('Unknown'); ?>
          <?php } else { ?>
          <?php echo $this->e($order['data']['user']['ip']); ?>
          <?php } ?>
        </td>
      </tr>
      <tr>
        <td><?php echo $this->text('User agent'); ?></td>
        <td>
          <?php if (empty($order['data']['user']['agent'])) { ?>
          <?php echo $this->text('Unknown'); ?>
          <?php } else { ?>
          <?php echo $this->e($order['data']['user']['agent']); ?>
          <?php } ?>
        </td>
      </tr>
      <?php if(!empty($order['tracking_number'])) { ?>
      <tr>
        <td><?php echo $this->text('Tracking number'); ?></td>
        <td>
          <?php echo $this->e($order['tracking_number']); ?>
        </td>
      </tr>
      <?php } ?>
    </table>
  </div>
</div>