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
<div class="card borderless collection collection-page">
  <h4 class="card-title"><?php echo $this->e($title); ?></h4>
  <div class="card-body">
    <div class="row">
      <ul class="list-unstyled">
        <?php foreach ($items as $item) { ?>
        <li><?php echo $item['rendered']; ?></li>
        <?php } ?>
      </ul>
    </div>
  </div>
</div>
<?php } ?>