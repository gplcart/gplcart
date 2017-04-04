<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<?php if ($this->access('report_events')) { ?>
<div class="panel panel-default">
  <div class="panel-heading">
    <?php echo $this->text('System events'); ?>
  </div>
  <div class="panel-body">
    <?php if (!empty($events)) { ?>
    <ul class="nav nav-tabs">
      <?php $first_event_tab = key($events); ?>
      <?php foreach ($events as $severity => $items) { ?>
      <?php if (!empty($items)) { ?>
      <li role="presentation" class="<?php echo $first_event_tab == $severity ? 'active' : ''; ?>">
        <a href="#event-<?php echo $severity; ?>" data-toggle="tab"><?php echo $this->text($severity); ?></a>
      </li>
      <?php } ?>
      <?php } ?>
    </ul>
    <div class="tab-content">
      <?php foreach ($events as $severity => $items) { ?>
      <?php if (!empty($items)) { ?>
      <div class="tab-pane<?php echo $first_event_tab == $severity ? ' in active' : ''; ?>" id="event-<?php echo $severity; ?>">
        <ul class="list-unstyled">
          <?php foreach ($items as $event) { ?>
          <li class="list-group-item clearfix">
            <span class="pull-left"><?php echo $this->truncate($this->escape($event['message']), 70); ?></span>
            <span class="pull-right small text-muted"><?php echo $this->date($event['time']); ?></span>
          </li>
          <?php } ?>
        </ul>
        <div class="text-right">
          <a href="<?php echo $this->url('admin/report/events', array('severity' => $severity)); ?>">
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