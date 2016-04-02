<div class="row">
  <div class="col-md-3">
    <div class="list-group">
      <a class="list-group-item active disabled">
        <h4 class="list-group-item-heading"><span class="fa fa-user"></span> <?php echo $this->truncate($this->escape($user['name']), 20); ?></h4>
        <p class="list-group-item-text"><?php echo $this->escape($user['email']); ?></p>
      </a>
      <a href="<?php echo $this->url("account/{$user['user_id']}/wishlist"); ?>" class="list-group-item">
        <h4 class="list-group-item-heading"><?php echo $this->text('Wishlist'); ?></h4>
        <p class="list-group-item-text"><?php echo $this->text('View and manage wishlist'); ?></p>
      </a>
      <a href="<?php echo $this->url("account/{$user['user_id']}/address"); ?>" class="list-group-item">
        <h4 class="list-group-item-heading"><?php echo $this->text('Addresses'); ?></h4>
        <p class="list-group-item-text"><?php echo $this->text('View and manage addressbook'); ?></p>
      </a>
      <a href="<?php echo $this->url("account/{$user['user_id']}/edit"); ?>" class="list-group-item">
        <h4 class="list-group-item-heading"><?php echo $this->text('Settings'); ?></h4>
        <p class="list-group-item-text"><?php echo $this->text('Edit account details'); ?></p>
      </a>
    </div>
    <a href="<?php echo $this->url('logout'); ?>">
      <span class="fa fa-sign-out"></span> <?php echo $this->text('Log out'); ?>
    </a>
  </div>
  <div class="col-md-9">
    
      <div class="row">
          <div class="col-md-12">
              <?php if ($orders) {
    ?>
    <table class="table">
        <thead>
            <tr>
              <th><a href="<?php echo $sort_order_id;
    ?>"># <i class="fa fa-sort"></i></a></th>
              <th><a href="<?php echo $sort_created;
    ?>"><?php echo $this->text('Created');
    ?> <i class="fa fa-sort"></i></a></th>
              <th><a href="<?php echo $sort_total;
    ?>"><?php echo $this->text('Total');
    ?> <i class="fa fa-sort"></i></a></th>
              <th><a href="<?php echo $sort_status;
    ?>"><?php echo $this->text('Status');
    ?> <i class="fa fa-sort"></i></a></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order) {
    ?>
            <tr>
                <td>
                  <a data-toggle="collapse" href="#order-details-<?php echo $order['order_id'];
    ?>">
                    <?php echo $this->escape($order['order_id']);
    ?>
                  </a>
                </td>
                <td><?php echo $this->date($order['created']);
    ?></td>
                <td><?php echo $this->escape($order['total_formatted']);
    ?></td>
                <td>
                    <?php if (empty($order['status_formatted'])) {
    ?>
                    <span class="text-danger"><?php echo $this->text('Unknown');
    ?></span>
                    <?php 
} else {
    ?>
                    <?php echo $this->escape($order['status_formatted']);
    ?>
                    <?php 
}
    ?>
                </td>
            </tr>
            <tr id="order-details-<?php echo $order['order_id'];
    ?>" class="active collapse">
                <td colspan="4"><?php echo $order['rendered'];
    ?></td>
            </tr>
            <?php 
}
    ?>
        </tbody>
    </table>
              
    <?php 
} else {
    ?>
    <?php echo $this->text('You have no orders yet. <a href="!href">Shop now</a>', array('!href' => $this->url('/')));
    ?>
    <?php 
} ?>    
              
          </div>
      </div>
      <?php if (!empty($pager)) {
    ?>
      <?php echo $pager;
    ?>
      <?php 
} ?>

  </div>
</div>



