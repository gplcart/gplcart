<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<div class="row section product-main">
  <div class="col-md-8 left">
    <h1 class="h4"><?php echo $this->e($product['title']); ?></h1>
    <div class="row section rating">
      <div class="col-md-12">
          <?php echo $rating; ?>
          <?php if (!empty($product['total_reviews'])) { ?>
            <a href="#reviews"><?php echo $this->text('@num reviews', array('@num' => $product['total_reviews'])); ?></a>
        <?php } ?>
        <?php echo $share; ?>
      </div>
    </div>
    <div class="row section images">
      <div class="col-md-12"><?php echo $images; ?></div>
    </div>
    <?php if ($product['description']) { ?>
    <div class="row section description">
      <div class="col-md-12">
        <?php echo $this->filter($product['description']); ?>
      </div>
    </div>
    <?php } ?>
  </div>
  <div class="col-md-4 right">
    <div class="panel add-to-cart panel-default">
      <div class="panel-body">
        <s id="original-price">
        <?php if (isset($product['selected_combination']['original_price']) && $product['selected_combination']['original_price'] > $product['selected_combination']['price']) { ?>
          <?php echo $this->e($product['selected_combination']['original_price_formatted']); ?>
        <?php } ?>
        </s>
        <div id="price" class="h4"><?php echo $this->e($product['selected_combination']['price_formatted']); ?></div>
        <p><?php echo $this->text('SKU'); ?>: <span id="sku" class="small"><?php echo $this->e($product['selected_combination']['sku']); ?></span></p>
        <?php echo $cart_form; ?>
      </div>
    </div>
  </div>
</div>
<?php if (!empty($pane_related)) { ?>
<?php echo $pane_related; ?>
<?php } ?>
<?php if (!empty($pane_recent)) { ?>
<?php echo $pane_recent; ?>
<?php } ?>
<?php if (!empty($pane_reviews)) { ?>
<?php echo $pane_reviews; ?>
<?php } ?>