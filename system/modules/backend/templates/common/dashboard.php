<div class="row summary">
  <?php if ($this->access('order')) {
    ?>
  <div class="col-md-3 col-sm-6">
    <a href="<?php echo $this->url('admin/sale/order');
    ?>" class="thumbnail clearfix">
      <span class="caption">
        <span class="pull-left"><?php echo $this->text('Orders: @num', array('@num' => $order_total));
    ?></span>
        <span class="pull-right"><i class="fa fa-shopping-cart fa-2x"></i></span>
      </span>
    </a>
  </div>
  <?php 
} ?>
  <?php if ($this->access('user')) {
    ?>
  <div class="col-md-3 col-sm-6">
    <a href="<?php echo $this->url('admin/user');
    ?>" class="thumbnail clearfix">
      <span class="caption">
        <span class="pull-left"><?php echo $this->text('Users: @num', array('@num' => $user_total));
    ?></span>
        <span class="pull-right"><i class="fa fa-user fa-2x"></i></span>
      </span>
    </a>
  </div>
  <?php 
} ?>
  <?php if ($this->access('review')) {
    ?>
  <div class="col-md-3 col-sm-6">
    <a href="<?php echo $this->url('admin/content/review');
    ?>" class="thumbnail clearfix">
      <span class="caption">
        <span class="pull-left"><?php echo $this->text('Reviews: @num', array('@num' => $review_total));
    ?></span>
        <span class="pull-right"><i class="fa fa-comments fa-2x"></i></span>
      </span>
    </a>
  </div>
  <?php 
} ?>
  <?php if ($this->access('product')) {
    ?>
  <div class="col-md-3 col-sm-6">
    <a href="<?php echo $this->url('admin/content/product');
    ?>" class="thumbnail clearfix">
      <span class="caption">
        <span class="pull-left"><?php echo $this->text('Products: @num', array('@num' => $product_total));
    ?></span>
        <span class="pull-right"><i class="fa fa-futbol-o fa-2x"></i></span>
      </span>
    </a>
  </div>
  <?php 
} ?>
</div>
<?php if ($this->access('order')) {
    ?>
<div class="row">
  <div class="col-md-12">
    <div class="panel panel-default">
      <div class="panel-heading clearfix">
        <span class="pull-left"><?php echo $this->text('Recent orders');
    ?></span>
        <span class="pull-right"><i data-panel-id="recent-orders" class="fa fa-chevron-up"></i></span>
      </div>
      <div class="panel-body hide">
        <?php if ($orders) {
    ?>
        <table class="table table-responsive table-condensed">
          <tbody>
            <?php foreach ($orders as $order) {
    ?>
            <tr class="<?php echo ($this->isNew($order)) ? 'danger' : '';
    ?>">
              <td><?php echo $order['html'];
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
        <?php echo $this->text('No have no orders yet');
    ?>
        <?php if ($this->access('order_add')) {
    ?>
        <a href="<?php echo $this->url('admin/sale/order/add');
    ?>">
        <?php echo $this->text('Add');
    ?>
        </a>
        <?php 
}
    ?>
        <?php 
}
    ?>		
      </div>
    </div>
  </div>
</div>
<?php 
} ?>
<?php if ($this->access('report_ga')) {
    ?>
<div class="row hidden-xs">
  <div class="col-md-12">
    <div class="panel panel-default">
      <div class="panel-heading clearfix">
        <span class="pull-left"><?php echo $this->text('Traffic');
    ?>
          <?php if (count($stores) > 1) {
    ?>
          <span class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown"><?php echo $store['domain'];
    ?></a>
            <ul class="dropdown-menu stores">
              <?php foreach ($stores as $id => $store) {
    ?>
              <li>
                <a href="<?php echo $this->url(false, array('store_id' => $id) + $this->request->get());
    ?>">
                    <?php echo $this->escape($store['domain']);
    ?>
                </a>
              </li>
              <?php 
}
    ?>
            </ul>
          </span>
          <?php 
} else {
    ?>
          <?php echo $store['domain'];
    ?>
          <?php 
}
    ?>
          <a href="<?php echo $this->url(false, array('ga_update' => 1));
    ?>">
          <?php echo $this->text('update');
    ?>
          </a>
        </span>
        <span class="pull-right"><i data-panel-id="ga-traffic" class="fa fa-chevron-up"></i></span>
      </div>
      <div class="panel-body chart-traffic hide">
        <?php if ($chart_traffic) {
    ?>
        <canvas id="chart-traffic" style="height:150px; width:100%;"></canvas>
        <div id="chart-traffic-legend"></div>
        <?php 
} else {
    ?>
        <?php if (isset($ga_missing_settings)) {
    ?>
        <?php echo $ga_missing_settings;
    ?>
        <?php 
}
    ?>
        <?php if (isset($gapi_missing_credentials)) {
    ?>
        <br><?php echo $gapi_missing_credentials;
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
</div>
<?php 
} ?>
<?php if ($this->access('user') || $this->access('report_system')) {
    ?>
<div class="row">
  <?php if ($this->access('user')) {
    ?>
  <div class="col-md-6">
    <div class="panel panel-default">
      <div class="panel-heading clearfix">
        <span class="pull-left"><?php echo $this->text('Recent users');
    ?></span>
        <span class="pull-right"><i data-panel-id="recent-users" class="fa fa-chevron-up"></i></span>
      </div>
      <div class="panel-body hide">
        <table class="table table-responsive table-condensed">
          <tbody>
            <?php foreach ($users as $user) {
    ?>
            <tr>
              <td><?php echo $this->truncate($this->escape($user['email']), 30);
    ?></td>
              <td><?php echo $this->date($user['created']);
    ?></td>
            </tr>
            <?php 
}
    ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <?php 
}
    ?>
  <?php if ($this->access('report_system')) {
    ?>
  <div class="col-md-6">
    <div class="panel panel-default">
      <div class="panel-heading clearfix">
        <span class="pull-left"><?php echo $this->text('Recent events');
    ?></span>
        <?php if ($severity_count) {
    ?>
        <span class="event-severity">
          <?php foreach ($severity_count as $severity => $count) {
    ?>
          <span class="label label-<?php echo $severity;
    ?>" style="margin-right:.5em;">
          <?php echo $this->text('@severity - @count', array('@severity' => $this->text($severity), '@count' => $count));
    ?>
          </span>
          <?php 
}
    ?>
        </span>
        <?php 
}
    ?>
        <span class="pull-right"><i data-panel-id="recent-events" class="fa fa-chevron-up"></i></span>
      </div>
      <div class="panel-body hide">
        <?php if ($system_events) {
    ?>
        <table class="table table-responsive table-condensed">
          <tbody>
            <?php foreach ($system_events as $event) {
    ?>
            <tr>
              <td><?php echo $this->xss($event['message']);
    ?></td>
              <td><?php echo $this->date($event['time']);
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
        <?php echo $this->text('No records');
    ?>
        <?php 
}
    ?>
      </div>
    </div>
  </div>
  <?php 
}
    ?>
</div>
<?php 
} ?>