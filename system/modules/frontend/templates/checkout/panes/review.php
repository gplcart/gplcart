<div class="panel panel-default">
  <div class="panel-heading"><?php echo $this->text('Review'); ?></div>
<div class="panel-body">
  <?php foreach ($cart['items'] as $sku => $item) { ?>
  <div class="form-group">
        <div class="col-md-2">
          <a target="_blank" href="<?php echo $this->url("product/{$item['product']['product_id']}"); ?>">
            <img class="img-responsive thumbnail" src="<?php echo $this->escape($item['thumb']); ?>">
          </a>
        </div>
        <div class="col-md-6">
          <a target="_blank" href="<?php echo $this->url("product/{$item['product']['product_id']}"); ?>">
            <?php echo $this->truncate($this->escape($item['product']['title']), 50); ?>
          </a><br>
          <span class="sku"><?php echo $this->text('SKU'); ?> : <?php echo $this->escape($item['sku']); ?></span><br>
          <span class="price"><?php echo $this->text('Price'); ?> : <?php echo $this->escape($item['price_formatted']); ?></span>
          <div>
            <div class="input-group input-group-sm">
              <span class="input-group-btn minus">
                <button class="btn btn-default" name="order[cart][action][minus]" value="<?php echo $sku; ?>">
                  <span class="fa fa-minus"></span>
                </button>
              </span>
              <input class="form-control text-center" type="number" min="1" step="1" maxlength="2" name="order[cart][items][<?php echo $sku; ?>][quantity]" value="<?php echo $item['quantity']; ?>">
              <span class="input-group-btn plus">
                <button class="btn btn-default" name="order[cart][action][plus]" value="<?php echo $sku; ?>">
                  <span class="fa fa-plus"></span>
                </button>
              </span>
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
  <?php } ?>
  <?php if (isset($form_messages['cart'])) { ?>
      <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
        <?php echo $form_messages['cart']; ?>
      </div>
  <?php } ?>
  <?php if (isset($this->errors['cart'])) { ?>
      <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
        <?php echo $this->errors['cart']; ?>
      </div>
  <?php } ?>
  <div class="form-group">
    <div class="col-md-12">
      <table class="table price-components">
        <tr class="active">
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
                
                <?php if(isset($price_component['rule']['type']) && $price_component['rule']['type'] === 'order' && !empty($price_component['rule']['code'])) { ?>
                <div class="form-group col-md-8<?php echo isset($this->errors['code']) ? ' has-error' : ''; ?>">
                <div class="input-group">
                <input class="form-control col-md-2" name="order[code]" placeholder="<?php echo $this->text('Enter your discount code'); ?>" value="<?php echo isset($order['code']) ? $order['code'] : ''; ?>">
                <span class="input-group-btn">
                <button class="btn btn-default" name="check_code" value="<?php echo $id; ?>"><?php echo $this->text('Check'); ?></button>
                </span>
                

                </div>
                
                <?php if(isset($this->errors['code'])) { ?>
                <div class="help-block"><?php echo $this->errors['code']; ?></div>
                <?php } ?>
                  
                </div>

                <?php } else { ?>
                <?php echo $price_component['formatted_price']; ?>
                <?php } ?>
                  
                  
              
              </td>
            </tr>
        <?php } ?>
        <tr class="active">
          <td><span class="h4"><?php echo $this->text('Grand total'); ?></span></td>
          <td>
            <input type="hidden" name="order[total]" value="<?php echo $total; ?>">
            <span class="h4"><?php echo $total_formatted; ?></span>
          </td>
        </tr>
      </table>
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