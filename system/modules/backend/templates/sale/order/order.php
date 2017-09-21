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
<form class="form-horizontal" method="post">
  <input type="hidden" name="token" value="<?php echo $_token; ?>">
  <div class="row">
    <div class="col-md-6">
    <?php echo $pane_summary; ?>
    <?php echo $pane_customer; ?>
    <?php echo $pane_log; ?>
    </div>
    <div class="col-md-6">
      <?php echo $pane_components; ?>
      <?php echo $pane_shipping_address; ?>
      <?php echo $pane_payment_address; ?>
      <?php echo $pane_comment; ?>
      <div class="btn-toolbar">
        <?php if ($this->access('order_delete')) { ?>
        <button class="btn btn-danger" name="delete" value="1" onclick="return confirm('<?php echo $this->text('Are you sure? It cannot be undone!'); ?>');"><?php echo $this->text('Delete'); ?></button>
        <?php } ?>
        <a href="#" class="btn btn-default" onclick="window.print(); return false;">
          <?php echo $this->text('Print'); ?>
        </a>
        <?php if ($this->access('order_add') && $this->access('order_edit')) { ?>
        <button class="btn btn-default" name="clone" value="1" onclick="return confirm('<?php echo $this->text('Are you sure? A new order will be created, this order will be canceled'); ?>');"><?php echo $this->text('Clone'); ?></button>
        <?php } ?>
      </div>
    </div>
  </div>
</form>