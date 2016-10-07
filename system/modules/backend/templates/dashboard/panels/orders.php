<?php if ($this->access('order')) { ?>
<div class="panel panel-default">
  <div class="panel-heading">
    <?php echo $this->text('Recent orders'); ?>
  </div>
  <div class="panel-body">
    <?php if (!empty($orders)) { ?>
    <table class="table table-responsive table-condensed">
      <tbody>
        <?php foreach ($orders as $order) { ?>
        <tr>
          <td><?php echo $order['rendered']; ?></td>
        </tr>
        <?php } ?>
      </tbody>
    </table>
    <div class="text-right">
      <a href="<?php echo $this->url('admin/sale/order'); ?>">
        <?php echo $this->text('See all'); ?>
      </a>
    </div>
    <?php } else { ?>
    <?php echo $this->text('No have no orders yet'); ?>
    <?php if ($this->access('order_add')) { ?>
    <a href="<?php echo $this->url('admin/sale/order/add'); ?>">
      <?php echo $this->text('Add'); ?>
    </a>
    <?php } ?>
    <?php } ?>		
  </div>
</div>
<?php } ?>