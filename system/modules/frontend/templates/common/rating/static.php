<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 * 
 * To see available variables: <?php print_r(get_defined_vars()); ?>
 * To see the current controller object: <?php print_r($this); ?>
 * To call a controller method: <?php $this->exampleMethod(); ?>
 */
?>
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


