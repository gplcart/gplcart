<form method="post" id="edit-order" class="form-horizontal<?php echo !empty($this->errors) ? ' form-errors' : ''; ?>">
  <input type="hidden" name="token" value="<?php echo $token; ?>">
  <?php if (!empty($cart['items'])) { ?>
  <input type="hidden" name="order[store_id]" value="<?php echo $store_id; ?>">
  <div class="row">
    <div class="col-md-12">
      <?php if ($register_form) { ?>
      <div class="form-group">
        <div class="col-md-12">
          <div class="panel panel-default">
            <div class="panel-body">
              <?php if (isset($this->errors['register'])) { ?>
              <div class="alert alert-danger alert-dismissible clearfix">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
                <?php echo implode('<br>', $this->errors['register']); ?>
              </div>
              <?php } ?>
              <div class="form-inline clearfix">
                <div class="form-group required col-md-4<?php echo isset($this->errors['register']['name']) ? ' has-error' : ''; ?>">
                  <label class="col-md-4 control-label"><?php echo $this->text('Name'); ?></label>
                  <div class="col-md-8">
                    <input maxlength="255" class="form-control" name="user[name]" value="<?php echo isset($user['name']) ? $user['name'] : ''; ?>" autofocus>
                  </div>
                </div>
                <div class="form-group required col-md-4<?php echo isset($this->errors['register']['email']) ? ' has-error' : ''; ?>">
                  <label class="col-md-4 control-label"><?php echo $this->text('Email'); ?></label>
                  <div class="col-md-8">
                    <input type="email" maxlength="255" class="form-control" name="user[email]" value="<?php echo isset($user['email']) ? $user['email'] : ''; ?>">
                  </div>
                </div>
                <div class="form-group col-md-4">
                  <div class="col-md-6">
                    <button class="btn btn-default" name="register" value="1"><?php echo $this->text('Register'); ?></button>
                  </div>
                  <div class="col-md-3">
                    <div class="btn-group" data-toggle="buttons">
                      <label title="<?php echo $this->text('Notify registered user'); ?>" class="btn btn-default">
                        <input type="checkbox" name="notify_registered" value="1" autocomplete="off"><i class="fa fa-envelope-o"></i>
                      </label>
                    </div>
                  </div>
                  <div class="col-md-3 text-right">
                    <button title="<?php echo $this->text('Select existing user'); ?>" class="btn btn-default" name="cancel_register_form" value="1">
                      <i class="fa fa-times"></i>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <?php } else { ?>
      <div class="required form-group<?php echo isset($this->errors['user']) ? ' has-error' : ''; ?>">
        <div class="col-md-6">
          <div class="input-group user">
            <input type="hidden" name="order[user_id]" value="<?php echo (isset($order['user_id'])) ? $order['user_id'] : ''; ?>">
            <input name="order[user]" class="form-control" value="<?php echo (!empty($order['user'])) ? $this->escape($order['user']) : ''; ?>" placeholder="<?php echo $this->text('Customer'); ?>">
            <span class="input-group-btn">
              <?php if ($this->access('user_add')) { ?>
              <button class="btn btn-default btn-block" name="show_register_form" value="1">
                <i class="fa fa-user-plus"></i> <?php echo $this->text('Register'); ?>
              </button>
              <?php } ?>
            </span>
          </div>
        </div>
      </div>
      <?php } ?>
    </div>
  </div>
  <div class="row">
    <div class="col-md-4">
      <h3><?php echo $this->text('Shipping address'); ?></h3>
      <?php if ($addresses && !$address_form) { ?>
      <div class="form-group">
        <div class="col-md-12">
          <div class="btn-group margin-top-20 saved-addresses" data-toggle="buttons">
            <?php foreach ($addresses as $address_id => $address) { ?>
            <label class="btn btn-default<?php echo (isset($order['shipping_address']) && $order['shipping_address'] == $address_id) ? ' active' : ''; ?>">
              <?php foreach ($address['fields'] as $key => $value) { ?>
              <span class="clearfix">
                <span class="pull-left"><?php echo $this->text($key); ?> : <?php echo $this->escape($value); ?></span>
              </span>
              <?php } ?>
              <input type="radio" name="order[shipping_address]" value="<?php echo $address_id; ?>" autocomplete="off"<?php echo (isset($order['shipping_address']) && $order['shipping_address'] == $address_id) ? ' checked' : ''; ?>>
            </label>
            <?php } ?>
          </div>
        </div>
      </div>
      <div class="form-group">
        <div class="col-md-12">
          <button class="btn btn-default" name="add_address" value="1"><i class="fa fa-plus"></i> <?php echo $this->text('Add address'); ?></button>
        </div>
      </div>
      <?php } else { ?>
      <div class="form-group">
        <div class="col-md-12">
          <table class="table table-borderless table-striped table-condensed margin-top-20 shipping-address">
            <tr>
              <td class="middle"><?php echo $this->text('Country'); ?></td>
              <td>
                <div class="btn-toolbar">
                  <div class="btn-group country<?php echo isset($this->errors['address']['country']) ? ' has-error' : ''; ?>">
                    <select class="form-control" name="address[country]">
                      <?php foreach ($countries as $code => $name) { ?>
                      <option value="<?php echo $this->escape($code); ?>"<?php echo ($country_code == $code) ? ' selected' : ''; ?>>
                      <?php echo $this->escape($name); ?>
                      </option>
                      <?php } ?>
                    </select>
                    <?php if (isset($this->errors['address']['country'])) { ?>
                    <div class="help-block"><?php echo $this->errors['address']['country']; ?></div>
                    <?php } ?>
                  </div>
                </div>
              </td>
            </tr>
            <?php foreach ($format as $key => $data) { ?>
            <?php if ($key != 'country') { ?>
            <tr>
              <td class="middle<?php echo!empty($data['required']) ? ' required' : ''; ?>">
              <?php echo $this->text($key); ?>
              </td>
              <td>
                <div class="<?php echo $key; ?><?php echo isset($this->errors['address'][$key]) ? ' has-error' : ''; ?>">
                  <?php if ($key == 'state_id') { ?>
                  <select class="form-control" name="address[state_id]">
                    <?php foreach ($states as $state_id => $state) { ?>
                    <option value="<?php echo $state_id; ?>"<?php echo (isset($address['state_id']) && $address['state_id'] == $state_id) ? ' selected' : ''; ?>>
                    <?php echo $state['name']; ?>
                    </option>
                    <?php } ?>
                  </select>
                  <?php } else { ?>
                  <input name="address[<?php echo $key; ?>]" maxlength="255" class="form-control" value="<?php echo isset($address[$key]) ? $this->escape($address[$key]) : ''; ?>">
                  <?php } ?>
                  <?php if (isset($this->errors['address'][$key])) { ?>
                  <div class="help-block"><?php echo $this->errors['address'][$key]; ?></div>
                  <?php } ?>
                </div>
              </td>
            </tr>
            <?php } ?>
            <?php } ?>
          </table>
        </div>
      </div>
      <?php if ($addresses) { ?>
      <div class="form-group">
        <div class="col-md-12">
          <button class="btn btn-default" name="cancel_address_form" value="1">
            <i class="fa fa-reply"></i> <?php echo $this->text('Select saved address'); ?>
          </button>
        </div>
      </div>
      <?php } ?>
      <?php } ?>
      <?php if (isset($this->errors['address']) && !is_array($this->errors['address'])) { ?>
      <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
        <?php echo $this->errors['address']; ?>
      </div>
      <?php } ?>
    </div>
    <?php if ($shipping_services || $payment_services) { ?>
    <div class="col-md-3">
      <div class="form-group">
        <div class="col-md-12">
          <?php if ($shipping_services) { ?>
          <h3><?php echo $this->text('Shipping'); ?></h3>
          <?php foreach ($shipping_services as $service_id => $service) { ?>
          <?php if (isset($service['html'])) { ?>
          <?php echo $service['html']; ?>
          <?php } else { ?>
          <div class="radio">
          <?php if (!empty($service['image'])) { ?>
            <div class="image">
              <img class="img-responsive" src="<?php echo $this->escape($service['image']); ?>">
            </div>
          <?php } ?>
            <label>
              <input type="radio" name="order[shipping]" value="<?php echo $service_id; ?>"<?php echo (isset($order['shipping']) && $order['shipping'] == $service_id) ? ' checked' : ''; ?>>
              <?php echo $this->escape($service['name']); ?>
              <?php if (!empty($service['price'])) { ?>
                  <strong><?php echo $this->escape($service['price_formatted']); ?></strong>
              <?php } ?>
            </label>
          </div>
          <?php } ?>
          <?php } ?>
          <?php if (isset($this->errors['shipping']) && !is_array($this->errors['shipping'])) { ?>
          <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
            <?php echo $this->errors['shipping']; ?>
          </div>
          <?php } ?>
          <?php } ?>
          <?php if ($payment_services) { ?>
          <div class="form-group">
            <div class="col-md-12">
              <h3><?php echo $this->text('Payment'); ?></h3>
              <?php foreach ($payment_services as $service_id => $service) { ?>
              <?php if (isset($service['html'])) { ?>
              <?php echo $service['html']; ?>
              <?php } else { ?>
              <div class="radio">
                <?php if (!empty($service['image'])) { ?>
                <div class="image">
                  <img class="img-responsive" src="<?php echo $this->escape($service['image']); ?>">
                </div>
                <?php } ?>
                <label>
                  <input type="radio" name="order[payment]" value="<?php echo $service_id; ?>"<?php echo (isset($order['payment']) && $order['payment'] == $service_id) ? ' checked' : ''; ?>>
                  <?php echo $this->escape($service['name']); ?>
                  <?php if (!empty($service['price'])) { ?>
                  <strong><?php echo $this->escape($service['price_formatted']); ?></strong>
                  <?php } ?>
                </label>
              </div>
              <?php } ?>
              <?php } ?>
              <?php if (isset($this->errors['payment']) && !is_array($this->errors['payment'])) { ?>
              <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
                <?php echo $this->errors['payment']; ?>
              </div>
              <?php } ?>
            </div>
          </div>
          <?php } ?>
        </div>
      </div>
    </div>
    <?php } ?>
    <div class="col-md-5">
      <h3><?php echo $this->text('Order'); ?></h3>
      <div class="panel panel-default">
        <div class="panel-body">
          <?php foreach ($cart['items'] as $cart_id => $item) { ?>
          <div class="row">
            <div class="col-md-3">
              <a target="_blank" href="<?php echo $this->url("product/{$item['product']['product_id']}"); ?>">
                <img class="img-responsive" src="<?php echo $this->escape($item['thumb']); ?>">
              </a>
            </div>
            <div class="col-md-6">
              <h4>
                <a target="_blank" href="<?php echo $this->url("product/{$item['product']['product_id']}"); ?>">
                <?php echo $this->escape($item['product']['title']); ?>
                </a>
              </h4>
              <div><?php echo $this->text('SKU'); ?> : <?php echo $this->escape($item['sku']); ?></div>
              <div><?php echo $this->text('Price'); ?> : <?php echo $this->escape($item['price_formatted']); ?></div>
              <div>
                <div class="input-group input-group-sm">
                  <span class="input-group-btn minus">
                    <button class="btn btn-default" name="cart[minus]" value="<?php echo $cart_id; ?>">
                      <span class="fa fa-minus"></span>
                    </button>
                  </span>
                  <input class="form-control text-center" maxlength="2" name="cart[items][<?php echo $cart_id; ?>][quantity]" value="<?php echo $item['quantity']; ?>">
                  <span class="input-group-btn plus">
                    <button class="btn btn-default" name="cart[plus]" value="<?php echo $cart_id; ?>">
                      <span class="fa fa-plus"></span>
                    </button>
                  </span>
                </div>
              </div>
              <div><?php echo $this->text('Amount'); ?> : <?php echo $this->escape($item['total_formatted']); ?></div>
            </div>
            <div class="col-md-3">
              <div class="btn-toolbar">
                <button title="<?php echo $this->text('Delete'); ?>" class="btn btn-danger btn-sm" name="cart[delete]" value="<?php echo $cart_id; ?>">
                  <i class="fa fa-trash"></i>
                </button>
              </div>
            </div>
          </div>
          <hr>
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
              <table class="table table-condensed table-striped">
                <tr>
                  <td class="col-md-6"><b><?php echo $this->text('Subtotal'); ?></b></td>
                  <td class="col-md-6"><b><?php echo $cart['total_formatted']; ?></b></td>
                </tr>
                <?php foreach ($price_components as $id => $price_component) { ?>
                <tr>
                  <td class="middle">
                    <?php echo $price_component['name']; ?>
                  </td>
                  <td>
                    <div class="input-group">
                      <input class="form-control" name="order[data][components][<?php echo $id; ?>]" value="<?php echo $price_component['price']; ?>">
                      <span class="input-group-addon"><?php echo $cart['currency']; ?></span>
                    </div>
                  </td>
                </tr>
                <?php } ?>
                <tr>
                  <td class="middle"><b><?php echo $this->text('Grand total'); ?></b></td>
                  <td>
                    <div class="input-group">
                      <input class="form-control" name="order[total]" value="<?php echo $total; ?>">
                      <span class="input-group-addon"><?php echo $cart['currency']; ?></span>
                    </div>
                  </td>
                </tr>
              </table>
            </div>
          </div>
          <div class="form-group">
            <div class="col-md-4">
              <div class="btn-group" data-toggle="buttons">
                <label title="<?php echo $this->text('Notify customer about this order'); ?>" class="btn btn-default">
                  <input type="checkbox" name="notify_customer_order" value="1" autocomplete="off"><i class="fa fa-envelope-o"></i>
                </label>
              </div>
            </div>
            <div class="col-md-8">
              <button class="btn btn-success btn-block" name="save" value="1"><?php echo $this->text('Save'); ?></button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <?php } else { ?>
  <div class="row">
    <div class="col-md-12">
      <?php echo $this->text('Add products to your cart'); ?>
    </div>
  </div>
  <?php } ?>
</form>