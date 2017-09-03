<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 * @var $this \gplcart\core\controllers\backend\Controller
 * To see available variables <?php print_r(get_defined_vars()); ?>
 */
?>
<div class="table-responsive">
  <table class="table table-bordered report-status">
    <?php foreach ($statuses as $status_id => $status) { ?>
    <tr class="<?php echo (empty($status['status']) || is_array($status['status'])) && $status['severity'] !== 'info' ? $this->e($status['severity']) : ''; ?>">
      <td class="col-md-3">
        <?php echo $this->e($status['title']); ?>
        <?php if (!empty($status['description'])) { ?>
        <p class="small"><?php echo $this->filter($status['description']); ?></p>
        <?php } ?>
      </td>
      <td class="col-md-9">
        <?php if (empty($status['details'])) { ?>
        <?php echo $this->truncate($status['status']); ?>
        <?php } else { ?>
        <a data-toggle="collapse" href="#status-details-<?php echo $status_id; ?>">
          <?php echo $this->truncate($status['status']); ?>
        </a>
        <?php } ?>
        <?php if (!empty($status['details'])) { ?>
        <div class="collapse" id="status-details-<?php echo $status_id; ?>">
          <ul class="list-unstyled">
            <?php foreach ($status['details'] as $status_message) { ?>
            <li><?php echo $this->filter($status_message); ?></li>
            <?php } ?>
          </ul>
        </div>
        <?php } ?>
        <?php if ($status_id === 'cron' && $this->config('cron_key') && $this->access('cron')) { ?>
        <a target="_blank" href="<?php echo $this->url('cron', array('key' => $this->config('cron_key'))); ?>">
          <?php echo $this->text('Run cron'); ?>
        </a>
        <?php } ?>
      </td>
    </tr>
    <?php } ?>
  </table>
</div>