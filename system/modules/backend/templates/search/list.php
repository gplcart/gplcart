<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<div class="row">
  <div class="col-md-12">
    <?php if($results) { ?>
    <?php foreach($results as $result) { ?>
    <?php echo $result; ?>
    <?php } ?>
    <?php } else { ?>
    <?php echo $this->text('No results'); ?>
    <?php } ?>
  </div>
</div>
<div class="row">
  <div class="col-md-12">
    <?php echo $_pager; ?>
  </div>
</div>
