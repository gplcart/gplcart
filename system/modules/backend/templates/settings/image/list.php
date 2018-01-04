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
<?php if (!empty($image_styles) || $_filtering) { ?>
<?php if ($this->access('image_style_add')) { ?>
<div class="btn-toolbar actions">
  <a class="btn btn-default add" href="<?php echo $this->url('admin/settings/imagestyle/add'); ?>">
    <?php echo $this->text('Add'); ?>
  </a>
</div>
<?php } ?>
<div class="table-responsive">
  <table class="table image-styles">
    <thead>
      <tr>
        <th><a href="<?php echo $sort_imagestyle_id; ?>"><?php echo $this->text('ID'); ?> <i class="fa fa-sort"></i></a></th>
        <th><a href="<?php echo $sort_name; ?>"><?php echo $this->text('Name'); ?> <i class="fa fa-sort"></i></a></th>
        <th><a href="<?php echo $sort_default; ?>"><?php echo $this->text('Default'); ?> <i class="fa fa-sort"></i></a></th>
        <th><a href="<?php echo $sort_in_database; ?>"><?php echo $this->text('In database'); ?> <i class="fa fa-sort"></i></a></th>
        <th><a href="<?php echo $sort_status; ?>"><?php echo $this->text('Status'); ?> <i class="fa fa-sort"></i></a></th>
        <th></th>
      </tr>
      <tr class="filters active hidden-no-js">
        <th></th>
        <th><input class="form-control" name="name" value="<?php echo $filter_name; ?>" placeholder="<?php echo $this->text('Any'); ?>"></th>
        <th>
          <select class="form-control" name="default">
            <option value=""><?php echo $this->text('Any'); ?></option>
            <option value="1"<?php echo $filter_default === '1' ? ' selected' : ''; ?>>
              <?php echo $this->text('Yes'); ?>
            </option>
            <option value="0"<?php echo $filter_default === '0' ? ' selected' : ''; ?>>
              <?php echo $this->text('No'); ?>
            </option>
          </select>
        </th>
        <th>
          <select class="form-control" name="in_database">
            <option value=""><?php echo $this->text('Any'); ?></option>
            <option value="1"<?php echo $filter_in_database === '1' ? ' selected' : ''; ?>>
              <?php echo $this->text('Yes'); ?>
            </option>
            <option value="0"<?php echo $filter_in_database === '0' ? ' selected' : ''; ?>>
              <?php echo $this->text('No'); ?>
            </option>
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
      <?php if ($_filtering && empty($image_styles)) { ?>
      <tr>
        <td colspan="6">
          <?php echo $this->text('No results'); ?>
          <a href="<?php echo $this->url($_path); ?>" class="clear-filter"><?php echo $this->text('Reset'); ?></a>
        </td>
      </tr>
      <?php } ?>
      <?php foreach ($image_styles as $id => $image_style) { ?>
      <tr>
        <td class="middle"><?php echo $id; ?></td>
        <td class="middle"><?php echo $this->e($image_style['name']); ?></td>
        <td class="middle">
          <?php if (empty($image_style['default'])) { ?>
          <i class="fa fa-square-o"></i>
          <?php } else { ?>
          <i class="fa fa-check-square-o"></i>
          <?php } ?>
        </td>
        <td class="middle">
          <?php if (empty($image_style['in_database'])) { ?>
          <i class="fa fa-square-o"></i>
          <?php } else { ?>
          <i class="fa fa-check-square-o"></i>
          <?php } ?>
        </td>
        <td class="middle">
          <?php if (empty($image_style['status'])) { ?>
          <i class="fa fa-square-o"></i>
          <?php } else { ?>
          <i class="fa fa-check-square-o"></i>
          <?php } ?>
        </td>
        <td class="col-md-2 middle">
          <ul class="list-inline">
            <?php if ($this->access('image_style_edit')) { ?>
            <li>
              <a href="<?php echo $this->url("admin/settings/imagestyle/edit/$id"); ?>" class="edit">
                <?php echo $this->lower($this->text('Edit')); ?>
              </a>
            </li>
            <?php } ?>
            <?php if(!empty($image_style['directory_exists'])) { ?>
            <li>
              <a href="<?php echo $this->url('', array('clear' => $id, 'token' => $_token)); ?>" class="clear">
                <?php echo $this->lower($this->text('Clear cache')); ?>
              </a>
            </li>
            <?php } ?>
          </ul>
        </td>
      </tr>
      <?php } ?>
    </tbody>
  </table>
</div>
<?php if(!empty($_pager)) { ?>
<?php echo $_pager; ?>
<?php } ?>
<?php } else { ?>
<?php echo $this->text('There are no items yet'); ?>&nbsp;
<?php if ($this->access('image_style_add')) { ?>
<a class="btn btn-default add" href="<?php echo $this->url('admin/settings/imagestyle/add'); ?>">
  <?php echo $this->text('Add'); ?>
</a>
<?php } ?>
<?php } ?>