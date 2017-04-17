<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<div class="panel panel-default order-components">
  <div class="panel-heading clearfix"><?php echo $this->text('Components'); ?></div>
  <div class="panel-body">
    <table class="table table-condensed">
      <tbody>
        <?php foreach($components as $component) { ?>
        <?php echo $component['rendered']; ?>
        <?php } ?>
        <tr>
          <td><b><?php echo $this->text('Grand total'); ?></b></td>
          <td><b><?php echo $this->e($order['total_formatted']); ?></b></td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

