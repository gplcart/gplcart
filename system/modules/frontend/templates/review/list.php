<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<?php if (!empty($reviews)) { ?>
<div id="reviews" class="panel panel-default panel-borderless reviews">
  <div class="panel-heading">
    <h4 class="panel-title"><?php echo $this->text('Reviews'); ?></h4>
  </div>
  <div class="panel-body">
    <div class="row">
      <?php foreach ($reviews as $review) { ?>
      <div class="col-md-12">
        <div class="media">
          <div class="media-body">
            <div class="media-heading">
              <div class="rating"><?php echo $review['rating_formatted']; ?></div>
              <b class="name"><?php echo $this->e($review['name']); ?></b>
              <span class="text-muted small"><?php echo $this->date($review['created']); ?></span>
              <?php if ($review['user_id'] == $_uid && $this->config('review_editable', 1)) { ?>
              <a href="<?php echo $this->url("review/edit/{$product['product_id']}/{$review['review_id']}"); ?>">
                <?php echo $this->lower($this->text('Edit')); ?>
              </a>
              <?php } ?>
            </div>
            <p class="text"><?php echo $this->e($review['text']); ?></p>
          </div>
        </div>
      </div>
      <?php } ?>
    </div>
    <div class="row">
      <div class="col-md-6"><?php echo $pager; ?></div>
      <div class="col-md-6 text-right">
        <?php if ($this->config('review_editable', 1) && $_is_logged_in) { ?>
        <a class="pull-right" rel="nofollow" href="<?php echo $this->url('review/add/' . $product['product_id']); ?>">
          <?php echo $this->text('Add review'); ?>
        </a>
        <?php } ?>
      </div>
    </div>
  </div>
</div>
<?php } ?>