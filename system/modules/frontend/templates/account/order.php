<?php d($shipping_address); ?>

<div class="row">
    <div class="col-md-6">
        <h5><?php echo $this->text('Shipping address'); ?></h5>
        <table class="table table-condensed">
          <?php foreach($shipping_address as $name => $value) { ?>
          <tr>
              <td><?php echo $this->escape($name); ?></td>
              <td><?php echo $this->escape($value); ?></td>
          </tr>
          <?php } ?>
        </table>
    </div>
</div>