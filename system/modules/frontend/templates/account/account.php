<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 * 
 * To see available variables: <?php print_r(get_defined_vars()); ?>
 * To see the current controller object: <?php print_r($this); ?>
 * To call a controller method: <?php $this->exampleMethod(); ?>
 */
?>
<div class="row">
  <div class="col-md-3">
    <div class="list-group">
      <a class="list-group-item active disabled">
        <h4 class="list-group-item-heading h5"><b><?php echo $this->truncate($this->escape($user['name']), 20); ?></b></h4>
        <p class="list-group-item-text"><?php echo $this->escape($user['email']); ?></p>
      </a>
      <a href="<?php echo $this->url("account/{$user['user_id']}/address"); ?>" class="list-group-item">
        <h4 class="list-group-item-heading h5"><?php echo $this->text('Addresses'); ?></h4>
        <p class="list-group-item-text"><?php echo $this->text('View and manage addressbook'); ?></p>
      </a>
      <a class="list-group-item" href="<?php echo $this->url("account/{$user['user_id']}/edit"); ?>">
        <h4 class="list-group-item-heading h5"><?php echo $this->text('Settings'); ?></h4>
        <p class="list-group-item-text"><?php echo $this->text('Edit account details'); ?></p>
      </a>
    </div>
    <a class="btn btn-default" href="<?php echo $this->url('logout'); ?>">
      <span class="fa fa-sign-out"></span> <?php echo $this->text('Log out'); ?>
    </a>
  </div>
  <div class="col-md-9">
    <?php if (!empty($orders)) { ?>
    <div class="panel-group" id="orders">
      <?php foreach ($orders as $order_id => $order) { ?>
      <div class="panel panel-default">
        <div class="panel-heading">
          <span class="panel-title">
          <a role="button" data-toggle="collapse" data-parent="#orders" href="#order-<?php echo $order_id; ?>">
            <b>#<?php echo $order['order_id']; ?></b>
            <?php echo $this->text('Created'); ?>:
            <?php echo $this->date($order['created']); ?>,
            <?php echo $this->text('Status'); ?>:
            <?php if (empty($order['status_formatted'])) { ?>
            <span class="text-danger"><?php echo $this->text('Unknown'); ?>,</span>
            <?php } else { ?>
            <?php echo $this->escape($order['status_formatted']); ?>,
            <?php } ?>
            <?php echo $this->text('Total'); ?>: <?php echo $order['total_formatted']; ?>
          </a>
          </span>
        </div>
        <div id="order-<?php echo $order_id; ?>" class="panel-collapse collapse">
          <div class="panel-body">
            <?php echo $order['rendered']; ?>
          </div>
        </div>
      </div>
      <?php } ?>
    </div>
    <?php if (!empty($pager)) { ?>
    <?php echo $pager; ?>
    <?php } ?>
    <?php } else { ?>
    <?php echo $this->text('You have no orders yet. <a href="!href">Shop now</a>', array('!href' => $this->url('/'))); ?>
    <?php } ?>
  </div>
</div>   
