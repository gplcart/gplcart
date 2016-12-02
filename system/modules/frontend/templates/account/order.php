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
<div class="row order-details">
  <div class="col-md-12">
    <table class="table table-auto table-condensed table-striped">
      <caption><?php echo $this->text('Shipping address'); ?></caption>
      <tbody>
      <?php foreach ($shipping_address as $name => $value) { ?>
      <tr>
        <td><?php echo $this->escape($name); ?></td>
        <td><?php echo $this->escape($value); ?></td>
      </tr>
      <?php } ?>
      </tbody>
    </table>
  </div>
  <div class="col-md-12">
    <table class="table table-condensed">
      <caption><?php echo $this->text('Components'); ?></caption>
      <tbody>
        <?php if(!empty($components['cart'])) { ?>
        <?php echo $components['cart']; ?>
        <?php } ?>
        <?php if(!empty($components['rule'])) { ?>
        <?php foreach($components['rule'] as $rule_id => $rule) { ?>
        <?php echo $rule; ?>
        <?php } ?>
        <?php } ?>
        <tr>
          <td><b><?php echo $this->text('Total'); ?></b></td>
          <td><b><?php echo $order['total_formatted']; ?></b></td>
        </tr>
      </tbody>
    </table>
  </div>
</div>