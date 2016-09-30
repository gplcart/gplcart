<div class="panel panel-default">
  <div class="panel-heading"><?php echo $this->text('Payment'); ?></div>
  <div class="panel-body">
    <div class="form-group">
      <div class="col-md-12">
        <?php foreach ($payment_methods as $method_id => $method) { ?>
        
        
        
        
        
        
        <?php if (!empty($method['template']['select'])) { ?>
        <?php echo $method['template']['select']; ?>
        <?php } else { ?>
        <div class="radio">
          <?php if (!empty($method['image'])) { ?>
          <div class="image">
            <img class="img-responsive" src="<?php echo $this->escape($method['image']); ?>">
          </div>
          <?php } ?>
          <label>
            <input type="radio" name="order[payment]" value="<?php echo $method_id; ?>"<?php echo (isset($order['payment']) && $order['payment'] == $method_id) ? ' checked' : ''; ?>>
            <?php echo $this->escape($method['title']); ?>
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
  </div>
</div>