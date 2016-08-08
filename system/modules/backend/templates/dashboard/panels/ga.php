<?php if ($this->access('report_ga')) { ?>
<div class="panel panel-default">
  <div class="panel-heading">
    <?php echo $this->text('Traffic'); ?>
    <?php if (count($stores) > 1) { ?>
    <span class="dropdown">
      <a href="#" class="dropdown-toggle" data-toggle="dropdown"><?php echo $store['domain']; ?></a>
      <ul class="dropdown-menu stores">
        <?php foreach ($stores as $id => $store) { ?>
        <li>
          <a href="<?php echo $this->url(false, array('store_id' => $id) + $this->query); ?>">
            <?php echo $this->escape($store['domain']); ?>
          </a>
        </li>
        <?php } ?>
      </ul>
    </span>
    <?php } else { ?>
    <?php echo $store['domain']; ?>
    <?php } ?>
    <?php if (!$missing_settings && !$missing_credentials) { ?>
    <a href="<?php echo $this->url(false, array('ga_update' => 1)); ?>">
      <?php echo $this->text('update'); ?>
    </a>
    <?php } ?>
  </div>
  <div class="panel-body">
    <?php if (!$missing_settings && !$missing_credentials) { ?>
    <canvas id="chart-traffic"></canvas>
    <div class="text-right">
      <a href="<?php echo $this->url('admin/report/ga'); ?>">
        <?php echo $this->text('See all analytics'); ?>
      </a>
    </div> 
    <?php } else { ?>
    <?php if ($missing_settings) { ?>
    <?php echo $this->text('<a href="!href">Google Analytics</a> is not properly set up', array('!href' => $this->url("admin/settings/store/{$store['store_id']}"))); ?>
    <?php } ?>
    <?php if ($missing_credentials) { ?>
    <br><?php echo $this->text('<a href="!href">Google API credentials</a> are not properly set up', array('!href' => $this->url('admin/settings/common'))); ?>
    <?php } ?>
    <?php } ?>
  </div>
</div>
<?php } ?>
