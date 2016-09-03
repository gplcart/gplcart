<form method="post" id="edit-review" onsubmit="return confirm();" class="form-horizontal">
  <input type="hidden" name="token" value="<?php echo $token; ?>">
  <div class="panel panel-default">
    <div class="panel-body">
      <div class="form-group">
        <label class="col-md-2 control-label"><?php echo $this->text('Status'); ?></label>
        <div class="col-md-6">
          <div class="btn-group" data-toggle="buttons">
            <label class="btn btn-default<?php echo!empty($review['status']) ? ' active' : ''; ?>">
              <input name="review[status]" type="radio" autocomplete="off" value="1"<?php echo!empty($product['status']) ? ' checked' : ''; ?>><?php echo $this->text('Enabled'); ?>
            </label>
            <label class="btn btn-default<?php echo empty($review['status']) ? ' active' : ''; ?>">
              <input name="review[status]" type="radio" autocomplete="off" value="0"<?php echo empty($review['status']) ? ' checked' : ''; ?>><?php echo $this->text('Disabled'); ?>
            </label>
          </div>
          <div class="help-block">
          <?php echo $this->text('Disabled reviews will not be available for frontend users and search engines'); ?>
          </div>
        </div>
      </div>
      <div class="form-group required<?php echo isset($this->errors['created']) ? ' has-error' : ''; ?>">
        <label class="col-md-2 control-label"><?php echo $this->text('Created'); ?></label>
        <div class="col-md-4">
          <input name="review[created]" class="form-control" value="<?php echo empty($review['created']) ? $this->date(null, false) : $this->date($review['created'], false); ?>">
          <div class="help-block">
            <?php if (isset($this->errors['created'])) { ?>
            <?php echo $this->errors['created']; ?>
            <?php } ?>
            <div class="text-muted"><?php echo $this->text('A date when the review was created'); ?></div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="panel panel-default">
    <div class="panel-body">
      <div class="form-group required<?php echo isset($this->errors['product_id']) ? ' has-error' : ''; ?>">
        <label class="col-md-2 control-label"><?php echo $this->text('Product'); ?></label>
        <div class="col-md-6">
          <input name="review[product]" class="form-control" value="<?php echo isset($review['product']) ? $this->escape($review['product']) : ''; ?>">
          <div class="help-block">
            <?php if (isset($this->errors['product_id'])) { ?>
            <?php echo $this->errors['product_id']; ?>
            <?php } ?>
            <div class="text-muted"><?php echo $this->text('Required. Autocomplete field. Select a product that is related to this review'); ?></div>
          </div>
        </div>
      </div>
      <input type="hidden" name="review[product_id]" value="<?php echo isset($review['product_id']) ? $review['product_id'] : ''; ?>">
      <div class="form-group required<?php echo isset($this->errors['email']) ? ' has-error' : ''; ?>">
        <label class="col-md-2 control-label"><?php echo $this->text('Email'); ?></label>
        <div class="col-md-6">
          <input name="review[email]" class="form-control" value="<?php echo isset($review['email']) ? $this->escape($review['email']) : ''; ?>">
          <div class="help-block">
            <?php if (isset($this->errors['email'])) { ?>
            <?php echo $this->errors['email']; ?>
            <?php } ?>
            <div class="text-muted"><?php echo $this->text('Required. Autocomplete field. Reviewer\'s E-mail'); ?></div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="panel panel-default">
    <div class="panel-body">
      <div class="form-group required<?php echo isset($this->errors['text']) ? ' has-error' : ''; ?>">
        <label class="col-md-2 control-label"><?php echo $this->text('Text'); ?></label>
        <div class="col-md-6">
          <textarea name="review[text]" rows="4" class="form-control"><?php echo isset($review['text']) ? $this->escape($review['text']) : ''; ?></textarea>
          <div class="help-block">
          <?php if (isset($this->errors['text'])) { ?>
          <?php echo $this->errors['text']; ?>
          <?php } ?>
          <div class="text-muted"><?php echo $this->text('Required. A text of the review. HTML not allowed'); ?></div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="panel panel-default">
    <div class="panel-body">
      <div class="row">
        <div class="col-md-2">
          <?php if (isset($review['review_id']) && $this->access('review_delete')) { ?>
          <button class="btn btn-danger delete" name="delete" value="1">
            <i class="fa fa-trash"></i> <?php echo $this->text('Delete'); ?>
          </button>
          <?php } ?>
        </div>
        <div class="col-md-10">
          <div class="btn-toolbar">
            <a href="<?php echo $this->url('admin/content/review'); ?>" class="btn btn-default cancel">
              <i class="fa fa-reply"></i> <?php echo $this->text('Cancel'); ?>
            </a>
            <?php if ($this->access('review_edit') || $this->access('review_add')) { ?>
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