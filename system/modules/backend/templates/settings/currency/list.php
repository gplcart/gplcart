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
        <th><a href="<?php echo $sort_name; ?>"><?php echo $this->text('Name'); ?> <i class="fa fa-sort"></i></a></th>
        <th><a href="<?php echo $sort_code; ?>"><?php echo $this->text('Code'); ?> <i class="fa fa-sort"></i></a></th>
        <th><a href="<?php echo $sort_symbol; ?>"><?php echo $this->text('Symbol'); ?> <i class="fa fa-sort"></i></a></th>
        <th><a href="<?php echo $sort_conversion_rate; ?>"><?php echo $this->text('Conversion rate'); ?> <i class="fa fa-sort"></i></a></th>
        <th><a href="<?php echo $sort_status; ?>"><?php echo $this->text('Enabled'); ?> <i class="fa fa-sort"></i></a></th>
        <th><a href="<?php echo $sort_modified; ?>"><?php echo $this->text('Updated'); ?> <i class="fa fa-sort"></i></a></th>
        <th><?php echo $this->text('Default'); ?></th>
        <th><?php echo $this->text('In database'); ?></th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($currencies as $code => $currency) { ?>
      <tr>
        <td class="middle"><?php echo $this->text($currency['name']); ?></td>
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
        <td class="middle"><?php echo empty($currency['modified']) ? '--' : $this->date($currency['modified']); ?></td>
        <td class="middle">
          <?php if (!empty($currency['status']) || $default_currency == $code) { ?>
          <i class="fa fa-check-square-o"></i>
          <?php } else { ?>
          <i class="fa fa-square-o"></i>
          <?php } ?>
        </td>
        <td class="middle">
          <?php if (empty($currency['in_database'])) { ?>
          <i class="fa fa-square-o"></i>
          <?php } else { ?>
          <i class="fa fa-check-square-o"></i>
          <?php } ?>
        </td>
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
<?php if(!empty($_pager)) { ?>
<?php echo $_pager; ?>
<?php } ?>
