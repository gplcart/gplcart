<?php if(!empty($rating)) { ?>
<div class="star-rating static" title="<?php echo $this->text('@num out of @total stars', array('@num' => $rating, '@total' => 5)); ?>">
  <div class="star-rating-wrap">
    <?php for($stars = 5; $stars > 0; $stars--) { ?>
    <?php if($stars > $rating) { ?>
    <span class="star-rating-ico fa fa-star-o"></span>
    <?php } else { ?>
    <span class="star-rating-ico fa fa-star"></span>
    <?php } ?>
    <?php } ?>
    <?php if(!empty($votes)) { ?>
    <span class="votes"> (<?php echo $this->text('@num votes', array('@num' => $votes)); ?>)</span>
    <?php } ?>
  </div>
</div>
<?php } ?>


