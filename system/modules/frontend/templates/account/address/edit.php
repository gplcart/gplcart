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
    <form method="post" id="edit-address" class="form-horizontal margin-top-20">
      <input type="hidden" name="token" value="<?php echo $token; ?>">
      <?php foreach ($format as $key => $value) { ?>
      <div class="record <?php echo $key; ?>"<?php echo empty($value['status']) ? ' style="display:none;"' : ''; ?>>
        <div class="form-group <?php echo $key; ?><?php echo isset($this->errors['format'][$key]) ? ' has-error' : ''; ?>">
          <label class="col-md-3 control-label">
            <?php if (!empty($value['required'])) { ?><span class="text-danger">* </span><?php } ?>
            <?php echo $this->escape($value['name']); ?>
          </label>
          <div class="col-md-6">
            <?php if ($key == 'country') { ?>
            <select class="form-control" name="address[country]">
              <?php foreach ($countries as $code => $name) { ?>
              <option value="<?php echo $code; ?>"<?php echo (isset($address['country']) && $address['country'] == $code) ? ' selected' : ''; ?>><?php echo $this->escape($name); ?></option>
              <?php } ?>
            </select>
            <?php } elseif ($key == 'state_id') { ?>
            <select class="form-control" name="address[state_id]">
              <option value="0"><?php echo $this->text('Not provided'); ?></option>
              <?php foreach ($states as $state_id => $state) { ?>
              <option value="<?php echo $state_id; ?>"<?php echo (isset($address['state_id']) && $address['state_id'] == $state_id) ? ' selected' : ''; ?>><?php echo $this->escape($state['name']); ?></option>
              <?php } ?>
            </select>
            <?php } else { ?>
            <input name="address[<?php echo $key; ?>]" maxlength="255" class="form-control" value="<?php echo isset($address[$key]) ? $this->escape($address[$key]) : ''; ?>">
            <?php } ?>
            <?php if (isset($this->errors['format'][$key])) { ?>
            <div class="help-block"><?php echo $this->xss($this->errors['format'][$key]); ?></div>
            <?php } ?>  
          </div>
        </div>
      </div>
      <?php } ?>
      <div class="row">
        <div class="col-md-4 col-md-offset-3">
          <a class="btn btn-default" href="<?php echo $this->url("account/{$user['user_id']}/address"); ?>">
              <?php echo $this->text('Cancel'); ?>
          </a>
        </div>
        <div class="col-md-2 text-right">
          <button class="btn btn-primary save" name="save" value="1">
            <i class="fa fa-floppy-o"></i> <?php echo $this->text('Save'); ?>
          </button>
        </div>
      </div>
    </form>
  </div>
</div>