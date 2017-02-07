<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<?php if (!empty($items) || !empty($order['log'])) { ?>
<div class="panel panel-default hidden-print">
  <div class="panel-heading"><?php echo $this->text('Log'); ?></div>
  <div class="panel-body">
    <?php if (!empty($order['log'])) { ?>
    <b><?php echo $this->text('Message'); ?>:</b>
    <p><?php echo $this->escape($order['log']['text']); ?></p>
    <b><?php echo $this->text('Editor'); ?>:</b>
    <p>
      <?php if (empty($order['log']['user_name'])) { ?>
      <?php echo $this->text('Unknown'); ?>
      <?php } else { ?>
      <?php echo $this->escape($order['log']['user_name']); ?>
      (<?php echo $this->escape($order['log']['user_email']); ?>)
      <?php } ?>
    </p>
    <b><?php echo $this->text('Created'); ?>:</b>
    <?php echo $this->date($order['log']['created']); ?>
    <?php } else if (!empty($items)) { ?>
    <table class="table table-condensed">
      <?php foreach ($items as $id => $item) { ?>
      <tr>
        <td>
          <div class="created">
            <?php echo $this->date($item['created']); ?>,
            <?php if (empty($item['user_name'])) { ?>
            <span class="text-danger"><?php echo $this->text('Unknown'); ?></span>
            <?php } else { ?>
            <a href="<?php echo $this->url("account/{$item['user_id']}"); ?>">
              <?php echo $this->escape($item['user_email']); ?>
            </a>
            <?php } ?>
          </div>
          <div class="notify">
            <?php if(isset($item['data']['notify'])) { ?>
            <?php if($item['data']['notify'] == 0) { ?>
            <span class="label label-warning"><?php echo $this->text('Customer not notified'); ?></span>
            <?php } else if($item['data']['notify'] == 1){ ?>
            <span class="label label-danger"><?php echo $this->text('Failed to notify customer'); ?></span>
            <?php } else if($item['data']['notify'] == 2) { ?>
            <span class="label label-success"><?php echo $this->text('Customer notified'); ?></span>
            <?php } ?>
            <?php } ?>
          </div>
          <div class="text"><?php echo $this->escape($item['text']); ?></div>
        </td>
      </tr>
      <?php } ?>
    </table>
    <?php if(!empty($pager)) { ?>
    <?php echo $pager; ?>
    <?php } ?>
    <?php } ?>
  </div>
</div>
<?php } ?>

