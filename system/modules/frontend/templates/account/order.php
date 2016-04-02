<div class="row order-details">
  <div class="col-md-6">
    <strong><?php echo $this->text('Shipping address'); ?></strong>
    <table class="table">
      <?php foreach ($shipping_address as $name => $value) { ?>
      <tr>
        <td><?php echo $this->escape($name); ?></td>
        <td><?php echo $this->escape($value); ?></td>
      </tr>
      <?php } ?>
    </table>
  </div>
  <div class="col-md-6">
    <table class="table">
      <tr>
        <td colspan="2"><strong><?php echo $this->text('Products'); ?></strong></td>
      </tr>
      <?php if(empty($components['cart'])) { ?>
      <tr>
        <td colspan="2"><strong><?php echo $this->text('Not available'); ?></strong></td>
      </tr>
      <?php } else { ?>
      <?php foreach ($components['cart'] as $item) { ?>
      <tr>
        <td>
          <?php if (empty($item['product'])) { ?>
          <?php echo $this->text('Unknown'); ?>
          <?php } else { ?>
          <?php if (!empty($item['product']['status']) && $item['product']['store_id'] == $this->store_id) { ?>
          <a href="<?php echo $this->url("product/{$item['product_id']}"); ?>">
            <?php echo $this->truncate($this->escape($item['product']['title']), 50); ?>
          </a>
          <?php } else { ?>
              <?php echo $this->truncate($this->escape($item['product']['title']), 50); ?>
          <?php } ?> X <?php echo $this->escape($item['quantity']); ?>
          <p class="small text-muted"><?php echo $this->text('SKU'); ?>: <?php echo $this->escape($item['sku']); ?></p>
          <?php } ?>
        </td>
        <td><?php echo $this->escape($item['component_price_formatted']); ?></td>
      </tr>
      <?php } ?>
      <?php } ?>
      <?php if (!empty($components['service'])) { ?>
      <tr><td colspan="2"><strong><?php echo $this->text('Line items'); ?></strong></td></tr>
      <?php foreach ($components['service'] as $item) { ?>
      <tr>
        <td><?php echo $this->escape($item['name']); ?></td>
        <td><?php echo $this->escape($item['component_price_formatted']); ?></td>
      </tr>
      <?php } ?>
      <?php } ?>
      <?php if (!empty($components['rule'])) { ?>
      <?php foreach ($components['rule'] as $item) { ?>
      <tr>
        <td><?php echo $this->escape($item['name']); ?></td>
        <td><?php echo $this->escape($item['component_price_formatted']); ?></td>
      </tr>
      <?php } ?>
      <?php } ?>
      <tr>
        <td><strong><?php echo $this->text('Total'); ?></strong></td>
        <td><strong><?php echo $this->escape($order['total_formatted']); ?></strong></td>
      </tr>
    </table>
  </div>
</div>