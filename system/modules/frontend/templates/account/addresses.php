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
<div class="row">
  <div class="col-md-3">
    <div class="list-group">
      <a class="list-group-item list-group-item-action" href="<?php echo $this->url("account/{$user['user_id']}"); ?>">
        <h4 class="h5"><b><?php echo $this->e($this->truncate($user['name'], 20)); ?></b></h4>
        <p><?php echo $this->e($user['email']); ?></p>
      </a>
      <a class="list-group-item list-group-item-action active disabled" href="<?php echo $this->url("account/{$user['user_id']}/address"); ?>">
        <h4 class="h5"><?php echo $this->text('Addresses'); ?></h4>
        <p><?php echo $this->text('View and manage addressbook'); ?></p>
      </a>
      <?php if ($_uid == $user['user_id'] || $this->access('user_edit')) { ?>
      <a class="list-group-item list-group-item-action" href="<?php echo $this->url("account/{$user['user_id']}/edit"); ?>">
        <h4 class="h5"><?php echo $this->text('Settings'); ?></h4>
        <p><?php echo $this->text('Edit account details'); ?></p>
      </a>
      <?php } ?>
    </div>
  </div>
  <div class="col-md-9">
    <?php if (!empty($addresses)) { ?>
    <div class="row addresses">
      <?php foreach ($addresses as $address_id => $address) { ?>
      <div class="col-md-4">
        <div class="card address">
          <div class="card-header clearfix">
            <?php if (($_uid == $user['user_id'] || $this->access('user_edit')) && empty($address['locked'])) { ?>
            <a class="btn btn-sm float-right" onclick="return confirm('<?php echo $this->text('Delete?'); ?>');" title="<?php echo $this->text('Delete'); ?>" href="<?php echo $this->url('', array('delete' => $address_id, 'token' => $_token)); ?>">
              <i class="fa fa-trash"></i>
            </a>
            <?php } else { ?>
            <span class="disabled btn btn-sm float-right"><i class="fa fa-trash"></i></span>
            <?php } ?>
          </div>
          <div class="card-body">
            <table class="table table-sm address">
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
    <?php if (empty($addresses) && $_uid == $user['user_id']) { ?>
    <div class="row">
      <div class="col-md-12">
        <?php echo $this->text('You have no saved addresses yet'); ?>
      </div>
    </div>
    <?php } ?>
  </div>
</div>

