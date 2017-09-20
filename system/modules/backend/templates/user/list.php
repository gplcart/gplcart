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
  <?php if ($this->access('user_edit') || $this->access('user_delete') || $this->access('user_add')) { ?>
  <div class="form-inline actions">
    <?php $access_actions = false; ?>
    <?php if ($this->access('user_edit') || $this->access('user_delete')) { ?>
    <?php $access_actions = true; ?>
    <div class="input-group">
      <select name="action[name]" class="form-control" onchange="Gplcart.action(this);">
        <option value=""><?php echo $this->text('With selected'); ?></option>
        <?php if ($this->access('user_edit')) { ?>
        <option value="status|1" data-confirm="<?php echo $this->text('Are you sure?'); ?>">
          <?php echo $this->text('Status'); ?>: <?php echo $this->text('Enabled'); ?>
        </option>
        <option value="status|0" data-confirm="<?php echo $this->text('Are you sure?'); ?>">
          <?php echo $this->text('Status'); ?>: <?php echo $this->text('Disabled'); ?>
        </option>
        <?php } ?>
        <?php if ($this->access('user_delete')) { ?>
        <option value="delete" data-confirm="<?php echo $this->text('Are you sure? It cannot be undone!'); ?>">
          <?php echo $this->text('Delete'); ?>
        </option>
        <?php } ?>
      </select>
      <span class="input-group-btn hidden-js">
        <button class="btn btn-default" name="action[submit]" value="1"><?php echo $this->text('OK'); ?></button>
      </span>
    </div>
    <?php } ?>
    <?php if ($this->access('user_add')) { ?>
    <a class="btn btn-default" href="<?php echo $this->url('admin/user/add'); ?>">
      <?php echo $this->text('Add'); ?>
    </a>
    <?php } ?>
  </div>
  <?php } ?>
  <div class="table-responsive">
    <table class="table users">
      <thead>
        <tr>
          <th><input type="checkbox" onchange="Gplcart.selectAll(this);"<?php echo $access_actions ? '' : ' disabled'; ?>></th>
          <th>
            <a href="<?php echo $sort_user_id; ?>"><?php echo $this->text('ID'); ?> <i class="fa fa-sort"></i></a>
          </th>
          <th>
            <a href="<?php echo $sort_name; ?>"><?php echo $this->text('Name'); ?> <i class="fa fa-sort"></i></a>
          </th>
          <th>
            <a href="<?php echo $sort_email; ?>"><?php echo $this->text('Email'); ?> <i class="fa fa-sort"></i></a>
          </th>
          <th>
            <a href="<?php echo $sort_role_id; ?>"><?php echo $this->text('Role'); ?> <i class="fa fa-sort"></i></a>
          </th>
          <th>
            <a href="<?php echo $sort_store_id; ?>"><?php echo $this->text('Store'); ?> <i class="fa fa-sort"></i></a>
          </th>
          <th>
            <a href="<?php echo $sort_status; ?>"><?php echo $this->text('Status'); ?> <i class="fa fa-sort"></i></a>
          </th>
          <th>
            <a href="<?php echo $sort_created; ?>"><?php echo $this->text('Created'); ?> <i class="fa fa-sort"></i></a>
          </th>
          <th></th>
        </tr>
        <tr class="filters active hidden-no-js">
          <th></th>
          <th></th>
          <th>
            <input class="form-control" name="name" maxlength="255" value="<?php echo $filter_name; ?>" placeholder="<?php echo $this->text('Any'); ?>">
          </th>
          <th>
            <input class="form-control" name="email" maxlength="255" value="<?php echo $filter_email; ?>" placeholder="<?php echo $this->text('Any'); ?>">
          </th>
          <th>
            <select class="form-control" name="role_id">
              <option value=""><?php echo $this->text('Any'); ?></option>
              <?php foreach ($roles as $role_id => $role) { ?>
              <option value="<?php echo $role_id; ?>"<?php echo $filter_role_id == $role_id ? ' selected' : ''; ?>>
                <?php echo $this->e($role['name']); ?>
              </option>
              <?php } ?>
            </select>
          </th>
          <th>
            <select class="form-control" name="store_id">
              <option value=""><?php echo $this->text('Any'); ?></option>
              <?php foreach ($_stores as $store_id => $store) { ?>
              <option value="<?php echo $store_id; ?>"<?php echo $filter_store_id == $store_id ? ' selected' : ''; ?>>
                <?php echo $this->e($store['name']); ?>
              </option>
              <?php } ?>
            </select>
          </th>
          <th>
            <select class="form-control" name="status">
              <option value=""><?php echo $this->text('Any'); ?></option>
              <option value="1"<?php echo $filter_status === '1' ? ' selected' : ''; ?>>
                <?php echo $this->text('Enabled'); ?>
              </option>
              <option value="0"<?php echo $filter_status === '0' ? ' selected' : ''; ?>>
                <?php echo $this->text('Disabled'); ?>
              </option>
            </select>
          </th>
          <th></th>
          <th>
            <a href="<?php echo $this->url($_path); ?>" class="btn btn-default clear-filter" title="<?php echo $this->text('Reset filter'); ?>">
              <i class="fa fa-refresh"></i>
            </a>
            <button class="btn btn-default filter" title="<?php echo $this->text('Filter'); ?>">
              <i class="fa fa-search"></i>
            </button>
          </th>
        </tr>
      </thead>
      <tbody>
        <?php if ($_filtering && empty($users)) { ?>
        <tr>
          <td colspan="9">
            <?php echo $this->text('No results'); ?>
            <a href="<?php echo $this->url($_path); ?>" class="clear-filter"><?php echo $this->text('Reset'); ?></a>
          </td>
        </tr>
        <?php } ?>
        <?php foreach ($users as $id => $user) { ?>
        <tr>
          <?php if ($this->isSuperadmin($id) && !$this->isSuperadmin()) { ?>
          <td colspan="9" class="bg-danger"><?php echo $this->text('No access'); ?></td>
          <?php } else { ?>
          <td class="middle">
            <input type="checkbox" class="select-all" name="action[items][]" value="<?php echo $id; ?>"<?php echo $access_actions ? '' : ' disabled'; ?>>
          </td>
          <td class="middle"><?php echo $id; ?></td>
          <td class="middle">
            <?php if (empty($user['data'])) { ?>
            <?php echo $this->e($user['name']); ?>
            <?php } else { ?>
            <a href="#" onclick="return false;" data-toggle="collapse" data-target="#user-<?php echo $id; ?>">
              <?php echo $this->e($user['name']); ?>
            </a>
            <?php } ?>
          </td>
          <td class="middle"><?php echo $this->e($user['email']); ?></td>
          <td class="middle">
            <?php if (isset($roles[$user['role_id']]['name'])) { ?>
            <?php echo $this->e($roles[$user['role_id']]['name']); ?>
            <?php } else { ?>
            <span class="text-danger"><?php echo $this->text('Unknown'); ?></span>
            <?php } ?>
          </td>
          <td class="middle">
            <?php if (isset($_stores[$user['store_id']])) { ?>
            <?php echo $this->e($_stores[$user['store_id']]['name']); ?>
            <?php } else { ?>
            <span class="text-danger"><?php echo $this->text('Unknown'); ?></span>
            <?php } ?>
          </td>
          <td class="middle">
            <?php if (empty($user['status'])) { ?>
            <i class="fa fa-square-o"></i>
            <?php } else { ?>
            <i class="fa fa-check-square-o"></i>
            <?php } ?>
          </td>
          <td class="middle"><?php echo $this->date($user['created']); ?></td>
          <td class="middle">
            <ul class="list-inline">
              <li>
                <a href="<?php echo $this->e($user['url']); ?>">
                  <?php echo $this->lower($this->text('View')); ?>
                </a>
              </li>
              <?php if ($this->access('user_edit')) { ?>
              <li>
                <a href="<?php echo $this->url("admin/user/edit/$id"); ?>">
                  <?php echo $this->lower($this->text('Edit')); ?>
                </a>
              </li>
              <?php } ?>
              <?php if ($this->access('order_add')) { ?>
              <li>
                <a href="<?php echo $this->url("checkout/add/$id"); ?>">
                  <?php echo $this->lower($this->text('Add order')); ?>
                </a>
              </li>
              <?php } ?>
            </ul>
          </td>
          <?php } ?>
        </tr>
        <?php if (!empty($user['data'])) { ?>
        <tr class="collapse active" id="user-<?php echo $id; ?>">
          <td colspan="9">
            <?php if (!empty($user['data']['reset_password'])) { ?>
            <?php echo $this->text('Reset password'); ?>:<br>
            <?php if (!empty($user['data']['reset_password']['token']) && $this->access('user_edit')) { ?>
            <b><?php echo $this->text('Key'); ?>:</b>
            <a href="<?php echo $this->url('forgot', array('key' => $user['data']['reset_password']['token'], 'user_id' => $id)); ?>">
              <?php echo $this->e($user['data']['reset_password']['token']); ?>
            </a>
            <br>
            <?php } ?>
            <?php if (!empty($user['data']['reset_password']['expires'])) { ?>
            <b><?php echo $this->text('Expires'); ?>:</b> <?php echo $this->date($user['data']['reset_password']['expires']); ?>
            <?php } ?>
            <?php } ?>
          </td>
        </tr>
        <?php } ?>
        <?php } ?>
      </tbody>
    </table>
  </div>
  <?php if (!empty($_pager)) { ?>
  <?php echo $_pager; ?>
  <?php } ?>
</form>