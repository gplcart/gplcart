<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<?php if (!empty($items)) { ?>
<ul class="list-unstyled">
  <?php foreach ($items as $item) { ?>
  <?php if ($item['depth'] == 0) { ?>
  <li>
    <a title="<?php echo $this->e($item['text']); ?>" href="<?php echo $this->e($item['url']); ?>"><?php echo $this->e($item['text']); ?></a>
  </li>
  <?php } ?>
  <?php } ?>
</ul>
<?php } ?>