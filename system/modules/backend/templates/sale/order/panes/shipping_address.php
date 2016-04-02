<div class="panel panel-default">
  <div class="panel-heading"><?php echo $this->text('Shipping address'); ?></div>
  <div class="panel-body">
    <div class="row">
      <div class="col-md-6">
        <?php if (!empty($items)) {
    ?>
        <table class="table table-condensed">
          <?php foreach ($items as $key => $value) {
    ?>
          <tr>
            <td><?php echo $this->escape($key);
    ?></td>
            <td><?php echo $this->escape($value);
    ?></td>
          </tr>
          <?php 
}
    ?>
        </table>
        <?php 
} ?>
      </div>
      <div class="col-md-6">
        <div class="embed-responsive embed-responsive-4by3">
          <div id="map-container" class="embed-responsive-item"></div>
        </div>
      </div>
    </div>
  </div>
</div>