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
<div class="card">
  <div class="card-header"><?php echo $this->text('Shipping address'); ?></div>
  <div class="card-body">
    <?php if (empty($order['address_translated']['shipping'])) { ?>
    <?php echo $this->text('Uknown'); ?>
    <?php } else { ?>
    <div class="row">
      <div class="col-md-12">
        <table class="table table-sm">
          <?php foreach ($order['address_translated']['shipping'] as $key => $value) { ?>
          <tr>
            <td><?php echo $this->e($key); ?></td>
            <td><?php echo $this->e($value); ?></td>
          </tr>
          <?php } ?>
        </table>
      </div>
    </div>
    <?php } ?>
  </div>
</div>