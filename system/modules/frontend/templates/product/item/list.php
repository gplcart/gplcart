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
<div class="col-md-12 list product item">
  <div class="panel">
    <div class="panel-body">
      <div class="row">
        <div class="col-md-2">
          <?php if (!empty($item['thumb'])) { ?>
          <a href="<?php echo empty($item['url']) ? $this->url("product/{$item['product_id']}") : $this->e($item['url']); ?>">
            <img class="img-responsive" title="<?php echo $this->e($item['title']); ?>" alt="<?php echo $this->e($item['title']); ?>" src="<?php echo $this->e($item['thumb']); ?>">
          </a>
          <?php } ?>
        </div>
        <div class="col-md-7">
          <div class="title">
            <a href="<?php echo empty($item['url']) ? $this->url("product/{$item['product_id']}") : $this->e($item['url']); ?>">
              <?php echo $this->e($item['title']); ?>
            </a>
          </div>
          <?php if(!empty($item['description'])) { ?>
          <div class="description">
            <p><?php echo $this->truncate($this->teaser(strip_tags($item['description'])), 500); ?></p>
          </div>
          <?php } ?>
          <?php if(!empty($item['bundled_products'])) { ?>
          <div class="bundle-title">
            <?php echo $this->text('+ @num bundled products!', array('@num' => count($item['bundled_products']))); ?>
          </div>
          <div class="bundle-items">
            <?php foreach($item['bundled_products'] as $bundle_item) { ?>
            <?php echo $bundle_item['rendered']; ?>
            <?php } ?>
          </div>
          <?php } ?>
        </div>
        <div class="col-md-3">
          <div class="row text-right">
            <div class="col-md-12">
              <p>
                <?php if (isset($item['original_price']) && $item['original_price'] > $item['price']) { ?>
                <s><?php echo $this->e($item['original_price_formatted']); ?></s>
                <?php } ?>
                <?php echo $this->e($item['price_formatted']); ?>
              </p>
            </div>
          </div>
          <?php if(!empty($buttons)) { ?>
            <div class="text-right">
              <form method="post" class="form-horizontal product-action">
                <input type="hidden" name="token" value="<?php echo $_token; ?>">
                <input type="hidden" name="product[product_id]" value="<?php echo $this->e($item['product_id']); ?>">
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
                <?php if (empty($item['in_wishlist'])) { ?>
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
                <?php if (empty($item['in_comparison'])) { ?>
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
              </form>
            </div>
          <?php } ?>
        </div>
      </div>
    </div>
  </div>
</div>

