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
<?php if ($categories) { ?>
<?php $depth = 0; ?>
<?php $num_at_depth = 0; ?>
<ul class="list-unstyled">
    <li>
    <?php foreach ($categories as $category) { ?>
    <?php $diffdepth = 0; ?>
    <?php if ($category['depth'] > $depth) { ?>
    <ul class="depth-<?php echo $category['depth']; ?>">
      <li class="depth-<?php echo $category['depth']; ?>">
      <?php $depth = $category['depth']; ?>
      <?php $num_at_depth = 0; ?>
      <?php } ?>
      <?php if ($category['depth'] < $depth) { ?>
      <?php $diffdepth = $depth - $category['depth']; ?>
      <?php while ($diffdepth > 0) { ?>
      </li>
    </ul>
    <?php $diffdepth--; ?>
    <?php } ?>
    <?php $depth = $category['depth']; ?>
    <?php } ?>
    <?php if ($category['depth'] == $depth && $num_at_depth > 0) { ?>
    </li><li class="depth-<?php echo $depth; ?>">
    <?php } ?>
    <a href="<?php echo $this->e($category['url']); ?>">
      <?php echo $this->e($category['title']); ?>
    </a>
    <?php $num_at_depth++; ?>
    <?php } ?>
    </li>
</ul>
<?php } ?>
