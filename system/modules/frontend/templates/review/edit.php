<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 * @var $this \gplcart\core\controllers\frontend\Controller
 * To see available variables <?php print_r(get_defined_vars()); ?>
 */
?>
<div class="row">
  <div class="col-md-6">
    <form method="post" class="form-horizontal">
      <input type="hidden" name="token" value="<?php echo $_token; ?>">
      <div class="form-group">
        <div class="col-md-12"><?php echo $rating; ?></div>
      </div>
      <div class="form-group required<?php echo $this->error('text', ' has-error'); ?>">
        <div class="col-md-12">
          <textarea class="form-control" rows="10" name="review[text]"><?php echo isset($review['text']) ? $this->e($review['text']) : ''; ?></textarea>
          <div class="help-block"><?php echo $this->error('text'); ?></div>
        </div>
      </div>
      <?php echo $_captcha; ?>
      <div class="form-group">
        <div class="col-md-2">
          <?php if($can_delete) { ?>
          <button class="btn btn-danger" name="delete" value="1" onclick="return confirm(Gplcart.text('Are you sure?'));"><?php echo $this->text('Delete'); ?></button>
          <?php } ?>
        </div>
        <div class="col-md-10 text-right">
          <a class="btn btn-default" href="<?php echo $this->url('product/' . $product['product_id']); ?>"><?php echo $this->text('Cancel'); ?></a>
          <button class="btn btn-default" name="save" value="1"><?php echo $this->text('Save'); ?></button>
        </div>
      </div>
    </form>
  </div>
</div>