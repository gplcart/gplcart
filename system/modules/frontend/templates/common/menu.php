<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<?php if (!empty($items)) { ?>
<ul class="nav navbar-nav menu-top">
  <?php foreach ($items as $item) { ?>
  <?php if ($item['depth'] <= $depth) { ?>
  <li class="depth-<?php echo $this->e($item['depth']); ?><?php echo empty($item['active']) ? '' : ' active'; ?>">
    <?php echo $item['indentation']; ?>
    <?php if (empty($item['active'])) { ?>
    <a title="<?php echo $this->e($item['title']); ?>" href="<?php echo $this->e($item['url']); ?>"><?php echo $this->e($this->truncate($item['title'], 30)); ?></a>
    <?php } else { ?>
    <a class="disabled"><?php echo $this->e($this->truncate($item['title'], 30)); ?></a>
    <?php } ?>
  </li>
  <?php } ?>
  <?php } ?>
</ul>
<?php } ?>