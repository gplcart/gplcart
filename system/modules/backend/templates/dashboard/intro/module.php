<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<div class="panel panel-default">
  <div class="panel-body">
    <div class="row">
      <div class="col-md-10">
        <h4><?php echo $this->text('Manage modules'); ?></h4>
        <p><?php echo $this->text('Extend your store by installing new modules and themes'); ?></p>
      </div>
      <div class="col-md-2 text-right">
        <a class="btn btn-default btn-block" href="<?php echo $this->url('admin/module/list'); ?>">
          <?php echo $this->text('Manage modules'); ?>
        </a>
      </div>
    </div>
  </div>
</div>