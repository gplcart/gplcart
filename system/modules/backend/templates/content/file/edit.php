<form method="post" enctype="multipart/form-data" onsubmit="return confirm();" id="edit-file" class="form-horizontal">
  <input type="hidden" name="token" value="<?php echo $token; ?>">
  <div class="row">
    <div class="col-md-6 col-md-offset-6 text-right">
      <div class="btn-toolbar">
        <?php if (isset($file['file_id']) && $this->access('file_delete')) {
    ?>
        <button class="btn btn-danger delete" name="delete" value="1">
          <i class="fa fa-trash"></i> <?php echo $this->text('Delete');
    ?>
        </button>
        <?php 
} ?>
        <a href="<?php echo $this->url('admin/content/file'); ?>" class="btn btn-default cancel"><i class="fa fa-reply"></i> <?php echo $this->text('Cancel'); ?></a>
        <?php if ($this->access('file_add') || $this->access('file_edit')) {
    ?>
        <button class="btn btn-primary save" name="save" value="1">
          <i class="fa fa-floppy-o"></i> <?php echo $this->text('Save');
    ?>
        </button>
        <?php 
} ?>
      </div>
    </div>
  </div>
  <div class="row margin-top-20">
    <div class="col-md-12">
      <?php if (isset($file['path'])) {
    ?>
      <div class="form-group">
        <label class="col-md-2 control-label">
        <?php echo $this->text('File');
    ?>
        </label>
        <div class="col-md-4">
          <?php if ($file['file_url']) {
    ?>
          <a class="btn btn-default btn-block" href="<?php echo $this->escape($file['file_url']);
    ?>" target="_blank">
            <?php echo $this->truncate($this->escape($file['path']));
    ?>
          </a>
          <?php 
} else {
    ?>
          <span class="text-danger"><?php echo $this->text('Missing');
    ?></span>
          <?php 
}
    ?>
        </div>
      </div>
      <?php if ($file['file_url']) {
    ?>
      <div class="form-group">
        <div class="col-md-4 col-md-offset-2">
          <div class="btn-group" data-toggle="buttons">
            <label class="btn btn-default">
              <input type="checkbox" name="delete_disk" value="<?php echo $file['path'];
    ?>"> <?php echo $this->text('Delete from disk');
    ?>
            </label>
          </div>
        </div>
      </div>
      <?php 
}
    ?>
      <?php 
} ?>
      <div class="form-group<?php echo isset($form_errors['file']) ? ' has-error' : ''; ?>">
        <div class="col-md-4 col-md-offset-2">
          <?php if ($this->access('file_upload')) {
    ?>
          <input class="form-control" type="file" name="file" accept="<?php echo $supported_extensions;
    ?>">
          <div class="help-block"><?php echo $this->text('Supported file extensions: %s', array('%s' => $supported_extensions));
    ?></div>
          <?php if (isset($form_errors['file'])) {
    ?>
          <div class="help-block"><?php echo $form_errors['file'];
    ?></div>
          <?php 
}
    ?>
          <?php 
} ?>
        </div>
      </div>
      <div class="required form-group<?php echo isset($form_errors['title']) ? ' has-error' : ''; ?>">
        <label class="col-md-2 control-label">
        <?php echo $this->text('Title'); ?>
        </label>
        <div class="col-md-4">
          <input maxlength="255" name="file[title]" class="form-control" value="<?php echo (isset($file['title'])) ? $this->escape($file['title']) : ''; ?>">
          <?php if (isset($form_errors['title'])) {
    ?>
          <div class="help-block"><?php echo $form_errors['title'];
    ?></div>
          <?php 
} ?>
        </div>
      </div>
      <div class="form-group<?php echo isset($form_errors['description']) ? ' has-error' : ''; ?>">
        <label class="col-md-2 control-label">
          <?php echo $this->text('Description'); ?>
        </label>
        <div class="col-md-4">
          <textarea name="file[description]" class="form-control"><?php echo isset($file['description']) ? $this->escape($file['description']) : ''; ?></textarea>
        </div>
      </div>
      <?php if ($languages) {
    ?>
      <div class="form-group">
        <div class="col-md-4 col-md-offset-2">
          <a data-toggle="collapse" href="#translations">
          <?php echo $this->text('Translations');
    ?> <span class="caret"></span>
          </a>
        </div>
      </div>
      <div id="translations" class="collapse translations<?php echo isset($form_errors) ? ' in' : '';
    ?>">
        <?php foreach ($languages as $code => $language) {
    ?>
        <div class="form-group<?php echo isset($form_errors['translation'][$code]['title']) ? ' has-error' : '';
    ?>">
          <label class="col-md-2 control-label"><?php echo $this->text('Title %language', array('%language' => $language['native_name']));
    ?></label>
          <div class="col-md-4">
            <input maxlength="255" name="file[translation][<?php echo $code;
    ?>][title]" class="form-control" value="<?php echo isset($file['translation'][$code]['title']) ? $this->escape($file['translation'][$code]['title']) : '';
    ?>">
            <?php if (isset($form_errors['translation'][$code]['title'])) {
    ?>
            <div class="help-block"><?php echo $form_errors['translation'][$code]['title'];
    ?></div>
            <?php 
}
    ?>
          </div>
        </div>
        <div class="form-group">
          <label class="col-md-2 control-label"><?php echo $this->text('Description %language', array('%language' => $language['native_name']));
    ?></label>
          <div class="col-md-4">
            <textarea class="form-control" name="file[translation][<?php echo $code;
    ?>][description]"><?php echo isset($file['translation'][$code]['description']) ? $this->escape($file['translation'][$code]['description']) : '';
    ?></textarea>
          </div>
        </div>
        <?php 
}
    ?>
      </div>
      <?php 
} ?>
    </div>
  </div>
</form>