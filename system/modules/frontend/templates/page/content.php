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
    <div class="row page">
      <?php if(!empty($images)) { ?>
      <div class="col-md-3"><?php echo $images; ?></div>
      <?php } ?>
      <div class="<?php echo empty($images) ? 'col-md-12' : 'col-md-9'; ?>">
        <?php echo $this->xss($page['description']); ?>
      </div>
    </div>
  </div>
</div>
