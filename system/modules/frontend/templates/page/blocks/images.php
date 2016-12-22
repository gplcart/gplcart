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
<?php if (!empty($page['images'])) { ?>
<ul class="list-unstyled" data-slider="true" data-slider-settings='{
    "gallery":false,
    "item":1,
    "loop":true,
    "thumbItem":9,
    "slideMargin":0,
    "currentPagerPosition":"left"
}'>
  <?php foreach ($page['images'] as $image) { ?>
  <li class="thumb" data-thumb="<?php echo $this->escape($image['thumb']); ?>" data-src="<?php echo $this->escape($image['url']); ?>">
    <img src="<?php echo $this->escape($image['thumb']); ?>">
  </li>
  <?php } ?>
</ul>
<?php } ?>