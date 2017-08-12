<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<div class="panel panel-checkout panel-default comment">
  <div class="panel-body">
    <a href="#" onclick="return false;" data-toggle="collapse" data-target="#order-comments"><?php echo $this->text('Order comments'); ?> <span class="caret"></span></a>
    <textarea name="order[comment]" id="order-comments" class="form-control<?php echo empty($order['comment']) ? ' collapse' : ''; ?>"><?php echo $this->e($order['comment']); ?></textarea>
  </div>
</div>


