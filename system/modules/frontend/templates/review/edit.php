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
  <?php if (!empty($product['images'])) { ?>
  <div class="col-md-3">
    <?php $first = reset($product['images']); ?>
    <img class="img-responsive" src="<?php echo $this->e($first['thumb']); ?>" alt="<?php echo $this->e($first['title']); ?>" title="<?php echo $this->e($first['title']); ?>">
  </div>
  <?php } ?>
  <div class="<?php echo empty($product['images']) ? 'col-md-12' : 'col-md-9'; ?>">
    <div class="price-wrapper h3">
      <?php if (isset($product['original_price']) && $product['original_price'] > $product['price']) { ?>
      <s id="original-price" class="small">
        <?php echo $this->e($product['original_price_formatted']); ?>
      </s>
      <?php } ?>
      <div id="price"><?php echo $this->e($product['price_formatted']); ?></div>
    </div>
    <?php if (!empty($product['description'])) { ?>
    <div class="description">
      <?php echo $this->truncate($this->teaser(strip_tags($product['description'])), 500); ?>
      <hr>
    </div>
    <?php } ?>
    <form method="post">
      <input type="hidden" name="token" value="<?php echo $_token; ?>">
      <div class="form-group">
        <lable><?php echo $this->text('Rating'); ?></lable>
        <div><?php echo $rating; ?></div>
      </div>
      <div class="form-group required<?php echo $this->error('text', ' has-error'); ?>">
        <lable><?php echo $this->text('Your review'); ?></lable>
        <textarea class="form-control" rows="4" name="review[text]"><?php echo isset($review['text']) ? $this->e($review['text']) : ''; ?></textarea>
        <div class="help-block"><?php echo $this->error('text'); ?></div>
      </div>
      <?php echo $_captcha; ?>
      <div class="btn-toolbar">
        <?php if ($can_delete) { ?>
        <button class="btn btn-danger" name="delete" value="1" onclick="return confirm('<?php echo $this->text('Are you sure?'); ?>');"><?php echo $this->text('Delete'); ?></button>
        <?php } ?>
        <a class="btn btn-default" href="<?php echo $this->url('product/' . $product['product_id']); ?>"><?php echo $this->text('Cancel'); ?></a>
        <button class="btn btn-default" name="save" value="1"><?php echo $this->text('Save'); ?></button>
      </div>
    </form>
  </div>
</div>
