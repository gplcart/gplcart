<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 * 
 * To see available variables: <?php print_r(get_defined_vars()); ?>
 * To see the current controller object: <?php print_r($this); ?>
 * To call a controller method: <?php $this->exampleMethod(); ?>
 */
?>
<div class="panel panel-default">
  <div class="panel-heading"><?php echo $this->text('Shipping'); ?></div>
  <div class="panel-body">
    <div class="form-group">
      <div class="col-md-12">
        <?php foreach ($shipping_methods as $method_id => $method) { ?>
        <div class="radio">
          <label>
            <?php if (!empty($method['image'])) { ?>
            <img class="img-responsive" src="<?php echo $this->escape($method['image']); ?>">
            <?php } ?>
            <input type="radio" name="order[shipping]" value="<?php echo $method_id; ?>"<?php echo (isset($order['shipping']) && $order['shipping'] == $method_id) ? ' checked' : ''; ?>>
            <?php echo $this->escape($method['title']); ?>
          </label>
        </div>
        <?php } ?>
        <?php if ($this->error('shipping', true) && !is_array($this->error('shipping'))) { ?>
        <div class="alert alert-danger alert-dismissible">
          <button type="button" class="close" data-dismiss="alert">
            <span aria-hidden="true">&times;</span>
          </button>
          <?php echo $this->error('shipping'); ?>
        </div>
        <?php } ?>
      </div>
    </div>
  </div>
</div>