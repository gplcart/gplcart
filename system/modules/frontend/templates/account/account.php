<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<div class="row account">
  <div class="col-md-3">
    <div class="list-group">
      <a class="list-group-item active disabled">
        <h4 class="list-group-item-heading h5"><b><?php echo $this->e($this->truncate($user['name'], 20)); ?></b></h4>
        <p class="list-group-item-text"><?php echo $this->e($user['email']); ?></p>
      </a>
      <a href="<?php echo $this->url("account/{$user['user_id']}/address"); ?>" class="list-group-item">
        <h4 class="list-group-item-heading h5"><?php echo $this->text('Addresses'); ?></h4>
        <p class="list-group-item-text"><?php echo $this->text('View and manage addressbook'); ?></p>
      </a>
      <?php if ($this->user('user_id') == $user['user_id'] || $this->access('user_edit')) { ?>
      <a class="list-group-item" href="<?php echo $this->url("account/{$user['user_id']}/edit"); ?>">
        <h4 class="list-group-item-heading h5"><?php echo $this->text('Settings'); ?></h4>
        <p class="list-group-item-text"><?php echo $this->text('Edit account details'); ?></p>
      </a>
      <?php } ?>
    </div>
    <?php if ($this->user('user_id') == $user['user_id']) { ?>
    <a class="btn btn-default" href="<?php echo $this->url('logout'); ?>">
      <span class="fa fa-sign-out"></span> <?php echo $this->text('Log out'); ?>
    </a>
    <?php } ?>
  </div>
  <div class="col-md-9">
    <?php if (empty($user['status']) && $this->access('user')) { ?>
    <div class="alert alert-warning" role="alert">
      <?php echo $this->text('This account is inactive'); ?>
    </div>
    <?php } ?>
    <?php if (!empty($orders)) { ?>
    <div class="table-responsive">
      <table class="table">
        <thead>
          <tr>
            <th><?php echo $this->text('ID'); ?></th>
            <th><?php echo $this->text('Created'); ?></th>
            <th><?php echo $this->text('Modified'); ?></th>
            <th><?php echo $this->text('Status'); ?></th>
            <th><?php echo $this->text('Total'); ?></th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($orders as $order_id => $order) { ?>
          <tr>
            <td><?php echo $this->e($order['order_id']); ?></td>
            <td><?php echo $this->date($order['created']); ?></td>
            <td>
              <?php if (empty($order['modified'])) { ?>
              <?php echo $this->text('Never'); ?>
              <?php } else { ?>
              <?php echo $this->date($order['modified']); ?>
              <?php } ?>
            </td>
            <td><?php echo $this->e($order['status_name']); ?></td>
            <td><?php echo $this->e($order['total_formatted']); ?></td>
            <td>
              <a href="<?php echo $this->url("account/{$order['user_id']}/order/{$order['order_id']}"); ?>">
                <?php echo $this->lower($this->text('View')); ?>
              </a>
            </td>
          </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
    <?php echo $pager; ?>
    <?php } else { ?>
    <?php if ($this->user('user_id') == $user['user_id']) { ?>
    <?php echo $this->text('You have no orders yet. <a href="@href">Shop now</a>', array('@href' => $this->url('/'))); ?>
    <?php } ?>
    <?php } ?>
  </div>
</div>