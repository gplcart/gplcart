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
<ul class="<?php echo $menu_class; ?>">
<?php foreach ($tree as $item) { ?>
  <?php if(!isset($menu_max_depth) || $item['depth'] <= $menu_max_depth) { ?>
  <li class="depth-<?php echo $item['depth']; ?><?php echo empty($item['active']) ? '' : ' active'; ?>">
  <?php echo $item['indentation']; ?>
  <?php if (empty($item['active'])) { ?>
  <a title="<?php echo $item['title']; ?>" href="<?php echo $item['url']; ?>"><?php echo $item['title']; ?></a>
  <?php } else { ?>
  <a class="disabled"><?php echo $item['title']; ?></a>
  <?php } ?>
  </li>
  <?php } ?>
<?php } ?>
</ul>
<?php } ?>