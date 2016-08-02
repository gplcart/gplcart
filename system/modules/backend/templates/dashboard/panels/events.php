<?php if ($this->access('report_system')) { ?>
<div class="panel panel-default">
  <div class="panel-heading">
    <?php echo $this->text('System events'); ?>
  </div>
  <div class="panel-body">
    <?php if (!empty($events)) { ?>   
    <ul class="list-inline text-right">
      <li role="presentation" class="active">
        <a href="#tab-chart-events" data-toggle="tab"><?php echo $this->text('Summary'); ?></a>
      </li>
      <?php foreach ($events as $severity => $items) { ?>
      <?php if (!empty($items)) { ?>
      <li role="presentation">
        <a href="#event-<?php echo $severity; ?>" data-toggle="tab"><?php echo $this->text($severity); ?></a>
      </li>
      <?php } ?>
      <?php } ?>
    </ul>    
    <div class="tab-content">
      <div class="tab-pane active" id="tab-chart-events">
        <canvas id="chart-events"></canvas>
      </div>
      <?php foreach ($events as $severity => $items) { ?>
      <?php if (!empty($items)) { ?>
      <div class="tab-pane" id="event-<?php echo $severity; ?>">
        <ul class="list-unstyled">
          <?php foreach ($items as $event) { ?>
          <li class="list-group-item clearfix">
            <span class="pull-left"><?php echo $this->truncate($this->escape($event['message']), 70); ?></span>
            <span class="pull-right small text-muted"><?php echo $this->date($event['time']); ?></span>
          </li>
          <?php } ?>
        </ul>
        <div class="text-right">
          <a href="<?php echo $this->url('admin/report/system', array('severity' => $severity)); ?>">
            <?php echo $this->text('See all'); ?>
          </a>
        </div>
      </div>
      <?php } ?>
      <?php } ?>
    </div>
    <?php } else { ?>
    <?php echo $this->text('No events yet'); ?>
    <?php } ?>
  </div>
</div>
<?php } ?>