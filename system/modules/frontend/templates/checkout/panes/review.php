<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<div class="panel panel-checkout panel-default review">
  <div class="panel-heading"><?php echo $this->text('Review'); ?></div>
  <div class="panel-body">
    <?php if (!empty($messages['cart'])) { ?>
    <?php foreach ($messages['cart'] as $severity => $text) { ?>
    <div class="alert alert-<?php echo $this->e($severity); ?> alert-dismissible">
      <button type="button" class="close" data-dismiss="alert">
        <span aria-hidden="true">&times;</span>
      </button>
      <?php echo $this->filter($text); ?>
    </div>
    <?php } ?>
    <?php } ?>
    <?php foreach ($cart['items'] as $sku => $item) { ?>
    <div class="form-group<?php echo $this->error("cart.items.$sku", ' has-error'); ?>">
      <div class="col-md-2">
        <a target="_blank" href="<?php echo $this->url("product/{$item['product']['product_id']}"); ?>">
          <img class="img-responsive thumbnail" src="<?php echo $this->e($item['thumb']); ?>">
        </a>
      </div>
      <div class="col-md-6">
        <a target="_blank" href="<?php echo $this->url("product/{$item['product']['product_id']}"); ?>">
          <?php echo $this->e($this->truncate($item['product']['title'], 50)); ?>
        </a>
        <br>
        <span class="sku"><?php echo $this->text('SKU'); ?> : <?php echo $this->e($item['sku']); ?></span><br>
        <span class="price">
          <?php echo $this->text('Price'); ?> :
          <?php if (isset($item['original_price']) && $item['original_price'] > $item['price']) { ?>
          <s><?php echo $this->e($item['original_price_formatted']); ?></s>
          <?php } ?>
          <?php echo $this->e($item['price_formatted']); ?>
        </span>
        <div>
          <div class="input-group">
            <span class="input-group-btn">
              <button type="button" class="btn btn-default hidden-non-js" data-spinner="-">
                <span class="fa fa-minus"></span>
              </button>
            </span>
            <input data-min="1" data-max="99" class="form-control text-center quantity" name="order[cart][items][<?php echo $this->e($sku); ?>][quantity]" value="<?php echo $this->e($item['quantity']); ?>">
            <span class="input-group-btn">
              <button type="button" class="btn btn-default hidden-non-js" data-spinner="+">
                <span class="fa fa-plus"></span>
              </button>
            </span>
            <span class="input-group-btn hidden-js">
              <button title="<?php echo $this->text('Update'); ?>" class="btn btn-default" name="update" value="1">
                <i class="fa fa-refresh"></i>
              </button>
            </span>
          </div>
        </div>
        <div><?php echo $this->text('Amount'); ?> : <?php echo $this->e($item['total_formatted']); ?></div>
      </div>
      <div class="col-md-3">
        <div class="btn-toolbar">
          <button title="<?php echo $this->text('Delete'); ?>" class="btn btn-default" name="order[cart][action][delete]" value="<?php echo $this->e($item['cart_id']); ?>">
            <i class="fa fa-trash"></i>
          </button>
          <?php if (!$admin) { ?>
          <button title="<?php echo $this->text('Move to wishlist'); ?>" class="btn btn-default" name="order[cart][action][wishlist]" value="<?php echo $this->e($sku); ?>">
            <i class="fa fa-star"></i>
          </button>
          <?php } ?>
        </div>
      </div>
    </div>
    <?php } ?>
    <div class="form-group">
      <div class="col-md-12">
        <table class="table table-borderless price-components">
          <?php if (!empty($price_components)) { ?>
          <tr>
            <td><?php echo $this->text('Subtotal'); ?></td>
            <td><?php echo $this->e($cart['total_formatted']); ?></td>
          </tr>
          <?php } ?>
          <?php foreach ($price_components as $id => $price_component) { ?>
          <tr>
            <td><?php echo $this->e($price_component['name']); ?></td>
            <td>
              <?php if (isset($price_component['rule']['code']) && $price_component['rule']['code'] !== '') { ?>
              <div class="form-group col-md-8<?php echo $this->error('check_pricerule', ' has-error'); ?>">
                <div class="input-group">
                  <input class="form-control col-md-2" name="order[data][pricerule_code]" placeholder="<?php echo $this->text('Enter code'); ?>" value="<?php echo isset($order['data']['pricerule_code']) ? $this->e($order['data']['pricerule_code']) : ''; ?>">
                  <span class="input-group-btn">
                    <button class="btn btn-default" data-block-if-empty="order[data][pricerule_code]" name="check_pricerule" value="<?php echo $this->e($id); ?>"><?php echo $this->text('Apply'); ?></button>
                  </span>
                </div>
                <?php if ($price_component['price'] != 0) { ?>
                <?php echo $this->e($price_component['price_formatted']); ?>
                <?php } ?>
              </div>
              <?php } else { ?>
              <?php if ($admin) { ?>
              <input class="form-control" name="order[data][components][<?php echo $this->e($id); ?>][price]" value="<?php echo $this->e($price_component['price_decimal']); ?>">
              <?php } else { ?>
              <?php echo $this->e($price_component['price_formatted']); ?>
              <input type="hidden" name="order[data][components][<?php echo $this->e($id); ?>][price]" value="<?php echo $this->e($price_component['price']); ?>">
              <?php } ?>
              <?php } ?>
              <?php echo $this->error("data.components.$id"); ?>
            </td>
          </tr>
          <?php } ?>
          <tr>
            <td><b><?php echo $this->text('Grand total'); ?></b></td>
            <td>
              <?php if ($admin) { ?>
              <input class="form-control" name="order[total]" value="<?php echo $this->e($total_decimal); ?>">
              <?php } else { ?>
              <input type="hidden" name="order[total]" value="<?php echo $this->e($total); ?>">
              <b><?php echo $this->e($total_formatted); ?></b>
              <?php } ?>
              <?php echo $this->error('total'); ?>
            </td>
          </tr>
        </table>
      </div>
    </div>
    <?php if (!empty($messages['components'])) { ?>
    <?php foreach ($messages['components'] as $severity => $text) { ?>
    <div class="alert alert-<?php echo $this->e($severity); ?> alert-dismissible">
      <button type="button" class="close" data-dismiss="alert">
        <span aria-hidden="true">&times;</span>
      </button>
      <?php echo $this->filter($text); ?>
    </div>
    <?php } ?>
    <?php } ?>
    <div class="form-group">
      <div class="col-md-12">
        <a href="#" onclick="return false;" data-toggle="collapse" data-target="#order-comments"><?php echo $this->text('Order comments'); ?> <span class="caret"></span></a>
        <textarea name="order[comment]" id="order-comments" class="form-control<?php echo empty($order['comment']) ? ' collapse' : ''; ?>"><?php echo $this->e($order['comment']); ?></textarea>
      </div>
    </div>
    <div class="form-group">
      <div class="col-md-6">
        <button class="btn btn-success" name="save" value="1">
          <?php echo $admin ? $this->text('Save') : $this->text('Place order now'); ?>
        </button>
      </div>
    </div>
  </div>
</div>