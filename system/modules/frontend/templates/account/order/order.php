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
<div class="row order-details">
  <div class="col-md-3">
    <div class="list-group">
      <a href="<?php echo $this->url("account/{$user['user_id']}"); ?>" class="list-group-item active disabled">
        <h4 class="list-group-item-heading h5"><b><?php echo $this->truncate($user['name'], 20); ?></b></h4>
        <p class="list-group-item-text"><?php echo $this->e($user['email']); ?></p>
      </a>
      <a href="<?php echo $this->url("account/{$user['user_id']}/address"); ?>" class="list-group-item">
        <h4 class="list-group-item-heading h5"><?php echo $this->text('Addresses'); ?></h4>
        <p class="list-group-item-text"><?php echo $this->text('View and manage addressbook'); ?></p>
      </a>
      <?php if ($_uid == $user['user_id'] || $this->access('user_edit')) { ?>
      <a class="list-group-item" href="<?php echo $this->url("account/{$user['user_id']}/edit"); ?>">
        <h4 class="list-group-item-heading h5"><?php echo $this->text('Settings'); ?></h4>
        <p class="list-group-item-text"><?php echo $this->text('Edit account details'); ?></p>
      </a>
      <?php } ?>
    </div>
    <?php if ($_uid == $user['user_id']) { ?>
    <a class="btn btn-default" href="<?php echo $this->url('logout'); ?>">
      <span class="fa fa-sign-out"></span> <?php echo $this->text('Log out'); ?>
    </a>
    <?php } ?>
  </div>
  <div class="col-md-9">
    <div class="row">
      <div class="<?php echo $payment_address ? 'col-md-4' : 'col-md-6'; ?>">
        <?php echo $summary; ?>
      </div>
      <div class="<?php echo $payment_address ? 'col-md-4' : 'col-md-6'; ?>">
        <?php echo $shipping_address; ?>
      </div>
      <?php if ($payment_address) { ?>
      <div class="col-md-4">
        <?php echo $payment_address; ?>
      </div>
      <?php } ?>
    </div>
    <div class="row">
      <div class="col-md-12"><?php echo $components; ?></div>
    </div>
  </div>
</div>



