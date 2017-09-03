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
<div class="row section product-main">
  <?php if(!empty($images)) { ?>
  <div class="col-md-5 left">
    <div class="row section images">
      <div class="col-md-12"><?php echo $images; ?></div>
    </div>
  </div>
  <?php } ?>
  <div class="col-md-7 right">
    <div class="product-rating">
      <?php echo $rating; ?>
      <?php if (!empty($product['total_reviews'])) { ?>
      <a href="#reviews"><?php echo $this->text('@num reviews', array('@num' => $product['total_reviews'])); ?></a>
      <?php } ?>
      <?php if ($this->config('review_editable', 1) && $_is_logged_in) { ?>
      <?php if (empty($product['total_reviews'])) { ?>
      <a rel="nofollow" href="<?php echo $this->url('review/add/' . $product['product_id']); ?>">
        <?php echo $this->text('Be first to review this product'); ?>
      </a>
      <?php } else { ?>
      | <a rel="nofollow" href="<?php echo $this->url('review/add/' . $product['product_id']); ?>">
          <?php echo $this->lower($this->text('Add review')); ?>
      </a>
      <?php } ?>
      <?php } ?>
    </div>
    <h1 class="h4"><?php echo $this->e($product['title']); ?></h1>
    <?php echo $this->text('SKU'); ?>: <span id="sku" class="small"><?php echo $this->e($product['selected_combination']['sku']); ?></span>
    <s id="original-price">
      <?php if (isset($product['selected_combination']['original_price']) && $product['selected_combination']['original_price'] > $product['selected_combination']['price']) { ?>
      <?php echo $this->e($product['selected_combination']['original_price_formatted']); ?>
      <?php } ?>
    </s>
    <div id="price" class="h3"><?php echo $this->e($product['selected_combination']['price_formatted']); ?></div>
    <?php echo $cart_form; ?>
    <?php if (!empty($summary)) { ?>
    <div class="summary">
      <hr>
      <?php echo $this->e($this->truncate($summary, 500)); ?>
    </div>
    <?php } ?>
  </div>
</div>
<?php if(!empty($description)) { ?>
<?php echo $description; ?>
<?php } ?>
<?php if(!empty($attributes)) { ?>
<?php echo $attributes; ?>
<?php } ?>
<?php if(!empty($reviews)) { ?>
<?php echo $reviews; ?>
<?php } ?>
<?php if(!empty($related)) { ?>
<?php echo $related; ?>
<?php } ?>
<?php if(!empty($recent)) { ?>
<?php echo $recent; ?>
<?php } ?>