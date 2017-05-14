<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<?php if (!empty($items)) { ?>
<div class="row section collection collection-file">
  <div class="col-md-12">
    <div id="carousel-example" class="carousel slide" data-ride="carousel">
      <div class="carousel-inner">
        <?php $pos = 0; ?>
        <?php foreach ($items as $item) { ?>
        <div class="item<?php echo $pos === 0 ? ' active' : ''; ?>">
          <?php echo $item['rendered']; ?>
          <div class="carousel-caption"><?php echo $this->e($item['title']); ?></div>
        </div>
        <?php $pos++; ?>
        <?php } ?>
      </div>
      <?php if (count($items) > 1) { ?>
      <a class="left carousel-control" href="#carousel-example" data-slide="prev">
        <span class="glyphicon glyphicon-chevron-left"></span>
      </a>
      <a class="right carousel-control" href="#carousel-example" data-slide="next">
        <span class="glyphicon glyphicon-chevron-right"></span>
      </a>
      <?php } ?>
    </div>
  </div>
</div>
<?php } ?>