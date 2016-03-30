<span class="media">
  <span class="media-left">
    <b>#<?php echo $order['order_id']; ?></b>
  </span>
  <span class="media-body">
    <span class="media-heading small">
      <i class="fa fa-clock-o"></i> <?php echo $this->date($order['created']); ?>
      <b><?php echo $order['total_formatted']; ?></b>
    </span>
    <br>
    <span class="small">
    <?php echo $order['country']; ?>
    <?php echo $order['city']; ?>
    <?php echo $order['address_1']; ?>
    <?php if($order['address_2']) { ?>
    , <?php echo $order['address_2']; ?>
    <?php } ?>
    <?php if($order['phone']) { ?>
    tel: <?php echo $order['phone']; ?>
    <?php } ?>
    <?php if($order['postcode']) { ?>
    zip: <?php echo $order['postcode']; ?>
    <?php } ?>
    </span>
    <br>
    <span class="small">
    <?php echo $order['last_name']; ?>
    <?php echo $order['first_name']; ?>
    <?php if($order['middle_name']) { ?>
    <?php echo $order['middle_name']; ?>
    <?php } ?>
    </span>
  </span>
</span>