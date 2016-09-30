<div class="panel panel-default">
  <div class="panel-heading"><?php echo $this->text('Shipping'); ?></div>
  <div class="panel-body">
    <?php foreach ($shipping_methods as $method_id => $method) { ?>
        <?php if (isset($method['html'])) { ?>
        <?php echo $method['html']; ?>
        <?php } else { ?>
        <div class="radio">
          <?php if (!empty($method['image'])) { ?>
          <div class="image">
            <img class="img-responsive" src="<?php echo $this->escape($method['image']); ?>">
          </div>
          <?php } ?>
          <label>
            <input type="radio" name="order[shipping]" value="<?php echo $method_id; ?>"<?php echo (isset($order['shipping']) && $order['shipping'] == $method_id) ? ' checked' : ''; ?>>
            <?php echo $this->escape($method['title']); ?>
            <?php if (!empty($method['price'])) { ?>
            <strong><?php echo $this->escape($method['price_formatted']); ?></strong>
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

