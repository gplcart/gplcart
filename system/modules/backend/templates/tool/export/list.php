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
    <div class="btn-toolbar">
      <?php foreach ($operations as $id => $operation) { ?>
      <a class="btn btn-default" href="<?php echo $this->url("admin/tool/export/$id"); ?>">
        <?php echo $this->escape($operation['name']); ?>
      </a>
      <?php } ?>
    </div>
  </div>
</div>