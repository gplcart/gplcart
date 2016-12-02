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
<?php if (!empty($pages)) { ?>
<div class="panel panel-default collection collection-page pages">
  <div class="panel-heading"><?php echo $this->escape($title); ?></div>
  <div class="panel-body">
    <div class="row">
      <?php foreach ($pages as $page) { ?>
      <?php echo $page['rendered']; ?>
      <?php } ?>
    </div>
  </div>
</div>
<?php } ?>

