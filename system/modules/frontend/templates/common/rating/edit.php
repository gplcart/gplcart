<div id="star-rating-<?php echo $product['product_id']; ?>" class="star-rating dinamic">
  <div class="star-rating-wrap">
    <?php if(!empty($unvote)) { ?>
    <input class="star-rating-input unvote" id="star-rating-0" name="review[rating]" type="radio" value="0">
    <label class="star-rating-ico fa fa-times-circle unvote" for="star-rating-0" title="<?php echo $this->text('Unvote'); ?>"></label>
    <?php } ?>
    <input class="star-rating-input" id="star-rating-5" type="radio" name="review[rating]" value="5"<?php echo (isset($review['rating']) && $review['rating'] == 5) ? ' checked' : ''; ?>>
    <label class="star-rating-ico fa fa-star-o" for="star-rating-5" title="<?php echo $this->text('@num out of @total stars', array('@num' => 5, '@total' => 5)); ?>"></label>
    <input class="star-rating-input" id="star-rating-4" type="radio" name="review[rating]" value="4"<?php echo (isset($review['rating']) && $review['rating'] == 4) ? ' checked' : ''; ?>>
    <label class="star-rating-ico fa fa-star-o" for="star-rating-4" title="<?php echo $this->text('@num out of @total stars', array('@num' => 4, '@total' => 5)); ?>"></label>
    <input class="star-rating-input" id="star-rating-3" type="radio" name="review[rating]" value="3"<?php echo (isset($review['rating']) && $review['rating'] == 3) ? ' checked' : ''; ?>>
    <label class="star-rating-ico fa fa-star-o" for="star-rating-3" title="<?php echo $this->text('@num out of @total stars', array('@num' => 3, '@total' => 5)); ?>"></label>
    <input class="star-rating-input" id="star-rating-2" type="radio" name="review[rating]" value="2"<?php echo (isset($review['rating']) && $review['rating'] == 2) ? ' checked' : ''; ?>>
    <label class="star-rating-ico fa fa-star-o" for="star-rating-2" title="<?php echo $this->text('@num out of @total stars', array('@num' => 2, '@total' => 5)); ?>"></label>
    <input class="star-rating-input" id="star-rating-1" type="radio" name="review[rating]" value="1"<?php echo (isset($review['rating']) && $review['rating'] == 1) ? ' checked' : ''; ?>>
    <label class="star-rating-ico fa fa-star-o" for="star-rating-1" title="<?php echo $this->text('@num out of @total stars', array('@num' => 1, '@total' => 5)); ?>"></label>
  </div>
</div>
<script>
$(function(){
    $('.star-rating-ico').tooltip();
});
</script>