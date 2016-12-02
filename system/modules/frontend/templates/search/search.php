<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 * 
 * To see available variables: <?php print_r(get_defined_vars()); ?>
 * To see the current controller object: <?php print_r($this); ?>
 * To call a controller method: <?php $this->exampleMethod(); ?>
 */
?>
<div class="panel panel-default">
  <div class="panel-body">
    <?php if (!empty($results)) { ?>
    <?php echo $navbar; ?>
    <?php echo $results; ?>
    <?php if ($pager) { ?>
    <div class="row">
      <div class="col-md-12 text-right">
        <?php echo $pager; ?>
      </div>
    </div>
    <?php } ?>
    <?php } else { ?>
    <?php echo $this->text('No products found. Try another search keyword'); ?>
    <?php } ?>
  </div>
</div>