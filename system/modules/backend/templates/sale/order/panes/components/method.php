<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<?php if (empty($method)) { ?>
<tr>
  <td colspan="2"><span class="text-danger"><?php echo $this->text('Unknown'); ?></span></td>
</tr>
<?php } else { ?>
<tr class="active">
  <td colspan="2"><?php echo $this->escape($method['cart']['type']); ?></td>
</tr>
<tr>
  <td>
    <?php if (empty($method['name'])) { ?>
    <span class="text-danger"><?php echo $this->text('Unknown'); ?></span>
    <?php } else { ?>
    <?php echo $this->text($method['name']); ?>
    <?php if(!empty($method['description'])) { ?>
    <br><span class="small text-muted"><?php echo $this->filter($method['description']); ?></span>
    <?php } ?>
    <?php } ?>
  </td>
  <td>
    <?php echo $this->escape($method['price_formatted']); ?>
  </td>
</tr>
<?php } ?>
