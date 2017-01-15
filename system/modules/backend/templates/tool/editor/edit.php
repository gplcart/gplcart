<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<form method="post" class="form-horizontal edit-theme-file">
  <input type="hidden" name="token" value="<?php echo $this->token(); ?>">
  <div class="panel panel-default">
    <div class="panel-body">
      <div class="form-group<?php echo $this->error('content', ' has-error'); ?>">
        <div class="col-md-12">
          <textarea name="editor[content]" data-codemirror="true" rows="<?php echo $lines; ?>" class="form-control"><?php echo isset($editor['content']) ? $this->escape($editor['content']) : ''; ?></textarea>
          <div class="help-block">
              <?php echo $this->error('content'); ?>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="panel panel-default">
    <div class="panel-body">
      <div class="row">
        <div class="col-md-12">
          <div class="btn-toolbar">
            <a class="btn btn-default" href="<?php echo $this->url("admin/tool/editor/{$module['id']}"); ?>">
              <i class="fa fa-reply"></i> <?php echo $this->text('Cancel'); ?>
            </a>
            <?php if ($can_save) { ?>
            <button class="btn btn-default" name="save" value="1" onclick="return confirm(GplCart.text('Do you want to save the changes? Do you have a backup?'));">
              <i class="fa fa-floppy-o"></i> <?php echo $this->text('Save'); ?>
            </button>
            <?php } ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</form>