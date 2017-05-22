<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<?php if (!empty($page['images'])) { ?>
    <div id="carousel-page-images" class="carousel slide" data-ride="carousel">
      <div class="carousel-inner">
        <?php $pos = 0; ?>
        <?php foreach ($page['images'] as $image) { ?>
        <div class="item<?php echo $pos === 0 ? ' active' : ''; ?>">
          <img class="fill" src="<?php echo $this->e($image['thumb']); ?>" title="<?php echo $this->e($image['title']); ?>" alt="<?php echo $this->e($image['title']); ?>">
        </div>
        <?php $pos++; ?>
        <?php } ?>
      </div>
      <?php if (count($page['images']) > 1) { ?>
      <a class="left carousel-control" href="#carousel-page-images" data-slide="prev">
        <span class="glyphicon glyphicon-chevron-left"></span>
      </a>
      <a class="right carousel-control" href="#carousel-page-images" data-slide="next">
        <span class="glyphicon glyphicon-chevron-right"></span>
      </a>
      <?php } ?>
    </div>
<?php } ?>