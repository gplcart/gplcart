<div class="panel panel-default">
  <div class="panel-heading"><?php echo $this->text('Shipping'); ?></div>
  <div class="panel-body">
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
  </div>
</div>

