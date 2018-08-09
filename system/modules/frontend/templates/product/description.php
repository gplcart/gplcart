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
<?php if (!empty($description)) { ?>
<div class="card borderless description">
  <div class="card-header"><h4 class="card-title"><?php echo $this->text('Description'); ?></h4></div>
  <div class="card-body">
    <?php echo $this->filter($description); ?>
  </div>
</div>
<?php } ?>