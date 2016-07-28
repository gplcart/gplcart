<div class="row">
  <?php if (!empty($image)) { ?>
  <div class="col-md-1">
    <img class="img-responsive thumbnail" alt="<?php echo $this->escape($image['title']); ?>" title="<?php echo $this->escape($image['description']); ?>" src="<?php echo $this->escape($image['thumb']); ?>">
  </div>
  <?php } ?>
  <div class="col-md-11">
    <h4 class="title"><?php echo $this->escape($product['title']); ?></h4>
    <div class="h4 price"><?php echo $price; ?></div>
  </div>
</div>
<div class="row">
  <div class="col-md-12">
    <form method="post" class="form-horizontal" id="edit-review">
      <input type="hidden" name="token" value="<?php echo $this->token; ?>">
      <div class="form-group">
        <label class="col-md-1"><?php echo $this->text('Rating'); ?></label>
        <div class="col-md-6"><?php echo $rating; ?></div>
      </div>
      <div class="form-group required<?php echo $this->error('text', ' has-error'); ?>">
        <label class="col-md-1"><?php echo $this->text('Review'); ?></label>
        <div class="col-md-6">
          <textarea class="form-control" rows="2" name="review[text]" maxlength="<?php echo $max_length; ?>" placeholder="<?php echo $this->text('Maximum @num characters', array('@num' => $max_length)); ?>"><?php echo isset($review['text']) ? $review['text'] : ''; ?></textarea>
          <?php if ($this->error('text', true)) { ?>
          <div class="help-block"><?php echo $this->error('text'); ?></div>
          <?php } ?>
        </div>
      </div>
      <div class="form-group">
        <div class="col-md-10 col-md-offset-1">
          <?php if(isset($review['review_id']) && $deletable) { ?>
          <button class="btn btn-danger" name="delete" value="1"><?php echo $this->text('Delete'); ?></button>
          <?php } ?>
          <a class="btn btn-default" href="<?php echo $this->url("product/{$product['product_id']}"); ?>">
            <i class="fa fa-reply"></i> <?php echo $this->text('Cancel'); ?>
          </a>
          <button class="btn btn-default" name="save" value="1"><?php echo $this->text('Save'); ?></button>
        </div>
      </div>
      <input name="url" style="position:absolute;top:-999px;" value="">
    </form>      
  </div>
</div>