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
  <input type="hidden" data-autocomplete-target="product" name="review[product_id]" value="<?php echo isset($review['product_id']) ? $review['product_id'] : ''; ?>">
  <div class="form-group">
    <label class="col-md-2 control-label"><?php echo $this->text('Status'); ?></label>
    <div class="col-md-6">
      <div class="btn-group" data-toggle="buttons">
        <label class="btn btn-default<?php echo empty($review['status']) ? '' : ' active'; ?>">
          <input name="review[status]" type="radio" autocomplete="off" value="1"<?php echo empty($product['status']) ? '' : ' checked'; ?>><?php echo $this->text('Enabled'); ?>
        </label>
        <label class="btn btn-default<?php echo empty($review['status']) ? ' active' : ''; ?>">
          <input name="review[status]" type="radio" autocomplete="off" value="0"<?php echo empty($review['status']) ? ' checked' : ''; ?>><?php echo $this->text('Disabled'); ?>
        </label>
      </div>
      <div class="help-block">
        <?php echo $this->text('Disabled reviews will not be available to customers and search engines'); ?>
      </div>
    </div>
  </div>
  <div class="form-group<?php echo $this->error('created', ' has-error'); ?>">
    <label class="col-md-2 control-label"><?php echo $this->text('Created'); ?></label>
    <div class="col-md-4">
      <input data-datepicker="true" data-datepicker-settings='{}' name="review[created]" class="form-control" value="<?php echo empty($review['created']) ? $this->date(null, false) : $this->date($review['created'], false); ?>">
      <div class="help-block">
        <?php echo $this->error('created'); ?>
        <div class="text-muted"><?php echo $this->text('Date when the review was created'); ?></div>
      </div>
    </div>
  </div>
  <div class="form-group required<?php echo $this->error('email', ' has-error'); ?>">
    <label class="col-md-2 control-label"><?php echo $this->text('Email'); ?></label>
    <div class="col-md-4">
      <input name="review[email]" data-autocomplete-source="user" class="form-control" value="<?php echo isset($review['email']) ? $this->e($review['email']) : ''; ?>">
      <div class="help-block">
        <?php echo $this->error('email'); ?>
        <div class="text-muted"><?php echo $this->text('Autocomplete field. An E-mail of the person who will be author of the review'); ?></div>
      </div>
    </div>
  </div>
  <div class="form-group required<?php echo $this->error('product_id', ' has-error'); ?>">
    <label class="col-md-2 control-label"><?php echo $this->text('Product'); ?></label>
    <div class="col-md-4">
      <input name="review[product]" data-autocomplete-source="product" class="form-control" value="<?php echo isset($review['product']) ? $this->e($review['product']) : ''; ?>">
      <div class="help-block">
        <?php echo $this->error('product_id'); ?>
        <div class="text-muted"><?php echo $this->text('Autocomplete field. Select a product that is related to this review'); ?></div>
      </div>
    </div>
  </div>
  <div class="form-group required<?php echo $this->error('text', ' has-error'); ?>">
    <label class="col-md-2 control-label"><?php echo $this->text('Text'); ?></label>
    <div class="col-md-10">
      <textarea name="review[text]" rows="8" class="form-control"><?php echo isset($review['text']) ? $this->e($review['text']) : ''; ?></textarea>
      <div class="help-block">
        <?php echo $this->error('text'); ?>
        <div class="text-muted"><?php echo $this->text('Text of the review. HTML tags are not allowed'); ?></div>
      </div>
    </div>
  </div>
  <div class="form-group">
    <div class="col-md-10 col-md-offset-2">
      <div class="btn-toolbar">
        <?php if (isset($review['review_id']) && $this->access('review_delete')) { ?>
        <button class="btn btn-danger delete" name="delete" value="1" onclick="return confirm(Gplcart.text('Are you sure? It cannot be undone!'));">
          <?php echo $this->text('Delete'); ?>
        </button>
        <?php } ?>
        <a href="<?php echo $this->url('admin/content/review'); ?>" class="btn btn-default cancel">
          <?php echo $this->text('Cancel'); ?>
        </a>
        <?php if ($this->access('review_edit') || $this->access('review_add')) { ?>
        <button class="btn btn-default save" name="save" value="1">
          <i class="fa fa-floppy-o"></i> <?php echo $this->text('Save'); ?>
        </button>
        <?php } ?>
      </div>
    </div>
  </div>
</form>