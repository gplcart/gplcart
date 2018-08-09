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
<?php if (!empty($languages) || $_filtering) { ?>
<?php if ($this->access('language_add')) { ?>
<div class="btn-toolbar actions">
  <a class="btn btn-outline-primary" href="<?php echo $this->url("admin/settings/language/add"); ?>">
    <?php echo $this->text('Add'); ?>
  </a>
</div>
<?php } ?>
<div class="table-responsive">
  <table class="table languages">
    <thead>
      <tr>
        <th><a href="<?php echo $sort_code; ?>"><?php echo $this->text('Code'); ?> <i class="fa fa-sort"></i></a></th>
        <th><a href="<?php echo $sort_name; ?>"><?php echo $this->text('Name'); ?> <i class="fa fa-sort"></i></a></th>
        <th><a href="<?php echo $sort_native_name; ?>"><?php echo $this->text('Native name'); ?> <i class="fa fa-sort"></i></a></th>
        <th><a href="<?php echo $sort_rtl; ?>"><?php echo $this->text('Right-to-left'); ?> <i class="fa fa-sort"></i></a></th>
        <th><a href="<?php echo $sort_default; ?>"><?php echo $this->text('Default'); ?> <i class="fa fa-sort"></i></a></th>
        <th><a href="<?php echo $sort_in_database; ?>"><?php echo $this->text('In database'); ?> <i class="fa fa-sort"></i></a></th>
        <th><a href="<?php echo $sort_status; ?>"><?php echo $this->text('Status'); ?> <i class="fa fa-sort"></i></a></th>
        <th></th>
      </tr>
      <tr class="filters active hidden-no-js">
        <th><input class="form-control" name="code" value="<?php echo $filter_code; ?>" placeholder="<?php echo $this->text('Any'); ?>"></th>
        <th><input class="form-control" name="name" value="<?php echo $filter_name; ?>" placeholder="<?php echo $this->text('Any'); ?>"></th>
        <th><input class="form-control" name="native_name" value="<?php echo $filter_native_name; ?>" placeholder="<?php echo $this->text('Any'); ?>"></th>
        <th>
          <select class="form-control" name="rtl">
            <option value=""><?php echo $this->text('Any'); ?></option>
            <option value="1"<?php echo $filter_rtl === '1' ? ' selected' : ''; ?>>
              <?php echo $this->text('Yes'); ?>
            </option>
            <option value="0"<?php echo $filter_rtl === '0' ? ' selected' : ''; ?>>
              <?php echo $this->text('No'); ?>
            </option>
          </select>
        </th>
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
          <a href="<?php echo $this->url($_path); ?>" class="btn clear-filter" title="<?php echo $this->text('Reset filter'); ?>">
            <i class="fa fa-sync"></i>
          </a>
          <button class="btn filter" title="<?php echo $this->text('Filter'); ?>">
            <i class="fa fa-search"></i>
          </button>
        </th>
      </tr>
    </thead>
    <tbody>
      <?php if ($_filtering && empty($languages)) { ?>
      <tr>
        <td colspan="8">
          <?php echo $this->text('No results'); ?>
          <a href="<?php echo $this->url($_path); ?>" class="clear-filter"><?php echo $this->text('Reset'); ?></a>
        </td>
      </tr>
      <?php } ?>
      <?php foreach ($languages as $code => $language) { ?>
      <tr data-code="<?php echo $code; ?>">
        <td class="middle"><?php echo $this->e($code); ?></td>
        <td class="middle"><?php echo $this->text($language['name']); ?></td>
        <td class="middle"><?php echo $this->e($language['native_name']); ?></td>
        <td class="middle">
          <?php if (empty($language['rtl'])) { ?>
          <i class="fa fa-square"></i>
          <?php } else { ?>
          <i class="fa fa-check-square"></i>
          <?php } ?>
        </td>
        <td class="middle">
          <?php if (empty($language['default'])) { ?>
          <i class="fa fa-square"></i>
          <?php } else { ?>
          <i class="fa fa-check-square"></i>
          <?php } ?>
        </td>
        <td class="middle">
          <?php if (empty($language['in_database'])) { ?>
          <i class="fa fa-square"></i>
          <?php } else { ?>
          <i class="fa fa-check-square"></i>
          <?php } ?>
        </td>
        <td class="middle">
          <?php if (empty($language['status'])) { ?>
          <i class="fa fa-square"></i>
          <?php } else { ?>
          <i class="fa fa-check-square"></i>
          <?php } ?>
        </td>
        <td class="middle">
          <ul class="list-inline">
            <?php if ($this->access('language_edit')) { ?>
            <li class="list-inline-item">
              <a href="<?php echo $this->url("admin/settings/language/edit/$code"); ?>">
                <?php echo $this->lower($this->text('Edit')); ?>
              </a>
            </li>
            <?php if(!empty($language['file_exists'])) { ?>
            <li class="list-inline-item">
              <a href="<?php echo $this->url('', array('refresh' => $code, 'token' => $_token)); ?>" onclick="return confirm('<?php echo $this->text('Are you sure? All compiled translations for this language will be re-created, their existing translations will be lost!'); ?>');">
                <?php echo $this->lower($this->text('Refresh')); ?>
              </a>
            </li>
            <?php } ?>
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
<?php if ($this->access('language_add')) { ?>
<a class="btn btn-outline-primary add" href="<?php echo $this->url('admin/settings/language/add'); ?>">
  <?php echo $this->text('Add'); ?>
</a>
<?php } ?>
<?php } ?>
