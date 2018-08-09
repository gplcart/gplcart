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
<?php if (!empty($reviews)) { ?>
<div id="reviews" class="card borderless reviews">
  <div class="card-header clearfix">
    <h4 class="card-title float-left"><?php echo $this->text('Reviews'); ?></h4>
    <?php if(!empty($pager)) { ?>
    <div class="float-right">
      <?php echo $pager; ?>
    </div>
    <?php } ?>
  </div>
  <div class="card-body">
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
    <?php if ($this->config('review_editable', 1) && $_is_logged_in) { ?>
    <div>
      <a rel="nofollow" href="<?php echo $this->url("review/add/{$product['product_id']}"); ?>">
        <?php echo $this->text('Add review'); ?>
      </a>
    </div>
    <?php } ?>
  </div>
</div>
<?php } ?>