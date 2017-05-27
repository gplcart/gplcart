<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<?php if (!empty($addresses) || $_filtering) { ?>
<div class="panel panel-default">
  <div class="panel-heading clearfix">
    <div class="btn-group pull-left">
      <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
        <span class="caret"></span>
      </button>
      <?php $access_actions = false; ?>
      <?php if ($this->access('address_delete')) { ?>
      <?php $access_actions = true; ?>
      <ul class="dropdown-menu">
        <li>
          <a data-action="delete" data-action-confirm="<?php echo $this->text('Are you sure? It cannot be undone!'); ?>" href="#">
            <?php echo $this->text('Delete'); ?>
          </a>
        </li>
      </ul>
      <?php } ?>
    </div>
  </div>
  <div class="panel-body table-responsive">
    <table class="table table-condensed addresses">
      <thead>
        <tr>
          <th><input type="checkbox" id="select-all" value="1"<?php echo $access_actions ? '' : ' disabled'; ?>></th>
          <th><a href="<?php echo $sort_address_id; ?>"><?php echo $this->text('ID'); ?> <i class="fa fa-sort"></i></a></th>
          <th><a href="<?php echo $sort_user_id; ?>"><?php echo $this->text('User'); ?> <i class="fa fa-sort"></i></a></th>
          <th><a href="<?php echo $sort_full_name; ?>"><?php echo $this->text('Full name'); ?> <i class="fa fa-sort"></i></a></th>
          <th><a href="<?php echo $sort_address_1; ?>"><?php echo $this->text('Address'); ?> <i class="fa fa-sort"></i></a></th>
          <th><a href="<?php echo $sort_city_id; ?>"><?php echo $this->text('City'); ?> <i class="fa fa-sort"></i></a></th>
          <th><a href="<?php echo $sort_phone; ?>"><?php echo $this->text('Phone'); ?> <i class="fa fa-sort"></i></a></th>
          <th></th>
        </tr>
        <tr class="filters active">
          <th></th>
          <th></th>
          <th>
            <input class="form-control" data-autocomplete-source="user" name="user_email" maxlength="255" value="<?php echo $filter_user_email; ?>" placeholder="<?php echo $this->text('Any'); ?>">
          </th>
          <th>
            <input class="form-control" name="full_name" maxlength="255" value="<?php echo $filter_full_name; ?>" placeholder="<?php echo $this->text('Any'); ?>">
          </th>
          <th>
            <input class="form-control" name="address_1" maxlength="255" value="<?php echo $filter_address_1; ?>" placeholder="<?php echo $this->text('Any'); ?>">
          </th>
          <th>
            <input class="form-control" name="city_name" maxlength="255" value="<?php echo $filter_city_name; ?>" placeholder="<?php echo $this->text('Any'); ?>">
          </th>
          <th>
            <input class="form-control" name="phone" maxlength="255" value="<?php echo $filter_phone; ?>" placeholder="<?php echo $this->text('Any'); ?>">
          </th>
          <th>
            <button type="button" class="btn btn-default clear-filter" title="<?php echo $this->text('Reset filter'); ?>">
              <i class="fa fa-refresh"></i>
            </button>
            <button type="button" class="btn btn-default filter" title="<?php echo $this->text('Filter'); ?>">
              <i class="fa fa-search"></i>
            </button>
          </th>
        </tr>
      </thead>
      <tbody>
        <?php if ($_filtering && empty($addresses)) { ?>
        <tr>
          <td colspan="8">
            <?php echo $this->text('No results'); ?>
            <a href="#" class="clear-filter"><?php echo $this->text('Reset'); ?></a>
          </td>
        </tr>
        <?php } ?>
        <?php foreach ($addresses as $address_id => $address) { ?>
        <tr>
          <td class="middle"><input type="checkbox" class="select-all" name="selected[]" value="<?php echo $address_id; ?>"<?php echo $access_actions ? '' : ' disabled'; ?>></td>
          <td class="middle"><?php echo $address_id; ?></td>
          <td class="middle"><a href="<?php echo $this->url("account/{$address['user_id']}"); ?>"><?php echo $this->e($address['user_email']); ?></a></td>
          <td class="middle"><?php echo $this->e($this->truncate($address['full_name'], 30)); ?></td>
          <td class="middle"><?php echo $this->e($this->truncate($address['address_1'], 30)); ?></td>
          <td class="middle"><?php echo $this->e($this->truncate($address['city_name'], 30)); ?></td>
          <td class="middle"><?php echo $this->e($this->truncate($address['phone'], 30)); ?></td>
          <td>
            <a href="#address-id-<?php echo $address_id; ?>" data-address-details="<?php echo $address_id; ?>" data-toggle="collapse">
              <?php echo $this->lower($this->text('Details')); ?>
            </a>
          </td>
        </tr>
        <tr id="address-id-<?php echo $address_id; ?>" class="collapse">
          <td colspan="8">
            <div class="row">
              <div class="col-md-12">
                <table class="table table-condensed">
                  <?php foreach ($address['translated'] as $label => $value) { ?>
                  <tr>
                    <td><?php echo $this->e($label); ?></td>
                    <td><?php echo $this->e($value); ?></td>
                  </tr>
                  <?php } ?>
                </table>
              </div>
            </div>
          </td>
        </tr>
        <?php } ?>
      </tbody>
    </table>
    <?php if(!empty($_pager)) { ?>
    <?php echo $_pager; ?>
    <?php } ?>
  </div>
</div>
<?php } else { ?>
<div class="row">
  <div class="col-md-12">
    <?php echo $this->text('You have no addresses yet'); ?>
  </div>
</div>
<?php } ?>