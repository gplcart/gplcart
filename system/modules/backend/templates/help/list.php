<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 * @var $this \gplcart\core\controllers\backend\Controller
 * To see available variables <?php print_r(get_defined_vars()); ?>
 */
?>
<?php if (empty($items)) { ?>
<?php echo $this->text('There are no items yet'); ?>
<?php } else { ?>
<ul class="list-unstyled">
  <?php foreach ($items as $item) { ?>
  <li>
    <a href="<?php echo $this->url("admin/help/{$item['hash']}"); ?>">
      <?php echo $this->e($item['title']); ?>
    </a>
  </li>
  <?php } ?>
</ul>
<?php } ?>

