<div class="row">
  <div class="col-md-3">
    <div class="list-group">
      <a href="<?php echo $this->url("account/{$user['user_id']}"); ?>" class="list-group-item">
        <h4 class="list-group-item-heading"><span class="fa fa-user"></span> <?php echo $this->truncate($this->escape($user['name']), 20); ?></h4>
        <p class="list-group-item-text"><?php echo $this->escape($user['email']); ?></p>
      </a>
      <a class="list-group-item active disabled">
        <h4 class="list-group-item-heading"><?php echo $this->text('Addresses'); ?></h4>
        <p class="list-group-item-text"><?php echo $this->text('View and manage addressbook'); ?></p>
      </a>
      <a href="<?php echo $this->url("account/{$user['user_id']}/edit"); ?>" class="list-group-item">
        <h4 class="list-group-item-heading"><?php echo $this->text('Settings'); ?></h4>
        <p class="list-group-item-text"><?php echo $this->text('Edit account details'); ?></p>
      </a>
    </div>
    <a href="<?php echo $this->url('logout'); ?>">
      <span class="fa fa-sign-out"></span> <?php echo $this->text('Log out'); ?>
    </a>
  </div>
  <div class="col-md-9">
    <?php if ($addresses) { ?>
    <div class="row addresses">
      <?php foreach ($addresses as $address_id => $address) { ?>
      <div class="col-md-4">
        <div class="panel panel-default address">
          <div class="panel-body">
            <table class="table table-condensed address">
              <tr>
                <td colspan="2" class="text-right">
                  <a href="<?php echo $this->url(false, array('delete' => $address_id)); ?>"><?php echo $this->text('Delete'); ?></a>
                </td>
              </tr>
              <?php foreach ($address as $label => $value) { ?>
              <tr>
                <td><?php echo is_numeric($label) ? '' : $this->escape($label); ?></td>
                <td><?php echo $this->escape($value); ?></td>
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
      <div class="col-md-6">
        <?php if(empty($addresses)) { ?>
        <?php echo $this->text('You have no saved addresses yet'); ?>
        <?php } ?>
        <?php if ($can_add) { ?>
        <p>
          <a href="<?php echo $this->url("account/{$user['user_id']}/address/add"); ?>">
            <?php echo $this->text('Add new address'); ?>
          </a>
        </p>
        <?php } ?>
      </div>
    </div>
  </div>
</div>
