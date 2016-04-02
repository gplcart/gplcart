<?php if (!empty($quotes)) {
    ?>
<div class="panel panel-default">
  <div class="panel-body">
    <div class="row">
      <?php foreach ($quotes as $service_id => $service) {
    ?>
      <?php echo $this->xss($service['name']);
    ?>
      <?php echo $this->xss($service['description']);
    ?>
      <?php if (empty($service['price'])) {
    ?>
      <?php echo $this->text('free');
    ?>
      <?php 
} else {
    ?>
      <?php echo $this->escape($service['price_formatted']);
    ?>
      <?php 
}
    ?>
      <?php 
}
    ?>
    </div>
  </div>
</div>
<?php 
} ?>
