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
<?php if (!empty($category['images'])) { ?>
    <div id="carousel-category-images" class="carousel slide" data-ride="carousel">
      <div class="carousel-inner">
        <?php $pos = 0; ?>
        <?php foreach ($category['images'] as $image) { ?>
        <div class="item<?php echo $pos === 0 ? ' active' : ''; ?>">
          <img class="fill" src="<?php echo $this->e($image['thumb']); ?>" title="<?php echo $this->e($image['title']); ?>" alt="<?php echo $this->e($image['title']); ?>">
        </div>
        <?php $pos++; ?>
        <?php } ?>
      </div>
      <?php if (count($category['images']) > 1) { ?>
      <a class="left carousel-control" href="#carousel-category-images" data-slide="prev">
        <span class="glyphicon glyphicon-chevron-left"></span>
      </a>
      <a class="right carousel-control" href="#carousel-category-images" data-slide="next">
        <span class="glyphicon glyphicon-chevron-right"></span>
      </a>
      <?php } ?>
    </div>
<?php } ?>
