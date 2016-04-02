<div class="col-md-12 list product-item">
  <div class="panel">
    <div class="panel-body">
      <div class="row">
        <div class="col-md-2">
          <a href="<?php echo $this->escape($product['url']); ?>">
            <img class="img-responsive thumbnail" src="<?php echo $this->escape($product['thumb']); ?>" alt="<?php echo $this->escape($product['title']); ?>">
          </a>
        </div>
        <div class="col-md-7">
          <a href="<?php echo $this->escape($product['url']); ?>"><?php echo $this->escape($product['title']); ?></a>
          <?php if (!empty($product['description'])) {
    ?>
          <p><?php echo $this->truncate($this->summary($product['description'], true, array()));
    ?></p>
          <?php 
} ?>
        </div>
        <div class="col-md-3">
          <div class="row text-right">
            <div class="col-md-12">
              <p><?php echo $this->escape($product['price_formatted']); ?></p>
            </div>
          </div>
          <?php if (!empty($buttons)) {
    ?>
          <div class="row">
            <div class="col-md-12 text-right">
              <form action="<?php echo $this->url('action');
    ?>" class="form-horizontal" data-product-id="<?php echo $product['product_id'];
    ?>">
                <input type="hidden" name="token" value="<?php echo $this->token;
    ?>">
                <input type="hidden" name="redirect" value="<?php echo isset($redirect) ? $redirect : $this->uri;
    ?>">
                <input type="hidden" name="product_id" value="<?php echo $product['product_id'];
    ?>">
                <input name="url" class="collapse" value="">
                <?php if (in_array('wishlist_remove', $buttons)) {
    ?>
                <button title="<?php echo $this->text('Remove');
    ?>" class="btn btn-default" name="action" value="removeFromWishlist">
                <i class="fa fa-trash"></i>
                </button>
                <?php 
}
    ?>
                <?php if (in_array('compare_remove', $buttons)) {
    ?>
                <button title="<?php echo $this->text('Remove');
    ?>" class="btn btn-default" name="action" value="removeFromComparison">
                <i class="fa fa-trash"></i>
                </button>
                <?php 
}
    ?>
                <?php if (in_array('wishlist_add', $buttons)) {
    ?>
                <?php if (empty($product['in_wishlist'])) {
    ?>
                <button title="<?php echo $this->text('Add to wishlist');
    ?>" class="btn btn-default" name="action" value="addToWishlist">
                  <i class="fa fa-heart"></i>
                </button>
                <?php 
} else {
    ?>
                <a rel="nofollow" title="<?php echo $this->text('Already in wishlist');
    ?>" href="<?php echo $this->url('wishlist');
    ?>" class="btn btn-default active">
                  <i class="fa fa-heart"></i>
                </a>
                <?php 
}
    ?>
                <?php 
}
    ?>
                <?php if (in_array('compare_add', $buttons)) {
    ?>
                <?php if (empty($product['in_comparison'])) {
    ?>
                <button title="<?php echo $this->text('Compare');
    ?>" class="btn btn-default" name="action" value="addToCompare">
                  <i class="fa fa-balance-scale"></i>
                </button>
                <?php 
} else {
    ?>
                <a rel="nofollow" title="<?php echo $this->text('Already in comparison');
    ?>" href="<?php echo $this->url('compare');
    ?>" class="btn btn-default active">
                  <i class="fa fa-balance-scale"></i>
                </a>
                <?php 
}
    ?>
                <?php 
}
    ?>
                <?php if (in_array('cart_add', $buttons)) {
    ?>
                <button title="<?php echo $this->text('Add to cart');
    ?>" class="btn btn-default btn-success" name="action" value="addToCart">
                  <i class="fa fa-shopping-cart"></i>
                </button>
                <?php 
}
    ?>
              </form>
            </div>
          </div>
          <?php 
} ?>
        </div>
      </div>
    </div>
  </div>
</div>