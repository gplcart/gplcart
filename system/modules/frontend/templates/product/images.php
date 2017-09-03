<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 * @var $this \gplcart\core\controllers\frontend\Controller
 * To see available variables <?php print_r(get_defined_vars()); ?>
 */
?>
<?php if (!empty($product['images'])) { ?>
<?php $first = reset($product['images']); ?>
<div class="row">
  <?php if (count($product['images']) > 1) { ?>
  <div class="col-md-2">
    <div class="row product-thumbs">
      <?php foreach ($product['images'] as $image) { ?>
      <div class="col-md-12">
        <a data-gallery="product-images" data-file-id="<?php echo $this->e($image['file_id']); ?>" data-gallery-thumb="<?php echo $this->e($image['thumb']); ?>" class="thumbnail" href="<?php echo $this->e($image['url']); ?>">
          <img class="img-responsive" title="<?php echo $this->e($image['title']); ?>" alt="<?php echo $this->e($image['title']); ?>" src="<?php echo $this->e($image['thumb']); ?>">
        </a>
      </div>
      <?php } ?>
    </div>
  </div>
  <?php } ?>
  <div class="col-md-10">
    <?php if(empty($first['url'])) { ?>
    <img class="img-responsive" src="<?php echo $this->e($first['thumb']); ?>">
    <?php } else { ?>
    <a data-gallery="product-images" data-gallery-main-image="true" href="<?php echo $this->e($first['url']); ?>">
      <img class="img-responsive" src="<?php echo $this->e($first['thumb']); ?>" alt="<?php echo $this->e($first['title']); ?>" title="<?php echo $this->e($first['title']); ?>">
    </a>
    <?php } ?>
  </div>
</div>
<?php } ?>