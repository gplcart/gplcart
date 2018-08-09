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
<form method="post">
  <input type="hidden" name="token" value="<?php echo $_token; ?>">
  <div class="btn-toolbar actions">
    <?php if (isset($dashboard['dashboard_id'])) { ?>
    <button class="btn delete" name="delete" value="1">
      <?php echo $this->text('Reset'); ?>
    </button>
    <?php } ?>
    <button class="btn save" name="save" value="1">
      <?php echo $this->text('Save'); ?>
    </button>
  </div>
  <table class="table table-sm" data-sortable-input-weight="true">
    <thead>
      <tr>
        <th><?php echo $this->text('Panel'); ?></th>
        <th><?php echo $this->text('Enabled'); ?></th>
        <th><?php echo $this->text('Weight'); ?></th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($dashboard['data'] as $id => $handler) { ?>
      <tr>
        <td class="middle"><?php echo $this->text($handler['title']); ?></td>
        <td class="middle">
          <input type="checkbox" name="dashboard[<?php echo $id; ?>][status]" value="1"<?php echo empty($handler['status']) ? '' : ' checked'; ?>>
        </td>
        <td class="middle">
          <input type="hidden" name="dashboard[<?php echo $id; ?>][weight]" value="<?php echo $this->e($handler['weight']); ?>">
          <i class="fa fa-arrows handle"></i> <span class="weight"><?php echo $this->e($handler['weight']); ?></span>
        </td>
      </tr>
      <?php } ?>
    </tbody>
  </table>
</form>

