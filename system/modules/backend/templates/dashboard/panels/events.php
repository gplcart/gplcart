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
<?php if ($this->access('report_events')) { ?>
<div class="card">
  <div class="card-header">
    <?php echo $this->text($content['title']); ?>
  </div>
  <div class="card-body">
    <?php if (!empty($content['data'])) { ?>
        <table class="table table-sm">
          <tbody>
            <?php foreach ($content['data'] as $event) { ?>
            <tr>
              <td><?php echo $this->truncate($this->e($event['message']), 50); ?></td>
              <td>
                <span class="label label-<?php echo $this->e($event['severity']); ?>">
                  <?php echo $this->text($event['severity']); ?>
                </span>
              </td>
            </tr>
            <?php } ?>
          </tbody>
        </table>
        <div class="text-right">
          <a href="<?php echo $this->url('admin/report/events'); ?>">
            <?php echo $this->text('See all'); ?>
          </a>
        </div>
    <?php } else { ?>
    <?php echo $this->text('There are no items yet'); ?>
    <?php } ?>
  </div>
</div>
<?php } ?>