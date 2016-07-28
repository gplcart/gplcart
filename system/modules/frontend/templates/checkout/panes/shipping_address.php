<div class="panel panel-default">
  <div class="panel-heading"><?php echo $this->text('Shipping address'); ?></div>
  <div class="panel-body">
    <?php if (!empty($addresses) && empty($address_form)) { ?>
    <div class="form-group">
      <div class="col-md-12">
        <div class="btn-group margin-top-20 saved-addresses" data-toggle="buttons">
          <?php foreach ($addresses as $address_id => $address) { ?>
          <label class="address btn btn-default<?php echo (isset($order['shipping_address']) && $order['shipping_address'] == $address_id) ? ' active' : ''; ?>">
            <?php foreach ($address as $name => $value) { ?>
            <span class="clearfix">
              <span class="pull-left"><?php echo $this->escape($name); ?> : <?php echo $this->escape($value); ?></span>
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
                <div class="btn-group hidden-js">
                  <button class="btn btn-default" name="get_states" value="1">
                  <?php echo $this->text('Get states'); ?>
                  </button>
                </div>
              </div>
            </td>
          </tr>
          <?php foreach ($format as $key => $data) { ?>
          <?php if ($key !== 'country') { ?>
          <tr>
            <td class="middle<?php echo!empty($data['required']) ? ' required' : ''; ?>">
              <?php echo $this->escape($data['name']); ?>
            </td>
            <td>
              <div class="<?php echo $key; ?><?php echo isset($this->errors['address'][$key]) ? ' has-error' : ''; ?>">
                <?php if ($key === 'state_id') { ?>
                <select class="form-control" name="address[state_id]">
                  <option value="" disabled selected><?php echo $this->text('Select'); ?></option>
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
</div>