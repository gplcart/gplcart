<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<div class="grid product item col-md-3 col-sm-4 col-xs-6">
  <div class="thumbnail">
    <?php if (!empty($product['thumb'])) { ?>
    <a href="<?php echo $this->e($product['url']); ?>">
      <img class="img-responsive thumbnail" title="<?php echo $this->e($product['title']); ?>" alt="<?php echo $this->e($product['title']); ?>" src="<?php echo $this->e($product['thumb']); ?>">
    </a>
    <?php } ?>
    <div class="caption text-center">
      <div class="title" data-equal-height="true">
        <a href="<?php echo $this->e($product['url']); ?>">
          <?php echo $this->e($this->truncate($product['title'], 50)); ?>
        </a>
      </div>
      <p>
        <?php if (isset($product['original_price']) && $product['original_price'] > $product['price']) { ?>
        <s><?php echo $this->e($product['original_price_formatted']); ?></s>
        <?php } ?>
        <?php echo $this->e($product['price_formatted']); ?>
      </p>
      <?php if (!empty($buttons)) { ?>
      <form method="post" class="form-horizontal product-action">
        <input type="hidden" name="token" value="<?php echo $this->prop('token'); ?>">
        <input type="hidden" name="product[product_id]" value="<?php echo $this->e($product['product_id']); ?>">
        <div class="row">
          <div class="col-md-12">
            <?php if (in_array('wishlist_remove', $buttons)) { ?>
            <button title="<?php echo $this->text('Remove'); ?>" class="btn btn-default" data-ajax="true" name="remove_from_wishlist" value="1">
              <i class="fa fa-trash"></i>
            </button>
            <?php } ?>
            <?php if (in_array('compare_remove', $buttons)) { ?>
            <button title="<?php echo $this->text('Remove'); ?>" class="btn btn-default" name="remove_from_compare" value="1">
              <i class="fa fa-trash"></i>
            </button>
            <?php } ?>
            <?php if (in_array('wishlist_add', $buttons)) { ?>
            <?php if (empty($product['in_wishlist'])) { ?>
            <button title="<?php echo $this->text('Add to wishlist'); ?>" class="btn btn-default" data-ajax="true" name="add_to_wishlist" value="1">
              <i class="fa fa-heart"></i>
            </button>
            <?php } else { ?>
            <a rel="nofollow" title="<?php echo $this->text('Already in wishlist'); ?>" href="<?php echo $this->url('wishlist'); ?>" class="btn btn-default active">
              <i class="fa fa-heart"></i>
            </a>
            <?php } ?>
            <?php } ?>
            <?php if (in_array('compare_add', $buttons)) { ?>
            <?php if (empty($product['in_comparison'])) { ?>
            <button title="<?php echo $this->text('Compare'); ?>" class="btn btn-default" data-ajax="true" name="add_to_compare" value="1">
              <i class="fa fa-balance-scale"></i>
            </button>
            <?php } else { ?>
            <a rel="nofollow" title="<?php echo $this->text('Already in comparison'); ?>" href="<?php echo $this->url('compare'); ?>" class="btn btn-default active">
              <i class="fa fa-balance-scale"></i>
            </a>
            <?php } ?>
            <?php } ?>
            <?php if (in_array('cart_add', $buttons)) { ?>
            <button title="<?php echo $this->text('Add to cart'); ?>" class="btn btn-success" data-ajax="true" name="add_to_cart" value="1">
              <i class="fa fa-shopping-cart"></i>
            </button>
            <?php } ?>
          </div>
        </div>
      </form>
      <?php } ?>
    </div>
  </div>
</div>

