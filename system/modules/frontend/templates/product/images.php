<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<?php if (!empty($product['images'])) { ?>
<?php $first = array_shift($product['images']); ?>
<div class="row">
  <?php if(!empty($product['images'])) { ?>
  <div class="col-md-4">
    <div class="row">
      <?php foreach ($product['images'] as $image) { ?>
      <div class="col-md-6">
        <a class="thumbnail" href="#">
          <img class="img-responsive" title="<?php echo $this->e($image['title']); ?>" alt="<?php echo $this->e($image['title']); ?>" src="<?php echo $this->e($image['thumb']); ?>">
        </a>
      </div>
      <?php } ?>
    </div>
  </div>
  <?php } ?>
  <div class="col-md-8">
    <a href="<?php echo $this->e($first['url']); ?>">
      <img class="img-responsive" src="<?php echo $this->e($first['thumb']); ?>" alt="<?php echo $this->e($first['title']); ?>" title="<?php echo $this->e($first['title']); ?>">
    </a>
  </div>
</div>
<?php } ?>