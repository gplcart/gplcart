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
<?php if (!empty($libraries) || $_filtering) { ?>
<div class="btn-toolbar actions">
  <a class="btn btn-default" href="<?php echo $this->url('', array('refresh' => 1, 'token' => $_token)); ?>" class="refresh">
    <?php echo $this->text('Clear cache'); ?>
  </a>
</div>
<div class="table-responsive">
  <table class="table libraries">
    <thead>
      <tr>
        <th><a href="<?php echo $sort_id; ?>"><?php echo $this->text('ID'); ?> <i class="fa fa-sort"></i></a></th>
        <th><a href="<?php echo $sort_name; ?>"><?php echo $this->text('Name'); ?> <i class="fa fa-sort"></i></a></th>
        <th><a href="<?php echo $sort_type; ?>"><?php echo $this->text('Type'); ?> <i class="fa fa-sort"></i></a></th>
        <th><a href="<?php echo $sort_version; ?>"><?php echo $this->text('Version'); ?> <i class="fa fa-sort"></i></a></th>
        <th><a href="<?php echo $sort_has_dependencies; ?>"><?php echo $this->text('Dependencies'); ?> <i class="fa fa-sort"></i></a></th>
        <th><a href="<?php echo $sort_status; ?>"><?php echo $this->text('Status'); ?> <i class="fa fa-sort"></i></a></th>
        <th></th>
      </tr>
      <tr class="filters active hidden-no-js">
        <th></th>
        <th><input class="form-control" name="name" value="<?php echo $filter_name; ?>" placeholder="<?php echo $this->text('Any'); ?>"></th>
        <th>
          <select name="type" class="form-control">
            <option value=""><?php echo $this->text('Any'); ?></option>
            <?php foreach ($types as $type_id => $type_name) { ?>
            <option value="<?php echo $this->e($type_id); ?>"<?php echo $filter_type == $type_id ? ' selected' : '' ?>>
              <?php echo $this->e($type_name); ?>
            </option>
            <?php } ?>
          </select>
        </th>
        <th>
          <input class="form-control" name="version" value="<?php echo $filter_version; ?>" placeholder="<?php echo $this->text('Any'); ?>">
        </th>
        <th>
          <select class="form-control" name="has_dependencies">
            <option value=""><?php echo $this->text('Any'); ?></option>
            <option value="1"<?php echo $filter_has_dependencies === '1' ? ' selected' : ''; ?>>
              <?php echo $this->text('Yes'); ?>
            </option>
            <option value="0"<?php echo $filter_has_dependencies === '0' ? ' selected' : ''; ?>>
              <?php echo $this->text('No'); ?>
            </option>
          </select>
        </th>
        <th>
          <select class="form-control" name="status">
            <option value=""><?php echo $this->text('Any'); ?></option>
            <option value="1"<?php echo $filter_status === '1' ? ' selected' : ''; ?>>
              <?php echo $this->text('OK'); ?>
            </option>
            <option value="0"<?php echo $filter_status === '0' ? ' selected' : ''; ?>>
              <?php echo $this->text('Error'); ?>
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
      <?php if ($_filtering && empty($libraries)) { ?>
      <tr>
        <td colspan="7">
          <?php echo $this->text('No results'); ?>
          <a href="<?php echo $this->url($_path); ?>" class="clear-filter"><?php echo $this->text('Reset'); ?></a>
        </td>
      </tr>
      <?php } ?>
      <?php foreach ($libraries as $library_id => $library) { ?>
      <tr class="library<?php echo empty($library['errors']) ? '' : ' bg-danger'; ?>">
        <td><?php echo $this->e($library_id); ?></td>
        <td>
          <a data-toggle="collapse" href="#details-<?php echo $this->e($library_id); ?>">
            <?php echo $this->text($library['name']); ?>
          </a>
        </td>
        <td><?php echo $this->e($library['type']); ?></td>
        <td>
          <?php echo $this->e($library['version']); ?>
        </td>
        <td>
          <?php if (empty($library['requires']) && empty($library['required_by'])) { ?>
          <?php echo $this->text('No'); ?>
          <?php } else { ?>
          <a data-toggle="collapse" href="#details-<?php echo $this->e($library_id); ?>">
            <?php echo $this->text('Yes'); ?>
          </a>
          <?php } ?>
        </td>
        <td>
          <?php if (empty($library['errors'])) { ?>
          <?php echo $this->text('OK'); ?>
          <?php } else { ?>
          <a data-toggle="collapse" href="#details-<?php echo $this->e($library_id); ?>">
            <?php echo $this->text('Error'); ?>
          </a>
          <?php } ?>
        </td>
        <td>
          <ul class="list-inline">
            <?php if (!empty($library['url'])) { ?>
            <li><a target="_blank" href="<?php echo $this->e($library['url']); ?>">
              <?php echo $this->text('URL'); ?></a>
            </li>
            <?php } ?>
            <?php if (!empty($library['download'])) { ?>
            <li><a href="<?php echo $this->e($library['download']); ?>">
              <?php echo $this->text('Download'); ?></a>
            </li>
            <?php } ?>
          </ul>
        </td>
      </tr>
      <tr class="collapse active" id="details-<?php echo $this->e($library_id); ?>">
        <td colspan="7">
          <?php if (!empty($library['description'])) { ?>
          <div class="description">
            <b><?php echo $this->text('Description'); ?>:</b> <?php echo $this->text($library['description']); ?>
          </div>
          <?php } ?>
          <?php if (!empty($library['vendor'])) { ?>
          <div class="vendor">
            <b><?php echo $this->text('Vendor'); ?>:</b> <?php echo $this->text($library['vendor']); ?>
          </div>
          <?php } ?>
          <b><?php echo $this->text('Directory'); ?>:</b> <?php echo $this->e($library['basepath']); ?>
          <?php if (!empty($library['requires'])) { ?>
          <div class="requires">
            <b><?php echo $this->text('Requires'); ?>:</b>
            <?php foreach ($library['requires'] as $requires_library_id => $version) { ?>
            <?php if (isset($libraries[$requires_library_id]['name'])) { ?>
            <?php echo $this->text($libraries[$requires_library_id]['name']); ?><?php echo $this->e($version); ?>,
            <?php } else { ?>
            <span class="text-danger"><?php echo $this->e($requires_library_id); ?> (<?php echo $this->text('invalid'); ?>)</span>,
            <?php } ?>
            <?php } ?>
          </div>
          <?php } ?>
          <?php if (!empty($library['required_by'])) { ?>
          <div class="required-by">
            <b><?php echo $this->text('Required by'); ?>:</b>
            <?php foreach ($library['required_by'] as $required_by_library_id => $version) { ?>
            <?php if (isset($libraries[$required_by_library_id]['name'])) { ?>
            <?php echo $this->text($libraries[$required_by_library_id]['name']); ?>,
            <?php } else { ?>
            <span class="text-danger"><?php echo $this->e($required_by_library_id); ?> (<?php echo $this->text('invalid'); ?>)</span>,
            <?php } ?>
            <?php } ?>
          </div>
          <?php } ?>
          <?php if (!empty($library['files'])) { ?>
          <div class="files">
            <b><?php echo $this->text('Files'); ?>:</b> <?php echo $this->e(implode(', ', $library['files'])); ?>
          </div>
          <?php } ?>
          <?php if (!empty($library['errors'])) { ?>
          <div class="errors">
            <b><?php echo $this->text('Error'); ?>:</b>
            <ul class="list-unstyled">
              <?php foreach ($library['errors'] as $error) { ?>
              <li>
              <?php if(empty($error[1])) { ?>
              <?php echo $this->text($error[0]); ?>
              <?php } else { ?>
              <?php echo $this->text($error[0], $error[1]); ?>
              <?php } ?>
              </li>
              <?php } ?>
            </ul>
          </div>
          <?php } ?>
        </td>
      </tr>
      <?php } ?>
    </tbody>
  </table>
</div>
<?php if (!empty($_pager)) { ?>
<?php echo $_pager; ?>
<?php } ?>
<?php } else { ?>
<?php echo $this->text('There are no items yet'); ?>
<?php } ?>