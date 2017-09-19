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
<?php if (!empty($aliases) || $_filtering) { ?>
<form method="post">
  <input type="hidden" name="token" value="<?php echo $_token; ?>">
  <?php if ($this->access('alias_delete')) { ?>
  <div class="form-inline bulk-actions">
    <div class="input-group">
      <span class="input-group-addon"><?php echo $this->text('With selected'); ?></span>
      <select name="action[name]" class="form-control" onchange="GplCart.action(event);">
        <option value=""><?php echo $this->text('- do action -'); ?></option>
        <option value="delete" data-confirm="<?php echo $this->text('Are you sure? It cannot be undone!'); ?>">
          <?php echo $this->text('Delete'); ?>
        </option>
      </select>
      <span class="input-group-btn hidden-js">
        <button class="btn btn-default" name="action[submit]" value="1"><?php echo $this->text('OK'); ?></button>
      </span>
    </div>
  </div>
  <?php } ?>
  <div class="table-responsive">
    <table class="table aliases">
      <thead>
        <tr>
          <th>
            <input type="checkbox" id="select-all" value="1">
          </th>
          <th>
            <a href="<?php echo $sort_alias_id; ?>">
              <?php echo $this->text('ID'); ?> <i class="fa fa-sort"></i>
            </a>
          </th>
          <th>
            <a href="<?php echo $sort_alias; ?>">
              <?php echo $this->text('Alias'); ?> <i class="fa fa-sort"></i>
            </a>
          </th>
          <th>
            <a href="<?php echo $sort_id_key; ?>">
              <?php echo $this->text('Entity type'); ?> <i class="fa fa-sort"></i>
            </a>
          </th>
          <th>
            <a href="<?php echo $sort_id_value; ?>">
              <?php echo $this->text('Entity ID'); ?> <i class="fa fa-sort"></i>
            </a>
          </th>
          <th></th>
        </tr>
        <tr class="filters active hidden-no-js">
          <th></th>
          <th></th>
          <th>
            <input class="form-control" name="alias" value="<?php echo $filter_alias; ?>" placeholder="<?php echo $this->text('Any'); ?>">
          </th>
          <th>
            <select name="id_key" class="form-control">
              <option value=""><?php echo $this->text('Any'); ?></option>
              <?php foreach ($id_keys as $id_key) { ?>
              <option value="<?php echo $this->e($id_key); ?>"<?php echo $filter_id_key == $id_key ? ' selected' : '' ?>>
                <?php echo $this->e($id_key); ?>
              </option>
              <?php } ?>
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
        <?php if ($_filtering && empty($aliases)) { ?>
        <tr>
          <td colspan="6">
            <?php echo $this->text('No results'); ?>
            <a href="<?php echo $this->url($_path); ?>" class="clear-filter"><?php echo $this->text('Reset'); ?></a>
          </td>
        </tr>
        <?php } ?>
        <?php foreach ($aliases as $id => $alias) { ?>
        <tr>
          <td class="middle">
            <input type="checkbox" class="select-all" name="action[items][]" value="<?php echo $id; ?>">
          </td>
          <td class="middle">
            <?php echo $this->e($id); ?>
          </td>
          <td class="middle">
            <?php echo $this->e($alias['alias']); ?>
          </td>
          <td class="middle">
            <?php echo $this->e($alias['entity']); ?>
          </td>
          <td class="middle">
            <?php echo $this->e($alias['id_value']); ?>
          </td>
          <td></td>
        </tr>
        <?php } ?>
      </tbody>
    </table>
    <?php if (!empty($_pager)) { ?>
    <?php echo $_pager; ?>
    <?php } ?>
  </div>
</form>
<?php } else { ?>
<?php echo $this->text('There are no items yet'); ?>
<?php } ?>
