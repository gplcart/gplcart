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
    <?php if (empty($results)) { ?>
    <?php echo $this->text('No products found. Try another search keyword'); ?>
    <?php } else { ?>
    <?php echo $navbar; ?>
    <?php echo $results; ?>
    <?php if (!empty($pager)) { ?>
    <div class="row">
      <div class="col-md-12 text-right">
        <?php echo $pager; ?>
      </div>
    </div>
    <?php } ?>
    <?php } ?>
  </div>
</div>