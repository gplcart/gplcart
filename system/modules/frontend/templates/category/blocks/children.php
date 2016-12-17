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
<?php if ($children) { ?>
<div class="row section">
  <?php foreach ($children as $child) { ?>
  <div class="col-md-2">
    <a href="<?php echo $this->escape($child['url']); ?>">
      <?php if (!empty($child['thumb'])) { ?>
      <img class="img-responsive thumbnail" src="<?php echo $this->escape($child['thumb']); ?>" alt="<?php echo $this->escape($child['title']); ?>">
      <?php } ?>
      <div class="clearfix"><?php echo $this->escape($child['title']); ?></div>
    </a>
  </div>
  <?php } ?>
</div>
<?php } ?>