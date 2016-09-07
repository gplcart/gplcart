<form method="post" id="edit-state" class="form-horizontal" onsubmit="return confirm();">
  <input type="hidden" name="token" value="<?php echo $token; ?>">
  <div class="panel panel-default">
    <div class="panel-body">
      <div class="form-group">
        <label class="col-md-2 control-label"><?php echo $this->text('Status'); ?></label>
        <div class="col-md-4">
          <div class="btn-group" data-toggle="buttons">
            <label class="btn btn-default<?php echo empty($state['status']) ? '' : ' active'; ?>">
              <input name="state[status]" type="radio" autocomplete="off" value="1"<?php echo empty($state['status']) ? '' : ' checked'; ?>><?php echo $this->text('Enabled'); ?>
            </label>
            <label class="btn btn-default<?php echo empty($state['status']) ? ' active' : ''; ?>">
              <input name="state[status]" type="radio" autocomplete="off" value="0"<?php echo empty($state['status']) ? ' checked' : ''; ?>><?php echo $this->text('Disabled'); ?>
            </label>
          </div>
          <div class="help-block">
            <?php echo $this->text('Disabled states will not be displayed to customers'); ?>
          </div>
        </div>
      </div>
      <div class="form-group required<?php echo isset($this->errors['name']) ? ' has-error' : ''; ?>">
        <label class="col-md-2 control-label"><?php echo $this->text('Name'); ?></label>
        <div class="col-md-4">
          <input type="text" name="state[name]" maxlength="255" class="form-control" value="<?php echo isset($state['name']) ? $this->escape($state['name']) : ''; ?>">
          <div class="help-block">
            <?php if (isset($this->errors['name'])) { ?>
            <?php echo $this->errors['name']; ?>
            <?php } ?>
            <div class="text-muted">
              <?php echo $this->text('Required. An official name of the state'); ?>
            </div>
          </div>
        </div>
      </div>
      <div class="form-group required<?php echo isset($this->errors['code']) ? ' has-error' : ''; ?>">
        <label class="col-md-2 control-label"><?php echo $this->text('Code'); ?></label>
        <div class="col-md-4">
          <input type="text" name="state[code]" maxlength="255" class="form-control" value="<?php echo isset($state['code']) ? $this->escape($state['code']) : ''; ?>">
          <div class="help-block">
            <?php if (isset($this->errors['code'])) { ?>
            <?php echo $this->errors['code']; ?>
            <?php } ?>
            <div class="text-muted">
              <?php echo $this->text('Required. A code / abbreviation used to represent the state in the country, e.g NY for New York'); ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="panel panel-default">
    <div class="panel-body">
      <div class="row">
        <div class="col-md-2">
          <?php if (isset($state['state_id']) && $this->access('state_delete')) { ?>
          <button class="btn btn-danger delete" name="delete" value="1">
            <i class="fa fa-trash"></i> <?php echo $this->text('Delete'); ?>
          </button>
          <?php } ?>
        </div>
        <div class="col-md-4">
          <div class="btn-toolbar">
            <a href="<?php echo $this->url("admin/settings/states/{$country['code']}"); ?>" class="btn btn-default cancel"><i class="fa fa-reply"></i> <?php echo $this->text('Cancel'); ?></a>
            <?php if ($this->access('state_edit') || $this->access('state_add')) { ?>
            <button class="btn btn-default save" name="save" value="1">
              <i class="fa fa-floppy-o"></i> <?php echo $this->text('Save'); ?>
            </button>
            <?php } ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</form>
