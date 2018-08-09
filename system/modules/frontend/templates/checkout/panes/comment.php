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
<div class="card panel-checkout comment">
  <div class="card-body">
    <a href="#" onclick="return false;" data-toggle="collapse" data-target="#order-comments"><?php echo $this->text('Order comments'); ?> <span class="dropdown-toggle"></span></a>
    <textarea name="order[comment]" id="order-comments" class="form-control<?php echo empty($order['comment']) ? ' collapse' : ''; ?>"><?php echo $this->e($order['comment']); ?></textarea>
  </div>
</div>


