<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 * @var $this \gplcart\core\controllers\frontend\Controller
 * To see available variables <?php print_r(get_defined_vars()); ?>
 */
?>
<?php if (!empty($items)) { ?>
<ul class="nav navbar-nav menu-top">
  <?php foreach ($items as $item) { ?>
  <?php if ($item['depth'] == $depth) { ?>
  <li class="<?php echo empty($item['active']) ? '' : 'active'; ?>">
    <?php $children = array(); ?>
    <?php foreach ($items as $child) { ?>
    <?php if ($child['depth'] == ($depth + 1) && in_array($item['category_id'], $child['parents'])) { ?>
    <?php $children[] = $child; ?>
    <?php } ?>
    <?php } ?>
    <a href="<?php echo $this->e($item['url']); ?>"<?php echo empty($children) ? '' : ' data-toggle="dropdown"'; ?>>
      <?php echo $this->e($item['title']); ?>
    </a>
    <?php if (!empty($children)) { ?>
    <ul class="dropdown-menu">
      <?php foreach ($children as $child) { ?>
      <li class="<?php echo empty($item['active']) ? '' : 'active'; ?>">
        <a href="<?php echo $this->e($child['url']); ?>">
          <?php echo $this->e($child['title']); ?>
        </a>
      </li>
      <?php } ?>
    </ul>
    <?php } ?>
  </li>
  <?php } ?>
  <?php } ?>
</ul>
<?php } ?>