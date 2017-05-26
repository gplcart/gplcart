<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
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
      <div class="panel panel-default hidden-print">
        <div class="panel-body">
          <?php if ($this->access('order_delete')) { ?>
          <button class="btn btn-danger" name="delete" value="1" onclick="return confirm(GplCart.text('Delete? It cannot be undone!'));"><?php echo $this->text('Delete'); ?></button>
          <?php } ?>
          <div class="btn-toolbar pull-right">
            <a href="#" class="btn btn-default" onclick="window.print(); return false;">
              <i class="fa fa-print"></i> <?php echo $this->text('Print'); ?>
            </a>
            <?php if ($this->access('order_add') && $this->access('order_edit')) { ?>
            <button class="btn btn-default" name="clone" value="1" onclick="return confirm(GplCart.text('Are you sure? A new order will be created, this order will be canceled'));"><?php echo $this->text('Clone'); ?></button>
            <?php } ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</form>