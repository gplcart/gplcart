<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<div class="panel panel-default">
  <div class="panel-heading clearfix">
    <?php echo $this->text('Summary'); ?>
    <a href="#" class="pull-right hidden-print" onclick="window.print(); return false;">
      <i class="fa fa-print"></i> <?php echo $this->text('Print'); ?>
    </a>
  </div>
  <div class="panel-body">
    <table class="table table-condensed">
      <tr>
        <td class="col-md-3"><?php echo $this->text('Order ID'); ?></td>
        <td class="col-md-9"><?php echo $this->escape($order['order_id']); ?></td>
      </tr>
      <tr>
        <td><?php echo $this->text('Status'); ?></td>
        <td><?php echo $this->escape($order['status_name']); ?></td>
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
        <?php if (empty($order['store_name'])) { ?>
        <?php echo $this->text('Unknown'); ?>
        <?php } else { ?>
        <?php echo $this->escape($order['store_name']); ?>
        <?php } ?>
        </td>
      </tr>
      <tr>
        <td><?php echo $this->text('Creator'); ?></td>
        <td><?php echo $this->escape($order['creator_formatted']); ?></td>
      </tr>
      <tr>
        <td><?php echo $this->text('IP'); ?></td>
        <td>
        <?php if (empty($order['data']['user']['ip'])) { ?>
        <?php echo $this->text('Unknown'); ?>
        <?php } else { ?>
        <?php echo $this->escape($order['data']['user']['ip']); ?>
        <?php } ?>
        </td>
      </tr>
      <tr>
        <td><?php echo $this->text('User agent'); ?></td>
        <td>
        <?php if (empty($order['data']['user']['agent'])) { ?>
        <?php echo $this->text('Unknown'); ?>
        <?php } else { ?>
        <?php echo $this->escape($order['data']['user']['agent']); ?>
        <?php } ?>
        </td>
      </tr>
    </table>
  </div>
</div>