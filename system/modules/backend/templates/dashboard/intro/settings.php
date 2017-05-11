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
        <h4><?php echo $this->text('Edit settings'); ?></h4>
        <p><?php echo $this->text('Add company info, change logo, theme'); ?></p>
      </div>
      <div class="col-md-2 text-right">
        <a class="btn btn-default btn-block" href="<?php echo $this->url('admin/settings/store/1/edit'); ?>">
          <?php echo $this->text('Edit settings'); ?>
        </a>
      </div>
    </div>
  </div>
</div>