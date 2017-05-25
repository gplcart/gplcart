<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<form method="post" enctype="multipart/form-data" id="edit-file" class="form-horizontal">
  <input type="hidden" name="token" value="<?php echo $this->prop('token'); ?>">
  <div class="panel panel-default">
    <div class="panel-body">
      <div class="form-group<?php echo $this->error('title', ' has-error'); ?>">
        <label class="col-md-2 control-label"><?php echo $this->text('Title'); ?></label>
        <div class="col-md-4">
          <input maxlength="255" name="file[title]" class="form-control" value="<?php echo (isset($file['title'])) ? $this->escape($file['title']) : ''; ?>" autofocus>
          <div class="help-block">
            <?php echo $this->error('title', ''); ?>
            <div class="text-muted"><?php echo $this->text('A short description of the file'); ?></div>
          </div>
        </div>
      </div>
      <div class="form-group<?php echo $this->error('description', ' has-error'); ?>">
        <label class="col-md-2 control-label"><?php echo $this->text('Description'); ?></label>
        <div class="col-md-4">
          <textarea name="file[description]" class="form-control"><?php echo (isset($file['description'])) ? $this->escape($file['description']) : ''; ?></textarea>
          <div class="help-block">
            <?php echo $this->error('description'); ?>
            <div class="text-muted"><?php echo $this->text('An optional detailed description of the file'); ?></div>
          </div>
        </div>
      </div>
      <?php if (!empty($_languages)) { ?>
      <div class="form-group">
        <div class="col-md-10 col-md-offset-2">
          <a data-toggle="collapse" href="#translations">
            <?php echo $this->text('Translations'); ?> <span class="caret"></span>
          </a>
        </div>
      </div>
      <div id="translations" class="collapse translations<?php echo $this->error(null, ' in'); ?>">
        <?php foreach ($_languages as $code => $language) { ?>
        <div class="form-group<?php echo $this->error("translation.$code.title", ' has-error'); ?>">
          <label class="col-md-2 control-label"><?php echo $this->text('Title %language', array('%language' => $language['native_name'])); ?></label>
          <div class="col-md-4">
            <input maxlength="255" name="file[translation][<?php echo $code; ?>][title]" class="form-control" value="<?php echo (isset($file['translation'][$code]['title'])) ? $this->escape($file['translation'][$code]['title']) : ''; ?>">
            <div class="help-block"><?php echo $this->error("translation.$code.title"); ?></div>
          </div>
        </div>
        <div class="form-group<?php echo $this->error("translation.$code.description", ' has-error'); ?>">
          <label class="col-md-2 control-label"><?php echo $this->text('Description %language', array('%language' => $language['native_name'])); ?></label>
          <div class="col-md-4">
            <textarea name="file[translation][<?php echo $code; ?>][description]" class="form-control"><?php echo (isset($file['translation'][$code]['description'])) ? $this->escape($file['translation'][$code]['description']) : ''; ?></textarea>
            <div class="help-block"><?php echo $this->error("translation.$code.description"); ?></div>
          </div>
        </div>
        <?php } ?>
      </div>
      <?php } ?>
    </div>
  </div>
  <div class="panel panel-default">
    <div class="panel-body">
      <div class="form-group<?php echo empty($file['file_id']) ? ' required' : ''; ?><?php echo $this->error('file', ' has-error'); ?>">
        <label class="col-md-2 control-label">
          <?php echo $this->text('File'); ?>
        </label>
        <div class="col-md-4">
          <?php if (empty($file['file_id'])) { ?>
          <input type="file" name="file" class="form-control">
          <div class="help-block">
            <?php echo $this->error('file'); ?>
            <div class="text-muted"><?php echo $this->text('Supported extensions: %list', array('%list' => implode(',', $extensions))); ?></div>
          </div>
          <?php } else { ?>
          <a href="<?php echo $this->url('', array('download' => $file['file_id'])); ?>"><?php echo $this->truncate($this->escape($file['path'])); ?></a>
          <?php } ?>
        </div>
      </div>
      <div class="form-group<?php echo $this->error('weight', ' has-error'); ?>">
        <label class="col-md-2 control-label">
          <?php echo $this->text('Weight'); ?>
        </label>
        <div class="col-md-3">
          <input maxlength="2" name="file[weight]" class="form-control" value="<?php echo (isset($file['weight'])) ? $this->escape($file['weight']) : 0; ?>">
          <div class="help-block">
            <?php echo $this->error('weight'); ?>
            <div class="text-muted">
            <?php echo $this->text('Files are sorted in lists by the weight value. Lower value means higher position'); ?>
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
          <?php if ($can_delete) { ?>
          <button class="btn btn-danger delete" name="delete" value="1" onclick="return confirm(GplCart.text('Delete? It cannot be undone!'));">
            <i class="fa fa-trash"></i> <?php echo $this->text('Delete'); ?>
          </button>
          <?php } ?>
        </div>
        <div class="col-md-4">
          <div class="btn-toolbar">
            <a href="<?php echo $this->url('admin/content/file'); ?>" class="btn btn-default cancel">
              <i class="fa fa-reply"></i> <?php echo $this->text('Cancel'); ?>
            </a>
            <?php if ($this->access('file_add') || $this->access('file_edit')) { ?>
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