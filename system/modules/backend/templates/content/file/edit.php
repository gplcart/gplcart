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
<form method="post" enctype="multipart/form-data" class="form-horizontal">
  <input type="hidden" name="token" value="<?php echo $_token; ?>">
  <div class="form-group<?php echo $this->error('title', ' has-error'); ?>">
    <label class="col-md-2 control-label"><?php echo $this->text('Title'); ?></label>
    <div class="col-md-4">
      <input maxlength="255" name="file[title]" class="form-control" value="<?php echo isset($file['title']) ? $this->e($file['title']) : ''; ?>" autofocus>
      <div class="help-block">
        <?php echo $this->error('title', ''); ?>
        <div class="text-muted"><?php echo $this->text('Short description of the file'); ?></div>
      </div>
    </div>
  </div>
  <div class="form-group<?php echo $this->error('description', ' has-error'); ?>">
    <label class="col-md-2 control-label"><?php echo $this->text('Description'); ?></label>
    <div class="col-md-4">
      <textarea name="file[description]" class="form-control"><?php echo isset($file['description']) ? $this->e($file['description']) : ''; ?></textarea>
      <div class="help-block">
        <?php echo $this->error('description'); ?>
        <div class="text-muted"><?php echo $this->text('Optional detailed description of the file'); ?></div>
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
        <input maxlength="255" name="file[translation][<?php echo $code; ?>][title]" class="form-control" value="<?php echo (isset($file['translation'][$code]['title'])) ? $this->e($file['translation'][$code]['title']) : ''; ?>">
        <div class="help-block"><?php echo $this->error("translation.$code.title"); ?></div>
      </div>
    </div>
    <div class="form-group<?php echo $this->error("translation.$code.description", ' has-error'); ?>">
      <label class="col-md-2 control-label"><?php echo $this->text('Description %language', array('%language' => $language['native_name'])); ?></label>
      <div class="col-md-4">
        <textarea name="file[translation][<?php echo $code; ?>][description]" class="form-control"><?php echo (isset($file['translation'][$code]['description'])) ? $this->e($file['translation'][$code]['description']) : ''; ?></textarea>
        <div class="help-block"><?php echo $this->error("translation.$code.description"); ?></div>
      </div>
    </div>
    <?php } ?>
  </div>
  <?php } ?>
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
      <div class="form-control">
        <a href="<?php echo $this->url('', array('download' => $file['file_id'])); ?>"><?php echo $this->truncate($this->e($file['path'])); ?></a>
      </div>
      <?php } ?>
    </div>
  </div>
  <div class="form-group<?php echo $this->error('weight', ' has-error'); ?>">
    <label class="col-md-2 control-label">
      <?php echo $this->text('Weight'); ?>
    </label>
    <div class="col-md-4">
      <input maxlength="2" name="file[weight]" class="form-control" value="<?php echo isset($file['weight']) ? $this->e($file['weight']) : 0; ?>">
      <div class="help-block">
        <?php echo $this->error('weight'); ?>
        <div class="text-muted">
        <?php echo $this->text('Items are sorted in lists by the weight value. Lower value means higher position'); ?>
        </div>
      </div>
    </div>
  </div>
  <div class="form-group">
    <div class="col-md-10 col-md-offset-2">
      <div class="btn-toolbar">
        <?php if ($can_delete) { ?>
        <button class="btn btn-danger delete" name="delete" value="1" onclick="return confirm(Gplcart.text('Are you sure? It cannot be undone!'));">
          <?php echo $this->text('Delete'); ?>
        </button>
        <?php } ?>
        <a href="<?php echo $this->url('admin/content/file'); ?>" class="btn btn-default cancel">
          <?php echo $this->text('Cancel'); ?>
        </a>
        <?php if ($this->access('file_add') || $this->access('file_edit')) { ?>
        <button class="btn btn-default save" name="save" value="1">
          <i class="fa fa-floppy-o"></i> <?php echo $this->text('Save'); ?>
        </button>
        <?php } ?>
      </div>
    </div>
  </div>
</form>