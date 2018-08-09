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
<?php if (!empty($product['field_value_labels']['attribute'])) { ?>
<div class="card borderless field-attributes">
  <div class="card-header"><h4 class="card-title"><?php echo $this->text('Specs'); ?></h4></div>
  <div class="card-body">
    <table class="table">
      <tbody>
        <?php foreach ($product['field_value_labels']['attribute'] as $field_id => $labels) { ?>
        <tr>
          <th scope="row"><?php echo $this->e($product['fields']['attribute'][$field_id]['title']); ?></th>
          <td><?php echo $this->e(implode(',', $labels)); ?></td>
        </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>
</div>
<?php } ?>

