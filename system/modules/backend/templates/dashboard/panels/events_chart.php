<?php if ($this->access('report_system')) { ?>
<div class="panel panel-default">
  <div class="panel-heading"><?php echo $this->text('Events'); ?></div>
  <div class="panel-body">
    <canvas id="chart-events"></canvas>
  </div>
</div>
<?php } ?>
