<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<?php if ($this->access('currency_add')) { ?>
<div class="btn-toolbar actions">
  <a href="<?php echo $this->url('admin/settings/currency/add'); ?>" class="btn btn-default">
    <?php echo $this->text('Add'); ?>
  </a>
</div>
<?php } ?>
<div class="table-responsive">
  <table class="table currencies">
    <thead>
      <tr>
        <th><?php echo $this->text('Name'); ?></th>
        <th><?php echo $this->text('Code'); ?></th>
        <th><?php echo $this->text('Symbol'); ?></th>
        <th><?php echo $this->text('Conversion rate'); ?></th>
        <th><?php echo $this->text('Default'); ?></th>
        <th><?php echo $this->text('Enabled'); ?></th>
        <th><?php echo $this->text('Updated'); ?></th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($currencies as $code => $currency) { ?>
      <tr>
        <td class="middle"><?php echo $this->e($currency['name']); ?></td>
        <td class="middle"><?php echo $this->e($code); ?></td>
        <td class="middle"><?php echo $this->e($currency['symbol']); ?></td>
        <td class="middle"><?php echo $this->e($currency['conversion_rate']); ?></td>
        <td class="middle">
          <?php if ($default_currency == $code) { ?>
          <i class="fa fa-check-square-o"></i>
          <?php } else { ?>
          <i class="fa fa-square-o"></i>
          <?php } ?>
        </td>
        <td class="middle">
          <?php if (!empty($currency['status']) || $default_currency == $code) { ?>
          <i class="fa fa-check-square-o"></i>
          <?php } else { ?>
          <i class="fa fa-square-o"></i>
          <?php } ?>
        </td>
        <td class="middle"><?php echo empty($currency['modified']) ? '--' : $this->date($currency['modified']); ?></td>
        <td class="middle">
         <?php if ($this->access('currency_edit')) { ?>
          <a href="<?php echo $this->url("admin/settings/currency/edit/$code"); ?>" title="" class="edit">
            <?php echo $this->lower($this->text('Edit')); ?>
          </a>
          <?php } ?>
        </td>
      </tr>
      <?php } ?>
    </tbody>
  </table>
</div>