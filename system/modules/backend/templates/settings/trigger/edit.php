<form method="post" id="edit-trigger" class="form-horizontal" onsubmit="return confirm();">
  <input type="hidden" name="token" value="<?php echo $token; ?>">
  <div class="row">
    <div class="col-md-6">
      <div class="panel panel-default">
        <div class="panel-body">
          <div class="form-group">
            <label class="col-md-3 control-label">
              <?php echo $this->text('Status'); ?>
            </label>
            <div class="col-md-9">
              <div class="btn-group" data-toggle="buttons">
                <label class="btn btn-default<?php echo!empty($trigger['status']) ? ' active' : ''; ?>">
                  <input name="trigger[status]" type="radio" autocomplete="off" value="1"<?php echo!empty($trigger['status']) ? ' checked' : ''; ?>><?php echo $this->text('Enabled'); ?>
                </label>
                <label class="btn btn-default<?php echo empty($trigger['status']) ? ' active' : ''; ?>">
                  <input name="trigger[status]" type="radio" autocomplete="off" value="0"<?php echo empty($trigger['status']) ? ' checked' : ''; ?>><?php echo $this->text('Disabled'); ?>
                </label>
              </div>
              <div class="help-block">
                <?php echo $this->text('Disabled triggers will be excluded from processing'); ?>
              </div>
            </div>
          </div>
          <div class="form-group required<?php echo isset($this->errors['name']) ? ' has-error' : ''; ?>">
            <label class="col-md-3 control-label"><?php echo $this->text('Name'); ?></label>
            <div class="col-md-9">
              <input maxlength="255" name="trigger[name]" class="form-control" value="<?php echo isset($trigger['name']) ? $this->escape($trigger['name']) : ''; ?>">
              <div class="help-block">
                <?php if (isset($this->errors['name'])) { ?>
                <?php echo $this->errors['name']; ?>
                <?php } ?>
                <div class="text-muted">
                  <?php echo $this->text('Required. The name will be shown to administrators'); ?>
                </div>
              </div>
            </div>
          </div>
          <div class="form-group">
            <label class="col-md-3 control-label">
              <?php echo $this->text('Store'); ?>
            </label>
            <div class="col-md-9">
              <select name="trigger[store_id]" class="form-control">
                <?php foreach ($stores as $store_id => $store_name) { ?>
                <?php if (isset($trigger['store_id']) && $trigger['store_id'] == $store_id) { ?>
                <option value="<?php echo $store_id; ?>" selected><?php echo $this->escape($store_name); ?></option>
                <?php } else { ?>
                <option value="<?php echo $store_id; ?>"><?php echo $this->escape($store_name); ?></option>
                <?php } ?>
                <?php } ?>
              </select>
              <div class="help-block">
                <?php echo $this->text('Select a store where to invoke this trigger'); ?>
              </div>
            </div>
          </div>
          <div class="form-group<?php echo isset($this->errors['weight']) ? ' has-error' : ''; ?>">
            <label class="col-md-3 control-label"><?php echo $this->text('Weight'); ?></label>
            <div class="col-md-9">
              <input maxlength="2" name="trigger[weight]" class="form-control" value="<?php echo isset($trigger['weight']) ? $this->escape($trigger['weight']) : '0'; ?>">
              <div class="help-block">
              <?php if (isset($this->errors['weight'])) { ?>
              <?php echo $this->errors['weight']; ?>
              <?php } ?>
              <div class="text-muted">
              <?php echo $this->text('A position of the trigger among other enabled triggers. Triggers with lower weight are invoked earlier'); ?>
              </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="panel panel-default">
        <div class="panel-body">
          <div class="form-group<?php echo isset($this->errors['data']['conditions']) ? ' has-error' : ''; ?>">
            <label class="col-md-3 control-label"><?php echo $this->text('Conditions'); ?></label>
            <div class="col-md-9">
              <textarea name="trigger[data][conditions]" rows="6" class="form-control" placeholder="<?php echo $this->text('E.g, user is logged in: user_id > 0'); ?>"><?php echo empty($trigger['data']['conditions']) ? '' : $this->escape($trigger['data']['conditions']); ?></textarea>
              <div class="help-block">
                <?php if (isset($this->errors['data']['conditions'])) { ?>
                <?php echo $this->errors['data']['conditions']; ?>
                <?php } ?>
                <div class="text-muted">
                  <?php echo $this->text('Required. What conditions must be met to invoke the trigger. One condition per line. See the legend. Conditions are checked from the top to bottom'); ?>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="panel panel-default">
        <div class="panel-body">
          <div class="row">
            <div class="col-md-3">
              <?php if (isset($trigger['trigger_id']) && $this->access('trigger_delete')) { ?>
              <button class="btn btn-danger delete" name="delete" value="1">
                <i class="fa fa-trash"></i> <?php echo $this->text('Delete'); ?>
              </button>
              <?php } ?>
            </div>
            <div class="col-md-9">
              <div class="btn-toolbar">
                <a href="<?php echo $this->url('admin/settings/trigger'); ?>" class="btn btn-default cancel">
                  <i class="fa fa-reply"></i> <?php echo $this->text('Cancel'); ?>
                </a>
                <?php if ($this->access('trigger_edit') || $this->access('trigger_add')) { ?>
                <button class="btn btn-default save" name="save" value="1">
                  <i class="fa fa-floppy-o"></i> <?php echo $this->text('Save'); ?>
                </button>
                <?php } ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="panel panel-default">
        <div class="panel-heading"><?php echo $this->text('Operators'); ?></div>
        <div class="panel-body">
          <table class="table table-striped table-condensed">
            <thead>
              <tr>
                <td><?php echo $this->text('Key'); ?></td>
                <td><?php echo $this->text('Description'); ?></td>
              </tr>
            </thead>
            <tbody>
              <?php foreach($operators as $key => $name) { ?>
              <tr>
                <td><?php echo $this->escape($key); ?></td>
                <td><?php echo $this->escape($name); ?></td>
              </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
      </div>
      <div class="panel panel-default">
        <div class="panel-heading"><?php echo $this->text('Conditions'); ?></div>
        <div class="panel-body">
          <table class="table table-striped table-condensed">
            <thead>
              <tr>
                <td><?php echo $this->text('Key'); ?></td>
                <td><?php echo $this->text('Description'); ?></td>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($conditions as $id => $info) { ?>
              <tr>
                <td class="middle"><?php echo $id; ?></td>
                <td>
                <?php echo $this->escape($info['title']); ?>
                <?php if (!empty($info['description'])) { ?>
                  <div class="text-muted small"><?php echo $this->escape($info['description']); ?></div>
                <?php } ?>
                </td>
              </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</form>