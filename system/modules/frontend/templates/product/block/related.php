<?php if (!empty($products)) {
    ?>
<div class="row">
  <div class="col-md-12">
    <h2 class="h3"><?php echo $this->text('Related');
    ?></h2>
    <div class="carousel slide block" data-ride="carousel" data-interval="false" id="related-carousel">
      <div class="carousel-inner">
        <?php foreach ($products as $index => $chunk) {
    ?>
        <div class="item<?php echo ($index == 0) ? ' active' : '';
    ?>">
          <div class="row">
            <?php foreach ($chunk as $product) {
    ?>
            <div class="col-md-2 col-sm-2 col-xs-2">
              <div class="thumbnail product">
                <a href="<?php echo $this->escape($product['url']);
    ?>">
                  <img src="<?php echo $this->escape($product['thumb']);
    ?>" title="<?php echo $this->escape($product['title']);
    ?>">
                </a>
                <div class="caption hidden-xs hidden-sm">
                  <h3 class="h5">
                    <a href="<?php echo $this->escape($product['url']);
    ?>"><?php echo $this->escape($product['title']);
    ?></a>
                  </h3>
                  <p><?php echo $this->escape($product['price_formatted']);
    ?></p>
                  <?php if (isset($form)) {
    ?>
                  <form method="post" class="form-horizontal" id="add-to-cart-<?php echo $product['product_id'];
    ?>">
                    <input type="hidden" name="token" value="<?php echo $this->token;
    ?>">
                    <div class="form-group">
                      <div class="col-md-12"> 
                        <button class="btn btn-success btn-block" name="to_cart" value="<?php echo $product['product_id'];
    ?>">
                          <?php echo $this->text('Add to cart');
    ?>
                        </button>
                      </div>
                    </div>
                  </form>
                  <?php 
}
    ?>
                </div>
              </div>
            </div>
            <?php 
}
    ?>
          </div>
        </div>
        <?php 
}
    ?>
      </div>
      <?php if (count($products) > 1) {
    ?>
      <a class="left carousel-control" href="#related-carousel" data-slide="prev">
        <span class="fa fa-2x fa-angle-left"></span>
      </a>
      <a class="right carousel-control" href="#related-carousel" data-slide="next">
        <span class="fa fa-2x fa-angle-right"></span>
      </a>
      <?php 
}
    ?>
    </div>
  </div>
</div>
<?php 
} ?>
