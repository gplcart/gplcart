<form method="post" id="edit-city" class="form-horizontal" onsubmit="return confirm();">
  <input type="hidden" name="token" value="<?php echo $token; ?>">
  <div class="panel panel-default">
    <div class="panel-body">
      <div class="form-group">
        <label class="col-md-2 control-label"><?php echo $this->text('Status'); ?></label>
        <div class="col-md-4">
          <div class="btn-group" data-toggle="buttons">
            <label class="btn btn-default<?php echo empty($city['status']) ? '' : ' active'; ?>">
              <input name="city[status]" type="radio" autocomplete="off" value="1"<?php echo empty($city['status']) ? '' : ' checked'; ?>>
              <?php echo $this->text('Enabled'); ?>
            </label>
            <label class="btn btn-default<?php echo empty($city['status']) ? ' active' : ''; ?>">
              <input name="city[status]" type="radio" autocomplete="off" value="0"<?php echo empty($city['status']) ? ' checked' : ''; ?>>
              <?php echo $this->text('Disabled'); ?>
            </label>
          </div>
          <div class="help-block">
            <?php echo $this->text('Disabled cities will not be displayed to customers'); ?>
          </div>
        </div>
      </div>
      <div class="form-group required<?php echo isset($this->errors['name']) ? ' has-error' : ''; ?>">
        <label class="col-md-2 control-label"><?php echo $this->text('Name'); ?></label>
        <div class="col-md-4">
          <input maxlength="255" name="city[name]" class="form-control" value="<?php echo isset($city['name']) ? $this->escape($city['name']) : ''; ?>" autofocus>
          <div class="help-block">
            <?php if (isset($this->errors['name'])) { ?>
            <?php echo $this->errors['name']; ?>
            <?php } ?>
            <div class="text-muted"><?php echo $this->text('Required. Native name of the city'); ?></div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="panel panel-default">
    <div class="panel-body">
      <div class="row">
        <div class="col-md-2">
          <?php if (isset($city['city_id']) && $this->access('city_delete')) { ?>
          <button class="btn btn-danger delete" name="delete" value="1">
            <i class="fa fa-trash"></i> <?php echo $this->text('Delete'); ?>
          </button>
          <?php } ?>
        </div>
        <div class="col-md-4">
          <div class="btn-toolbar">
            <a href="<?php echo $this->url("admin/settings/cities/{$country['code']}/{$state['state_id']}"); ?>" class="btn btn-default">
              <i class="fa fa-reply"></i> <?php echo $this->text('Cancel'); ?>
            </a>
            <?php if ($this->access('city_edit') || $this->access('city_add')) { ?>
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