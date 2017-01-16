<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<?php if ($this->access('user')) { ?>
<div class="panel panel-default">
  <div class="panel-heading"><?php echo $this->text('Recent users'); ?></div>
  <div class="panel-body">
    <ul class="list-unstyled">
      <?php foreach ($users as $user) { ?>
      <?php if(!$this->isSuperadmin($user['user_id']) || $this->isSuperadmin()) { ?>
      <li class="list-group-item clearfix">
        <span class="pull-left"><?php echo $this->truncate($this->escape($user['email']), 30); ?></span>
        <span class="pull-right small text-muted"><?php echo $this->date($user['created']); ?></span>
      </li>
      <?php } ?>
      <?php } ?>
    </ul>
    <div class="text-right">
      <a href="<?php echo $this->url('admin/user/list'); ?>">
        <?php echo $this->text('See all users'); ?>
      </a>
    </div>
  </div>
</div>
<?php } ?>