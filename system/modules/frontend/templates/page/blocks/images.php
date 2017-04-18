<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<?php if (!empty($page['images'])) { ?>
<ul class="list-unstyled">
  <?php foreach ($page['images'] as $image) { ?>
  <li class="thumb">
    <img src="<?php echo $this->e($image['thumb']); ?>">
  </li>
  <?php } ?>
</ul>
<?php } ?>

