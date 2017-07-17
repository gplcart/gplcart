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
    <?php if (!empty($items)) { ?>
    <ul class="list-inline pull-right">
      <?php $first_event_tab = key($items); ?>
      <?php foreach ($items as $severity => $events) { ?>
      <?php if (!empty($events)) { ?>
      <li class="<?php echo $first_event_tab == $severity ? 'active' : ''; ?>">
        <a href="#event-<?php echo $severity; ?>" data-toggle="tab"><?php echo $this->text($severity); ?></a>
      </li>
      <?php } ?>
      <?php } ?>
    </ul>
    <?php } ?>
  </div>
  <div class="panel-body">
    <?php if (!empty($items)) { ?>
    <div class="tab-content">
      <?php foreach ($items as $severity => $events) { ?>
      <?php if (!empty($events)) { ?>
      <div class="tab-pane<?php echo $first_event_tab == $severity ? ' in active' : ''; ?>" id="event-<?php echo $severity; ?>">
        <table class="table table-condensed">
          <tbody>
            <?php foreach ($events as $event) { ?>
            <tr>
              <td><?php echo $this->truncate($this->e($event['message']), 50); ?></td>
              <td><?php echo $this->date($event['time']); ?></td>
            </tr>
            <?php } ?>
          </tbody>
        </table>
        <div class="text-right">
          <a href="<?php echo $this->url('admin/report/events'); ?>">
            <?php echo $this->text('See all'); ?>
          </a>
        </div>
      </div>
      <?php } ?>
      <?php } ?>
    </div>
    <?php } else { ?>
    <?php echo $this->text('There are no items yet'); ?>
    <?php } ?>
  </div>
</div>
<?php } ?>