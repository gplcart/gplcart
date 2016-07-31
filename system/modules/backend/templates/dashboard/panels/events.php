<?php if ($this->access('report_system')) { ?>
<div class="panel panel-default">
  <div class="panel-heading">
    <?php echo $this->text('Recent events'); ?>
  </div>
  <div class="panel-body">
    <?php if (!empty($events)) { ?>   
    <ul class="nav nav-tabs">
      <?php $index = 0; ?>
      <?php foreach ($events as $severity => $items) { ?>
      <?php if (!empty($items)) { ?>
      <li role="presentation" class="<?php echo ($index == 0) ? 'active' : ''; ?>">
        <a href="#event-<?php echo $severity; ?>" data-toggle="tab"><?php echo $this->text($severity); ?></a>
      </li>
      <?php } ?>
      <?php $index++; ?>
      <?php } ?>
    </ul>    
    <div class="tab-content">
      <?php $index = 0; ?>
      <?php foreach ($events as $severity => $items) { ?>
      <?php if (!empty($items)) { ?>
      <div class="tab-pane<?php echo ($index == 0) ? ' active' : ''; ?>" id="event-<?php echo $severity; ?>">
        <ul class="list-unstyled">
          <?php foreach ($items as $event) { ?>
          <li class="list-group-item clearfix">
            <span class="pull-left"><?php echo $this->truncate($this->escape($event['message'])); ?></span>
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
      <?php $index++; ?>
      <?php } ?>
    </div>
    <?php } else { ?>
    <?php echo $this->text('No events yet'); ?>
    <?php } ?>
  </div>
</div>
<?php } ?>