<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 * @var $this \gplcart\core\controllers\backend\Controller
 * To see available variables <?php print_r(get_defined_vars()); ?>
 */
?>
<form method="post" class="form-horizontal">
  <input type="hidden" name="token" value="<?php echo $_token; ?>">
  <div class="row">
    <div class="col-md-6">
      <div class="form-group">
        <label class="col-md-3 control-label">
          <?php echo $this->text('Status'); ?>
        </label>
        <div class="col-md-9">
          <div class="btn-group" data-toggle="buttons">
            <label class="btn btn-default<?php echo empty($trigger['status']) ? '' : ' active'; ?>">
              <input name="trigger[status]" type="radio" autocomplete="off" value="1"<?php echo empty($trigger['status']) ? '' : ' checked'; ?>><?php echo $this->text('Enabled'); ?>
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
      <div class="form-group required<?php echo $this->error('name', ' has-error'); ?>">
        <label class="col-md-3 control-label"><?php echo $this->text('Name'); ?></label>
        <div class="col-md-9">
          <input maxlength="255" name="trigger[name]" class="form-control" value="<?php echo isset($trigger['name']) ? $this->e($trigger['name']) : ''; ?>">
          <div class="help-block">
            <?php echo $this->error('name'); ?>
            <div class="text-muted">
              <?php echo $this->text('The name will be shown to administrators'); ?>
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
            <?php foreach ($_stores as $store_id => $store) { ?>
            <?php if (isset($trigger['store_id']) && $trigger['store_id'] == $store_id) { ?>
            <option value="<?php echo $store_id; ?>" selected><?php echo $this->e($store['name']); ?></option>
            <?php } else { ?>
            <option value="<?php echo $store_id; ?>"><?php echo $this->e($store['name']); ?></option>
            <?php } ?>
            <?php } ?>
          </select>
          <div class="help-block">
            <?php echo $this->text('Select a store that is associated with this trigger'); ?>
          </div>
        </div>
      </div>
      <div class="form-group<?php echo $this->error('weight', ' has-error'); ?>">
        <label class="col-md-3 control-label"><?php echo $this->text('Weight'); ?></label>
        <div class="col-md-9">
          <input maxlength="2" name="trigger[weight]" class="form-control" value="<?php echo isset($trigger['weight']) ? $this->e($trigger['weight']) : '0'; ?>">
          <div class="help-block">
            <?php echo $this->error('weight'); ?>
            <div class="text-muted">
              <?php echo $this->text('Position of the trigger among other enabled triggers. Triggers with lower weight are invoked earlier'); ?>
            </div>
          </div>
        </div>
      </div>
      <div class="form-group required<?php echo $this->error('data.conditions', ' has-error'); ?>">
        <label class="col-md-3 control-label"><?php echo $this->text('Conditions'); ?></label>
        <div class="col-md-9">
          <textarea name="trigger[data][conditions]" rows="6" class="form-control"><?php echo empty($trigger['data']['conditions']) ? '' : $this->e($trigger['data']['conditions']); ?></textarea>
          <div class="help-block">
            <?php echo $this->error('data.conditions'); ?>
            <div class="text-muted">
              <?php echo $this->text('Which conditions must be met to invoke the trigger. One condition per line. See the legend. Conditions are checked from the top to bottom. Format: [condition ID][space][operator][space][parameter(s)]'); ?>
            </div>
          </div>
        </div>
      </div>
      <div class="form-group">
        <div class="col-md-10 col-md-offset-3">
          <div class="btn-toolbar">
            <?php if ($can_delete) { ?>
            <button class="btn btn-danger delete" name="delete" value="1" onclick="return confirm('<?php echo $this->text('Are you sure? It cannot be undone!'); ?>');">
              <?php echo $this->text('Delete'); ?>
            </button>
            <?php } ?>
            <a class="btn btn-default cancel" href="<?php echo $this->url('admin/settings/trigger'); ?>">
              <?php echo $this->text('Cancel'); ?>
            </a>
            <?php if ($this->access('trigger_edit') || $this->access('trigger_add')) { ?>
            <button class="btn btn-default save" name="save" value="1">
              <?php echo $this->text('Save'); ?>
            </button>
            <?php } ?>
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
                <td><?php echo $this->text('Operator'); ?></td>
                <td><?php echo $this->text('Description'); ?></td>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($operators as $key => $name) { ?>
              <tr>
                <td><?php echo $this->e($key); ?></td>
                <td><?php echo $this->e($name); ?></td>
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
                <td><?php echo $this->text('ID'); ?></td>
                <td><?php echo $this->text('Description'); ?></td>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($conditions as $id => $info) { ?>
              <tr>
                <td class="middle"><?php echo $id; ?></td>
                <td>
                  <?php echo $this->e($this->text($info['title'])); ?>
                  <?php if (!empty($info['description'])) { ?>
                  <div class="text-muted small"><?php echo $this->filter($this->text($info['description'])); ?></div>
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