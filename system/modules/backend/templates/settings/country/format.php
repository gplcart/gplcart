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
  <?php $access_actions = false; ?>
  <?php if ($this->access('country_format_edit')) { ?>
  <?php $access_actions = true; ?>
  <div class="btn-toolbar actions">
    <button class="btn save" name="save" value="1">
      <?php echo $this->text('Save'); ?>
    </button>
  </div>
  <?php } ?>
  <div class="table-responsive">
    <table class="table country-format" data-sortable-input-weight="true">
      <thead>
        <tr>
          <th><?php echo $this->text('Name'); ?></th>
          <th><?php echo $this->text('Enabled'); ?></th>
          <th><?php echo $this->text('Required'); ?></th>
          <th><?php echo $this->text('Weight'); ?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($format as $name => $item) { ?>
        <?php if (isset($item['name'])) { ?>
        <tr>
          <td><?php echo $this->e($item['name']); ?></td>
          <td class="middle">
            <?php if ($name === 'country') { ?>
            <input type="checkbox" name="format[<?php echo $name; ?>][status]" value="1" checked disabled>
            <?php } else { ?>
            <input type="checkbox" name="format[<?php echo $name; ?>][status]" value="1"<?php echo empty($item['status']) ? '' : ' checked'; ?><?php echo $access_actions && empty($item['required']) ? '' : ' disabled'; ?>>
            <?php } ?>
            <input type="hidden" name="format[<?php echo $name; ?>][weight]" value="<?php echo $item['weight']; ?>">
          </td>
          <td class="middle">
            <?php if ($name === 'country') { ?>
            <input type="checkbox" name="format[<?php echo $name; ?>][required]" value="1" disabled checked>
            <?php } else { ?>
            <input type="checkbox" name="format[<?php echo $name; ?>][required]" value="1"<?php echo empty($item['required']) ? '' : ' checked'; ?><?php echo $access_actions ? '' : ' disabled'; ?>>
            <?php } ?>
          </td>
          <td class="middle">
            <?php if ($access_actions) { ?>
              <i class="fa fa-arrows handle"></i>
            <?php } ?>
            <span class="weight"><?php echo $this->e($item['weight']); ?></span>
          </td>
        </tr>
        <?php } ?>
        <?php } ?>
      </tbody>
    </table>
  </div>
</form>