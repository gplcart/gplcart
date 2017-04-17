<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<div class="row">
  <div class="col-md-3">
    <div class="list-group">
      <a href="<?php echo $this->url("account/{$user['user_id']}"); ?>" class="list-group-item">
        <h4 class="list-group-item-heading h5"><b><?php echo $this->e($this->truncate($user['name'], 20)); ?></b></h4>
        <p class="list-group-item-text"><?php echo $this->e($user['email']); ?></p>
      </a>
      <a href="<?php echo $this->url("account/{$user['user_id']}/address"); ?>" class="list-group-item active disabled">
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
    <?php if (!empty($addresses)) { ?>
    <div class="row addresses">
      <?php foreach ($addresses as $address_id => $address) { ?>
      <div class="col-md-4">
        <div class="panel panel-default address" data-equal-height="true">
          <div class="panel-heading clearfix">
            <?php if (($this->user('user_id') == $user['user_id'] || $this->access('user_edit')) && empty($address['locked'])) { ?>
            <a class="btn btn-default btn-sm pull-right" onclick="return confirm(GplCart.text('Are you sure?'));" title="<?php echo $this->text('Delete'); ?>" href="<?php echo $this->url('', array('delete' => $address_id)); ?>">
              <i class="fa fa-trash"></i>
            </a>
            <?php } else { ?>
            <span class="disabled btn btn-default btn-sm pull-right"><i class="fa fa-trash"></i></span>
            <?php } ?>
          </div>
          <div class="panel-body">
            <table class="table table-condensed address">
              <?php foreach ($address['items'] as $label => $value) { ?>
              <tr>
                <td><?php echo $this->e($label); ?></td>
                <td><?php echo $this->e($value); ?></td>
              </tr>
              <?php } ?>
            </table>
          </div>
        </div>
      </div>
      <?php } ?>
    </div>
    <?php } ?>
    <div class="row">
      <div class="col-md-12">
        <?php if (empty($addresses) && $this->user('user_id') == $user['user_id']) { ?>
        <p>
          <?php echo $this->text('Currently you have no saved addresses, but they will be added after next <a href="@href">checkout</a>', array('@href' => $this->url('checkout'))); ?>
        </p>
        <?php } ?>
      </div>
    </div>
  </div>
</div>

