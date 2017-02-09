<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<?php if (isset($order['user']['user_id'])) { ?>
<div class="panel panel-default">
  <div class="panel-heading"><?php echo $this->text('Customer'); ?></div>
  <div class="panel-body">
    <div class="row">
      <div class="col-md-12">
        <table class="table table-condensed">
          <tr>
            <td><?php echo $this->text('ID'); ?></td>
            <td>
              <a href="<?php echo $this->url("account/{$order['user']['user_id']}/edit"); ?>">
                <?php echo $this->escape($order['user']['user_id']); ?>
              </a>
            </td>
          </tr>
          <tr>
            <td><?php echo $this->text('E-mail'); ?></td>
            <td><?php echo $this->escape($order['user']['email']); ?></td>
          </tr>
          <tr>
            <td><?php echo $this->text('Name'); ?></td>
            <td><?php echo $this->escape($order['user']['name']); ?></td>
          </tr>
          <tr>
            <td><?php echo $this->text('Role'); ?></td>
            <td>
              <?php echo $this->escape($order['user']['role_name']); ?>
              <?php if (empty($order['user']['role_name'])) { ?>
              <?php echo $this->text('Unknown'); ?>
              <?php } else { ?>
              <?php echo $this->escape($order['user']['role_name']); ?>
              <?php } ?>
            </td>
          </tr>
          <tr>
            <td><?php echo $this->text('Created'); ?></td>
            <td><?php echo $this->date($order['user']['created']); ?></td>
          </tr>
          <tr>
            <td><?php echo $this->text('Status'); ?></td>
            <td>
              <?php if (empty($order['user']['status'])) { ?>
              <span class="text-danger"><?php echo $this->text('Disabled'); ?></span>
              <?php } else { ?>
              <span class="text-success"><?php echo $this->text('Enabled'); ?></span>
              <?php } ?>
            </td>
          </tr>
          <tr>
            <td><?php echo $this->text('Orders placed'); ?></td>
            <td>
              <a href="<?php echo $this->url('admin/sale/order', array('user_id' => $order['user']['user_id'])); ?>">
                <?php echo $this->escape($order['user']['total_orders']); ?>
              </a>
            </td>
          </tr>
        </table>
      </div>
    </div>
  </div>
</div>
<?php } ?>