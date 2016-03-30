<?php if ($queues) { ?>
<div class="row">
  <div class="col-md-6 col-md-offset-6 text-right">
  <?php if ($this->access('queue_edit') || $this->access('queue_delete')) { ?>
  <div class="btn-group">
    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
      <?php echo $this->text('With selected'); ?> <span class="caret"></span>
    </button>
    <ul class="dropdown-menu dropdown-menu-right">
      <?php if ($this->access('queue_edit')) { ?>
      <li>
        <a data-action="status" data-action-value="1" href="#">
          <?php echo $this->text('Status'); ?>: <?php echo $this->text('Enabled'); ?>
        </a>
      </li>
      <li>
        <a data-action="status" data-action-value="0" href="#">
          <?php echo $this->text('Status'); ?>: <?php echo $this->text('Disabled'); ?>
        </a>
      </li>
      <?php } ?>
      <?php if ($this->access('queue_delete')) { ?>
      <li>
        <a data-action="delete" href="#">
          <?php echo $this->text('Delete'); ?>
        </a>
      </li>
      <?php } ?>
    </ul>
  </div>
  <?php } ?>
</div>
</div>
<div class="row">
    <div class="col-md-12">
      <table class="table table-condensed margin-top-20 queues">
        <thead>
          <tr>
            <th><input type="checkbox" id="select-all" value="1"></th>
            <th><?php echo $this->text('Queue ID'); ?></th>
            <th><?php echo $this->text('Modified'); ?></th>
            <th><?php echo $this->text('Enabled'); ?></th>
            <th><?php echo $this->text('Items left'); ?></th>
            <th><?php echo $this->text('Progress'); ?></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($queues as $id => $queue) { ?>
          <tr data-queue-id="<?php echo $id; ?>">
            <td class="middle">
              <input type="checkbox" class="select-all" name="selected[]" value="<?php echo $id; ?>">
            </td>
            <td class="middle">
              <?php echo $this->text($queue['queue_id']); ?>
            </td>
            <td class="middle">
              <?php echo $this->date($queue['created']); ?>
            </td>
            <td class="middle">
              <?php echo!empty($queue['status']) ? '<i class="fa fa-check-square-o"></i>' : '<i class="fa fa-square-o"></i>'; ?>
            </td>
            <td class="middle">
              <?php echo $this->escape($queue['items']); ?>
            </td>
            <td class="middle">
              <div class="progress" style="margin-bottom:0;">
                <div class="progress-bar progress-bar-striped<?php echo!empty($queue['status']) ? ' active' : ' progress-bar-warning'; ?>"style="width: <?php echo intval($queue['progress']); ?>%; min-width: 2em;">
                  <?php echo ($queue['progress'] > 0) ? (int) $queue['progress'] : 0; ?>%
                </div>
              </div>
            </td>
          </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
</div>
<?php } else { ?>
<?php echo $this->text('Currently you have no queues'); ?>
<?php } ?>