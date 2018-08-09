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
<?php if (!empty($addresses) || $_filtering) { ?>
<form method="post">
  <input type="hidden" name="token" value="<?php echo $_token; ?>">
  <?php $access_actions = false; ?>
  <?php if ($this->access('address_delete')) { ?>
  <?php $access_actions = true; ?>
  <div class="form-inline actions">
    <div class="input-group">
      <select name="action[name]" class="form-control" onchange="Gplcart.action(this);">
        <option value=""><?php echo $this->text('With selected'); ?></option>
        <option value="delete" data-confirm="<?php echo $this->text('Are you sure? It cannot be undone!'); ?>">
          <?php echo $this->text('Delete'); ?>
        </option>
      </select>
        <button class="btn btn-secondary hidden-js" name="action[submit]" value="1"><?php echo $this->text('OK'); ?></button>
    </div>
  </div>
  <?php } ?>
  <div class="table-responsive">
    <table class="table addresses">
      <thead class="thead-light">
        <tr>
          <th><input type="checkbox" onchange="Gplcart.selectAll(this);"<?php echo $access_actions ? '' : ' disabled'; ?>></th>
          <th><a href="<?php echo $sort_address_id; ?>"><?php echo $this->text('ID'); ?> <i class="fa fa-sort"></i></a></th>
          <th><a href="<?php echo $sort_user_email; ?>"><?php echo $this->text('User'); ?> <i class="fa fa-sort"></i></a></th>
          <th><a href="<?php echo $sort_full_name; ?>"><?php echo $this->text('Full name'); ?> <i class="fa fa-sort"></i></a></th>
          <th><a href="<?php echo $sort_address_1; ?>"><?php echo $this->text('Address'); ?> <i class="fa fa-sort"></i></a></th>
          <th><a href="<?php echo $sort_city_id; ?>"><?php echo $this->text('City'); ?> <i class="fa fa-sort"></i></a></th>
          <th><a href="<?php echo $sort_phone; ?>"><?php echo $this->text('Phone'); ?> <i class="fa fa-sort"></i></a></th>
          <th></th>
        </tr>
        <tr class="filters active hidden-no-js">
          <th></th>
          <th></th>
          <th>
            <input class="form-control" name="user_email_like" maxlength="255" value="<?php echo $filter_user_email_like; ?>" placeholder="<?php echo $this->text('Any'); ?>">
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
            <a href="<?php echo $this->url($_path); ?>" class="btn btn-outline-secondary clear-filter" title="<?php echo $this->text('Reset filter'); ?>">
              <i class="fa fa-sync"></i>
            </a>
            <button class="btn btn-secondary filter" title="<?php echo $this->text('Filter'); ?>">
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
            <a href="<?php echo $this->url($_path); ?>" class="clear-filter"><?php echo $this->text('Reset'); ?></a>
          </td>
        </tr>
        <?php } ?>
        <?php foreach ($addresses as $address_id => $address) { ?>
        <tr>
          <td class="middle"><input type="checkbox" class="select-all" name="action[items][]" value="<?php echo $address_id; ?>"<?php echo $access_actions ? '' : ' disabled'; ?>></td>
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
                <table class="table table-sm table-bordered">
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
  </div>
  <?php if (!empty($_pager)) { ?>
  <?php echo $_pager; ?>
  <?php } ?>
</form>
<?php } else { ?>
<?php echo $this->text('There are no items yet'); ?>
<?php } ?>