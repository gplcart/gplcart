<div class="panel panel-default">
  <div class="panel-body table-responsive">
    <table class="table table-striped">
      <?php foreach ($statuses as $status_id => $status) { ?>
      <tr class="<?php echo ((empty($status['status']) || is_array($status['status'])) && $status['severity'] !== 'info') ? $this->escape($status['severity']) : ''; ?>">
        <td class="col-md-3">
          <?php echo $this->escape($status['title']); ?>
          <?php if (!empty($status['description'])) { ?>
          <p class="small"><?php echo $this->xss($status['description']); ?></p>
          <?php } ?>
        </td>
        <td class="col-md-9">
          <?php if (empty($status['status'])) { ?>
          <?php echo $this->text('No'); ?>
          <?php } else if ($status['status'] === true) { ?>
          <?php echo $this->text('Yes'); ?>
          <?php } else if (is_array($status['status'])) { ?>
          <a data-toggle="collapse" href="#status-details-<?php echo $status_id; ?>">
            <?php echo $this->text('No'); ?>
          </a>
          <div class="collapse" id="status-details-<?php echo $status_id; ?>">
            <ul class="list-unstyled">
              <?php foreach ($status['status'] as $status_message) { ?>
              <li><?php echo $this->xss($status_message); ?></li>
              <?php } ?>
            </ul>
          </div>
          <?php } else { ?>
          <?php echo $this->truncate($status['status']); ?>
          <?php } ?>
        </td>
      </tr>
      <?php } ?>
    </table>
  </div>
</div>