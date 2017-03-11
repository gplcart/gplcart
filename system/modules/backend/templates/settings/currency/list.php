<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<div class="panel panel-default">
  <div class="panel-heading clearfix">
    <?php if ($this->access('currency_add')) { ?>
    <div class="btn-group pull-right">
      <a href="<?php echo $this->url('admin/settings/currency/add'); ?>" class="btn btn-default">
        <i class="fa fa-plus"></i> <?php echo $this->text('Add'); ?>
      </a>
    </div>
    <?php } ?>
  </div>
  <div class="panel-body table-responsive">
    <table class="table table-condensed currencies">
      <thead>
        <tr>
          <th><?php echo $this->text('Name'); ?></th>
          <th><?php echo $this->text('Code'); ?></th>
          <th><?php echo $this->text('Symbol'); ?></th>
          <th><?php echo $this->text('Conversion rate'); ?></th>
          <th><?php echo $this->text('Default'); ?></th>
          <th><?php echo $this->text('Enabled'); ?></th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($currencies as $code => $currency) { ?>
        <tr>
          <td class="middle"><?php echo $this->escape($currency['name']); ?></td>
          <td class="middle"><?php echo $this->escape($code); ?></td>
          <td class="middle"><?php echo $this->escape($currency['symbol']); ?></td>
          <td class="middle"><?php echo $this->escape($currency['conversion_rate']); ?></td>
          <td class="middle"><?php echo($default_currency == $code) ? '<i class="fa fa-check-square-o"></i>' : '<i class="fa fa-square-o"></i>'; ?></td>
          <td class="middle"><?php echo (!empty($currency['status']) || $default_currency == $code) ? '<i class="fa fa-check-square-o"></i>' : '<i class="fa fa-square-o"></i>'; ?></td>
          <td class="middle">
            <?php if ($this->access('currency_edit')) { ?>
            <a href="<?php echo $this->url("admin/settings/currency/edit/$code"); ?>" title="" class="edit">
              <?php echo mb_strtolower($this->text('Edit')); ?>
            </a>
            <?php } ?>
          </td>
        </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>
</div>