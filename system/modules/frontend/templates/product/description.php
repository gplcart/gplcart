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
<div class="panel panel-default panel-borderless description">
  <div class="panel-heading"><h4 class="panel-title"><?php echo $this->text('Description'); ?></h4></div>
  <div class="panel-body">
    <?php echo $this->filter($description); ?>
  </div>
</div>
<?php } ?>