<div class="panel panel-default">
  <div class="panel-heading"><?php echo $this->text('Review'); ?></div>
  <div class="panel-body">
    <?php foreach ($cart['items'] as $sku => $item) { ?>
    <div class="form-group<?php echo isset($this->errors['cart']['items'][$sku]) ? ' has-error' : ''; ?>">
      <div class="col-md-2">
        <a target="_blank" href="<?php echo $this->url("product/{$item['product']['product_id']}"); ?>">
          <img class="img-responsive thumbnail" src="<?php echo $this->escape($item['thumb']); ?>">
        </a>
      </div>
      <div class="col-md-6">
        <a target="_blank" href="<?php echo $this->url("product/{$item['product']['product_id']}"); ?>">
        <?php echo $this->truncate($this->escape($item['product']['title']), 50); ?>
        </a>
        <br>
        <span class="sku"><?php echo $this->text('SKU'); ?> : <?php echo $this->escape($item['sku']); ?></span><br>
        <span class="price"><?php echo $this->text('Price'); ?>
          : <?php echo $this->escape($item['price_formatted']); ?></span>
        <div>
          <div class="input-group input-group-sm">
            <input class="form-control text-center" maxlength="2" name="order[cart][items][<?php echo $sku; ?>][quantity]" value="<?php echo $item['quantity']; ?>">
          </div>
          <div class="input-group input-group-sm hidden-js">
            <button title="<?php echo $this->text('Update'); ?>" class="btn btn-default btn-sm" name="update" value="1">
              <i class="fa fa-refresh"></i>
            </button>
          </div>
        </div>
        <div><?php echo $this->text('Amount'); ?> : <?php echo $this->escape($item['total_formatted']); ?></div>
      </div>
      <div class="col-md-3">
        <div class="btn-toolbar">
          <button title="<?php echo $this->text('Delete'); ?>" class="btn btn-default btn-sm" name="order[cart][action][delete]" value="<?php echo $item['cart_id']; ?>">
            <i class="fa fa-trash"></i>
          </button>
          <button title="<?php echo $this->text('Move to wishlist'); ?>" class="btn btn-default btn-sm" name="order[cart][action][wishlist]" value="<?php echo $sku; ?>">
            <i class="fa fa-star"></i>
          </button>
        </div>
      </div>
    </div>
    <hr>
    <?php } ?>
    <?php if (!empty($messages['cart'])) { ?>
    <?php foreach ($messages['cart'] as $severity => $text) { ?>
    <div class="alert alert-<?php echo $severity; ?> alert-dismissible">
      <button type="button" class="close" data-dismiss="alert">
        <span aria-hidden="true">&times;</span>
      </button>
      <?php echo $text; ?>
    </div>
    <?php } ?>
    <?php } ?>
    <div class="form-group">
      <div class="col-md-12">
        <table class="table table-borderless price-components">
          <tr>
            <td><?php echo $this->text('Subtotal'); ?></td>
            <td><?php echo $cart['total_formatted']; ?></td>
          </tr>
          <?php foreach ($price_components as $id => $price_component) { ?>
          <tr>
            <td>
              <?php echo $price_component['name']; ?>
              <input type="hidden" name="order[data][components][<?php echo $id; ?>]" value="<?php echo $price_component['price']; ?>">
            </td>
            <td>
              <?php if (isset($price_component['rule']['code']) && $price_component['rule']['code'] !== '') { ?>
              <div class="form-group col-md-8<?php echo isset($this->errors['pricerule_code']) ? ' has-error' : ''; ?>">
                <div class="input-group">
                  <input class="form-control col-md-2" name="order[data][pricerule_code]" placeholder="<?php echo $this->text('Enter your discount code'); ?>" value="<?php echo isset($order['data']['pricerule_code']) ? $order['data']['pricerule_code'] : ''; ?>">
                  <span class="input-group-btn">
                    <button class="btn btn-default" name="check_pricerule" value="<?php echo $id; ?>"><?php echo $this->text('Check'); ?></button>
                  </span>
                </div>
              </div>
              <?php } else { ?>
              <?php echo $price_component['price_formatted']; ?>
              <?php } ?>
            </td>
          </tr>
          <?php } ?>
          <tr>
            <td><span class="h4"><?php echo $this->text('Grand total'); ?></span></td>
            <td>
              <input type="hidden" name="order[total]" value="<?php echo $total; ?>">
              <span class="h4"><?php echo $total_formatted; ?></span>
            </td>
          </tr>
        </table>
        <?php if (!empty($messages['components'])) { ?>
        <?php foreach ($messages['components'] as $severity => $text) { ?>
        <div class="alert alert-<?php echo $severity; ?> alert-dismissible">
          <button type="button" class="close" data-dismiss="alert">
            <span aria-hidden="true">&times;</span>
          </button>
          <?php echo $text; ?>
        </div>
        <?php } ?>
        <?php } ?>
      </div>
    </div>
    <div class="form-group">
      <div class="col-md-6 hidden-js">
        <button class="btn btn-default" name="update" value="1"><i class="fa fa-refresh"></i> <?php echo $this->text('Update'); ?></button>
      </div>
      <div class="col-md-6">
        <button class="btn btn-success" name="save" value="1"><?php echo $this->text('Place order now'); ?></button>
      </div>
    </div>
  </div>
</div>