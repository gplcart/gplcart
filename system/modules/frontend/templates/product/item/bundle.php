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
<div class="bundle-item">
  <div class="image" title="<?php echo $this->e($item['title']); ?>">
    <img src="<?php echo $this->e($item['thumb']); ?>" alt="<?php echo $this->e($item['title']); ?>">
  </div>
  <div class="title"><?php echo $this->e($item['title']); ?></div>
</div>