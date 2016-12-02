<div class="row">
  <?php if (!empty($image)) { ?>
  <div class="col-md-1">
    <img class="img-responsive thumbnail" alt="<?php echo $this->escape($image['title']); ?>" title="<?php echo $this->escape($image['description']); ?>" src="<?php echo $this->escape($image['thumb']); ?>">
  </div>
  <?php } ?>
  <div class="col-md-11">
    <h4 class="title"><?php echo $this->escape($product['title']); ?></h4>
    <div class="h4 price"><?php echo $this->escape($product['price_formatted']); ?></div>
  </div>
</div>
<div class="row">
  <div class="col-md-12">
    <form method="post" class="form-horizontal" id="edit-review">
      <?php echo $honeypot; ?>
      <input type="hidden" name="token" value="<?php echo $this->token(); ?>">
      <div class="form-group">
        <label class="col-md-1"><?php echo $this->text('Rating'); ?></label>
        <div class="col-md-6"><?php echo $rating; ?></div>
      </div>
      <div class="form-group required<?php echo $this->error('text', ' has-error'); ?>">
        <label class="col-md-1"><?php echo $this->text('Review'); ?></label>
        <div class="col-md-6">
          <textarea class="form-control" rows="2" name="review[text]"><?php echo isset($review['text']) ? $review['text'] : ''; ?></textarea>
          <div class="help-block"><?php echo $this->error('text'); ?></div>
        </div>
      </div>
      <div class="form-group">
        <div class="col-md-10 col-md-offset-1">
          <?php if($can_delete) { ?>
          <button class="btn btn-danger" name="delete" value="1"><?php echo $this->text('Delete'); ?></button>
          <?php } ?>
          <a class="btn btn-default" href="<?php echo $this->url("product/{$product['product_id']}"); ?>">
            <i class="fa fa-reply"></i> <?php echo $this->text('Cancel'); ?>
          </a>
          <button class="btn btn-default" name="save" value="1"><?php echo $this->text('Save'); ?></button>
        </div>
      </div>
    </form>
  </div>
</div>