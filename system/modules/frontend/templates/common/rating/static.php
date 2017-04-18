<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<?php if (!empty($rating['rating'])) { ?>
<span class="star-rating static" title="<?php echo $this->text('@num out of @total stars', array('@num' => $rating['rating'], '@total' => 5)); ?>">
  <span class="star-rating-wrap">
    <span class="star-rating-icons">
    <?php for($stars = 5; $stars > 0; $stars--) { ?>
    <?php if($stars > $rating['rating']) { ?>
    <span class="star-rating-ico fa fa-star-o"></span>
    <?php } else { ?>
    <span class="star-rating-ico fa fa-star"></span>
    <?php } ?>
    <?php } ?>
    </span>
  </span>
</span>
<?php } ?>