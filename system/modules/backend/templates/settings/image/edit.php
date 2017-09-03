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
        <label class="col-md-2 control-label"><?php echo $this->text('Status'); ?></label>
        <div class="col-md-10">
          <div class="btn-group" data-toggle="buttons">
            <label class="btn btn-default<?php echo empty($imagestyle['status']) ? '' : ' active'; ?>">
              <input name="imagestyle[status]" type="radio" autocomplete="off" value="1"<?php echo empty($imagestyle['status']) ? '' : ' checked'; ?>><?php echo $this->text('Enabled'); ?>
            </label>
            <label class="btn btn-default<?php echo empty($imagestyle['status']) ? ' active' : ''; ?>">
              <input name="imagestyle[status]" type="radio" autocomplete="off" value="0"<?php echo empty($imagestyle['status']) ? ' checked' : ''; ?>><?php echo $this->text('Disabled'); ?>
            </label>
          </div>
          <div class="help-block">
            <?php echo $this->text('Disabled imagestyles will not process images'); ?>
          </div>
        </div>
      </div>
      <div class="form-group required<?php echo $this->error('name', ' has-error'); ?>">
        <label class="col-md-2 control-label"><?php echo $this->text('Name'); ?></label>
        <div class="col-md-10">
          <input name="imagestyle[name]" class="form-control" maxlength="32" value="<?php echo isset($imagestyle['name']) ? $this->e($imagestyle['name']) : ''; ?>">
          <div class="help-block">
            <?php echo $this->error('name'); ?>
            <div class="text-muted">
              <?php echo $this->text('Required. A descriptive name of the image style for administrators'); ?>
            </div>
          </div>
        </div>
      </div>
      <div class="form-group required<?php echo $this->error('actions', ' has-error'); ?>">
        <label class="col-md-2 control-label"><?php echo $this->text('Actions'); ?></label>
        <div class="col-md-10">
          <textarea name="imagestyle[actions]" rows="6" class="form-control" placeholder="<?php echo $this->text('Make thumbnail 50X50: thumbnail 50,50'); ?>"><?php echo $this->e($imagestyle['actions']); ?></textarea>
          <div class="help-block">
            <?php echo $this->error('actions'); ?>
            <div class="text-muted">
              <?php echo $this->text('Required. A list of actions to be applied from the top to bottom. One action per line. See the legend'); ?>
            </div>
          </div>
        </div>
      </div>
      <div class="form-group">
        <div class="col-md-10 col-md-offset-2">
          <div class="btn-toolbar">
            <?php if (isset($imagestyle['imagestyle_id']) && $this->access('image_style_delete')) { ?>
            <button class="btn btn-danger delete" name="delete" value="1" onclick="return confirm(GplCart.text('Are you sure?'));">
              <?php if (empty($imagestyle['default'])) { ?>
              <?php echo $this->text('Delete'); ?>
              <?php } else { ?>
              <?php echo $this->text('Reset'); ?>
              <?php } ?>
            </button>
            <?php } ?>
            <a class="btn btn-default cancel" href="<?php echo $this->url('admin/settings/imagestyle'); ?>">
              <?php echo $this->text('Cancel'); ?>
            </a>
            <?php if ($this->access('image_style_edit') || $this->access('image_style_add')) { ?>
            <button class="btn btn-default save" name="save" value="1">
              <i class="fa fa-floppy-o"></i> <?php echo $this->text('Save'); ?>
            </button>
            <?php } ?>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="panel panel-default">
        <div class="panel-heading"><?php echo $this->text('Legend'); ?></div>
        <div class="panel-body">
          <table class="table table-striped table-condensed">
            <thead>
              <tr>
                <td><?php echo $this->text('Key'); ?></td>
                <td><?php echo $this->text('Name'); ?></td>
                <td><?php echo $this->text('Description'); ?></td>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($action_handlers as $action_id => $handler) { ?>
              <tr>
                <td class="middle"><?php echo $this->e($action_id); ?></td>
                <td class="middle"><?php echo $this->text($handler['name']); ?></td>
                <td class="middle">
                  <?php if (isset($handler['description'])) { ?>
                  <?php echo $this->text($handler['description']); ?>
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