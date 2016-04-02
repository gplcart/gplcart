<div class="panel panel-default">
  <div class="panel-heading"><?php echo $this->text('Payment'); ?></div>
  <div class="panel-body">
    <div class="form-group">
      <div class="col-md-12">
        <?php foreach ($payment_services as $service_id => $service) {
    ?>
        <?php if (isset($service['html'])) {
    ?>
        <?php echo $service['html'];
    ?>
        <?php 
} else {
    ?>
        <div class="radio">
          <?php if (!empty($service['image'])) {
    ?>
          <div class="image">
            <img class="img-responsive" src="<?php echo $this->escape($service['image']);
    ?>">
          </div>
          <?php 
}
    ?>
          <label>
            <input type="radio" name="order[payment]" value="<?php echo $service_id;
    ?>"<?php echo (isset($order['payment']) && $order['payment'] == $service_id) ? ' checked' : '';
    ?>>
            <?php echo $this->escape($service['name']);
    ?>
            <?php if (!empty($service['price'])) {
    ?>
            <strong><?php echo $this->escape($service['price_formatted']);
    ?></strong>
            <?php 
}
    ?>
          </label>
        </div>
        <?php 
}
    ?>
        <?php 
} ?>
        <?php if (isset($form_errors['payment']) && !is_array($form_errors['payment'])) {
    ?>
        <div class="alert alert-danger alert-dismissible">
          <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
          <?php echo $form_errors['payment'];
    ?>
        </div>
        <?php 
} ?>
      </div>
    </div>
  </div>
</div>