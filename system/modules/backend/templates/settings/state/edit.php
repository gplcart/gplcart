<form method="post" id="edit-state" class="form-horizontal" onsubmit="return confirm();">
  <input type="hidden" name="token" value="<?php echo $token; ?>">
  <div class="row">
    <div class="col-md-6 col-md-offset-6 text-right">
      <div class="btn-toolbar">
        <?php if (isset($state['state_id']) && $this->access('state_delete')) { ?>
        <button class="btn btn-danger delete" name="delete" value="1">
          <i class="fa fa-trash"></i> <?php echo $this->text('Delete'); ?>
        </button>
        <?php } ?>
        <a href="<?php echo $this->url("admin/settings/states/{$country['code']}"); ?>" class="btn btn-default cancel"><i class="fa fa-reply"></i> <?php echo $this->text('Cancel'); ?></a>
        <?php if ($this->access('state_edit') || $this->access('state_add')) { ?>
        <button class="btn btn-primary save" name="save" value="1">
          <i class="fa fa-floppy-o"></i> <?php echo $this->text('Save'); ?>
        </button>
        <?php } ?>
      </div>
    </div>
  </div>
  <div class="row margin-top-20">
    <div class="col-md-12">
      <div class="form-group">
        <label class="col-md-2 control-label">
          <span class="hint" title="<?php echo $this->text('Disabled states will not be displayed to customers and managers'); ?>">
          <?php echo $this->text('Status'); ?>
          </span>
        </label>
        <div class="col-md-4">
          <div class="btn-group" data-toggle="buttons">
            <label class="btn btn-default<?php echo empty($state['status']) ? '' : ' active'; ?>">
              <input name="state[status]" type="radio" autocomplete="off" value="1"<?php echo empty($state['status']) ? '' : ' checked'; ?>><?php echo $this->text('Enabled'); ?>
            </label>
            <label class="btn btn-default<?php echo empty($state['status']) ? ' active' : ''; ?>">
              <input name="state[status]" type="radio" autocomplete="off" value="0"<?php echo empty($state['status']) ? ' checked' : ''; ?>><?php echo $this->text('Disabled'); ?>
            </label>
          </div>
        </div>
      </div>
      <div class="form-group<?php echo isset($this->errors['name']) ? ' has-error' : ''; ?>">
        <label class="col-md-2 control-label">
          <span class="hint" title="<?php echo $this->text('Official name of the state'); ?>">
          <?php echo $this->text('Name'); ?>
          </span>
        </label>
        <div class="col-md-4">
          <input type="text" name="state[name]" maxlength="255" class="form-control" value="<?php echo isset($state['name']) ? $this->escape($state['name']) : ''; ?>" required>
          <?php if (isset($this->errors['name'])) { ?>
          <div class="help-block"><?php echo $this->errors['name']; ?></div>
          <?php } ?>
        </div>
      </div>
      <div class="form-group<?php echo isset($this->errors['code']) ? ' has-error' : ''; ?>">
        <label class="col-md-2 control-label">
          <span class="hint" title="<?php echo $this->text('Code/abbreviation used to represent the state in the country'); ?>">
          <?php echo $this->text('Code'); ?>
          </span>
        </label>
        <div class="col-md-4">
          <input type="text" name="state[code]" maxlength="255" class="form-control" value="<?php echo isset($state['code']) ? $this->escape($state['code']) : ''; ?>" required>
          <?php if (isset($this->errors['code'])) { ?>
          <div class="help-block"><?php echo $this->errors['code']; ?></div>
          <?php } ?>
        </div>
      </div>
    </div>
  </div>
</form>
