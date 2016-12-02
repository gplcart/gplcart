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
<?php if(!empty($tree)) { ?>
<ul class="list-group" id="sidebar-menu">
<?php foreach ($tree as $item) { ?>
  <li class="list-group-item depth-<?php echo $item['depth']; ?><?php echo empty($item['active']) ? '' : ' active'; ?>">
  <?php echo $item['indentation']; ?>
  <?php if (empty($item['active'])) { ?>
  <a title="<?php echo $item['title']; ?>" href="<?php echo $item['url']; ?>"><?php echo $item['title']; ?></a>
  <?php } else { ?>
  <?php echo $item['title']; ?>
  <?php } ?>
  </li>
<?php } ?>
</ul>
<?php } ?>