<form method="post" id="edit-review" onsubmit="return confirm();" class="form-horizontal">
  <input type="hidden" name="token" value="<?php echo $token; ?>">
  <div class="row">
    <div class="col-md-8">
      <div class="panel panel-default">
        <div class="panel-heading"><?php echo $this->text('Relations'); ?></div>
        <div class="panel-body">
          <div class="form-group required<?php echo isset($this->errors['product']) ? ' has-error' : ''; ?>">
            <label class="col-md-2 control-label">
              <span class="hint" title="<?php echo $this->text('Autocomplete field. Select a product that is related to the review'); ?>">
                <?php echo $this->text('Product'); ?>
              </span>
            </label>
            <div class="col-md-10">
              <input name="review[product]" class="form-control" value="<?php echo isset($review['product']) ? $this->escape($review['product']) : ''; ?>">
              <?php if (isset($this->errors['product'])) { ?>
              <div class="help-block"><?php echo $this->errors['product']; ?></div>
              <?php } ?>
            </div>
          </div>
          <input type="hidden" name="review[product_id]" value="<?php echo isset($review['product_id']) ? $review['product_id'] : ''; ?>">
          <div class="form-group required<?php echo isset($this->errors['email']) ? ' has-error' : ''; ?>">
            <label class="col-md-2 control-label">
              <span class="hint" title="<?php echo $this->text('Reviewer\'s e-mail'); ?>">
                <?php echo $this->text('Email'); ?>
              </span>
            </label>
            <div class="col-md-10">
              <input name="review[email]" class="form-control" value="<?php echo isset($review['email']) ? $this->escape($review['email']) : ''; ?>">
              <?php if (isset($this->errors['email'])) { ?>
              <div class="help-block"><?php echo $this->errors['email']; ?></div>
              <?php } ?>
            </div>
          </div>
        </div>
      </div>
      <div class="panel panel-default">
        <div class="panel-heading"><?php echo $this->text('Description'); ?></div>
        <div class="panel-body">
          <div class="form-group required<?php echo isset($this->errors['text']) ? ' has-error' : ''; ?>">
            <label class="col-md-2 control-label">
              <span class="hint" title="<?php echo $this->text('Review text. HTML not allowed'); ?>">
                <?php echo $this->text('Text'); ?>
              </span>
            </label>
            <div class="col-md-10">
              <textarea name="review[text]" rows="3" class="form-control"><?php echo isset($review['text']) ? $this->escape($review['text']) : ''; ?></textarea>
              <?php if (isset($this->errors['text'])) { ?>
                <div class="help-block"><?php echo $this->errors['text']; ?></div>
              <?php } ?>
            </div>
          </div>    
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="panel panel-default">
        <div class="panel-body">    
          <div class="form-group">
            <label class="col-md-2 control-label">
              <span class="hint" title="<?php echo $this->text('Disabled reviews are hidden for customers'); ?>">
                <?php echo $this->text('Status'); ?>
              </span>
            </label>
            <div class="col-md-6">
              <div class="btn-group" data-toggle="buttons">
                <label class="btn btn-default<?php echo!empty($review['status']) ? ' active' : ''; ?>">
                  <input name="review[status]" type="radio" autocomplete="off" value="1"<?php echo!empty($product['status']) ? ' checked' : ''; ?>><?php echo $this->text('Enabled'); ?>
                </label>
                <label class="btn btn-default<?php echo empty($review['status']) ? ' active' : ''; ?>">
                  <input name="review[status]" type="radio" autocomplete="off" value="0"<?php echo empty($review['status']) ? ' checked' : ''; ?>><?php echo $this->text('Disabled'); ?>
                </label>
              </div>
            </div>
          </div>         
          <div class="form-group<?php echo isset($this->errors['created']) ? ' has-error' : ''; ?>">
            <label class="col-md-2 control-label">
              <span class="hint" title="<?php echo $this->text('Date when the review was created. Leave empty for the current date'); ?>">
                <?php echo $this->text('Created'); ?>
              </span>
            </label>
            <div class="col-md-10">
              <input name="review[created]" class="form-control" placeholder="<?php echo $this->text('Current time'); ?>" value="<?php echo empty($review['created']) ? '' : $this->date($review['created'], false); ?>">
              <?php if (isset($this->errors['created'])) { ?>
              <div class="help-block"><?php echo $this->errors['created']; ?></div>
              <?php } ?>
            </div>
          </div>
        </div>
      </div>
      <div class="panel panel-default">
        <div class="panel-body">
          <div class="btn-toolbar">
            <?php if (isset($review['review_id']) && $this->access('review_delete')) { ?>
            <button class="btn btn-danger delete" name="delete" value="1">
              <i class="fa fa-trash"></i> <?php echo $this->text('Delete'); ?>
            </button>
            <?php } ?>
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