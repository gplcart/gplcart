<div class="row">
  <div class="col-md-3">
    <div class="list-group">
      <a href="<?php echo $this->url("account/{$user['user_id']}"); ?>" class="list-group-item">
        <h4 class="list-group-item-heading h5"><b><?php echo $this->truncate($this->escape($user['name']), 20); ?></b></h4>
        <p class="list-group-item-text"><?php echo $this->escape($user['email']); ?></p>
      </a>
      <a class="list-group-item active disabled">
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
    
          <a class="btn btn-default" href="<?php echo $this->url("account/{$user['user_id']}/address"); ?>">
              <?php echo $this->text('Cancel'); ?>
          </a>
    
    <div id="address-form-wrapper">
    <?php echo $address_form; ?>
    </div>
  </div>
</div>