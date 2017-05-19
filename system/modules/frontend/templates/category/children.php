<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<?php if (!empty($children)) { ?>
<div class="row section">
  <?php foreach ($children as $child) { ?>
  <div class="col-md-2">
    <a href="<?php echo $this->e($child['url']); ?>">
      <?php if (!empty($child['thumb'])) { ?>
      <img class="img-responsive thumbnail" src="<?php echo $this->e($child['thumb']); ?>" alt="<?php echo $this->e($child['title']); ?>" title="<?php echo $this->e($child['title']); ?>">
      <?php } ?>
      <div class="clearfix"><?php echo $this->e($child['title']); ?></div>
    </a>
  </div>
  <?php } ?>
</div>
<?php } ?>