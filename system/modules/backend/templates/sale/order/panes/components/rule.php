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
<?php if (empty($rule)) { ?>
<tr>
  <td colspan="2"><span class="text-danger"><?php echo $this->text('Unknown'); ?></span></td>
</tr>
<?php } else { ?>
<tr class="active order-component-title">
  <td colspan="2"><?php echo $this->text('Price rules'); ?></td>
</tr>
<tr>
  <td>
    <?php if (empty($rule['name'])) { ?>
    <span class="text-danger"><?php echo $this->text('Unknown'); ?></span>
    <?php } else { ?>
    <?php echo $this->text($rule['name']); ?>
    <?php } ?>
  </td>
  <td>
    <?php echo $this->e($price); ?>
  </td>
</tr>
<?php } ?>
