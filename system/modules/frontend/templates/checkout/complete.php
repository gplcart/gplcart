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
<div class="panel panel-default complete">
  <div class="panel-body">
    <?php echo $complete_message; ?>
    <?php if (!empty($templates)) { ?>
    <?php foreach ($templates as $template) { ?>
    <?php echo $template; ?>
    <?php } ?>
    <?php } ?>
  </div>
</div>