<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<form method="post" id="country-format" class="form-horizontal">
  <input type="hidden" name="token" value="<?php echo $token; ?>">
  <div class="panel panel-default">
    <?php $access_actions = false; ?>
    <?php if ($this->access('country_format_edit')) { ?>
    <?php $access_actions = true; ?>
    <div class="panel-heading clearfix">
      <div class="btn-toolbar pull-right">
        <button class="btn btn-default save" name="save" value="1">
          <i class="fa fa-floppy-o"></i> <?php echo $this->text('Save'); ?>
        </button>
      </div>
    </div>
    <?php } ?>
    <div class="panel-body table-responsive">
      <table class="table country-format">
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
            <td><?php echo $this->escape($item['name']); ?></td>
            <td class="middle">
              <?php if($name == 'country') { ?>
              <input type="checkbox" name="format[<?php echo $name; ?>][status]" value="1" checked disabled>
              <?php } else { ?>
              <input type="checkbox" name="format[<?php echo $name; ?>][status]" value="1"<?php echo empty($item['status']) ? '' : ' checked'; ?><?php echo $access_actions ? '' : ' disabled'; ?>>
              <?php } ?>
              <input type="hidden" name="format[<?php echo $name; ?>][weight]" value="<?php echo $item['weight']; ?>">
            </td>
            <td class="middle">
              <?php if($name == 'country') { ?>
              <input type="checkbox" name="format[<?php echo $name; ?>][required]" value="1" disabled checked>
              <?php } else { ?>
              <input type="checkbox" name="format[<?php echo $name; ?>][required]" value="1"<?php echo empty($item['status']) ? '' : ' checked'; ?><?php echo $access_actions ? '' : ' disabled'; ?>>
              <?php } ?>
            </td>
            <td class="middle">
              <?php if($access_actions) { ?>
              <i class="fa fa-arrows handle"></i>
              <?php } ?>
              <span class="weight"><?php echo $this->escape($item['weight']); ?></span>
            </td>
          </tr>
          <?php } ?>
          <?php } ?>
        </tbody>
      </table>
    </div>
  </div>
</form>