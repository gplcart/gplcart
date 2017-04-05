<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<div class="panel panel-default">
  <div class="panel-heading">
    <?php echo $this->text('Recent orders'); ?>
  </div>
  <div class="panel-body">
    <?php if (!empty($orders)) { ?>
    <table class="table table-responsive table-condensed">
      <tbody>
        <?php foreach ($orders as $order) { ?>
        <tr>
          <td>
            <a href="<?php echo $this->url("admin/sale/order/{$order['order_id']}"); ?>">
            <b>#<?php echo $order['order_id']; ?></b>
            <?php echo $this->text('Created'); ?>: <?php echo $this->date($order['created']); ?>,
            <?php echo $this->text('Total'); ?>: <?php echo $order['total_formatted']; ?>
            </a>
            <?php if(!empty($order['is_new'])) { ?>
            <span class="label label-danger"><?php echo $this->text('new'); ?></span>
            <?php } ?>
          </td>
        </tr>
        <?php } ?>
      </tbody>
    </table>
    <div class="text-right">
      <a href="<?php echo $this->url('admin/sale/order'); ?>">
        <?php echo $this->text('See all'); ?>
      </a>
    </div>
    <?php } else { ?>
    <?php echo $this->text('No have no orders yet'); ?>
    <?php if ($this->access('order_add') && $this->access('user')) { ?>
    <a href="<?php echo $this->url('admin/user/list'); ?>">
      <?php echo $this->text('Add'); ?>
    </a>
    <?php } ?>
    <?php } ?>		
  </div>
</div>