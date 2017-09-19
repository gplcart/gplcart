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
<?php if (!empty($values)) { ?>
<form method="post">
  <input type="hidden" name="token" value="<?php echo $_token; ?>">
  <?php if ($this->access('field_value_delete') || $this->access('field_value_add')) { ?>
  <div class="form-inline bulk-actions">
    <?php $access_actions = false; ?>
    <?php if ($this->access('field_value_delete')) { ?>
    <?php $access_actions = true; ?>
    <div class="input-group">
      <select name="action[name]" class="form-control" onchange="GplCart.action(event);">
        <option value=""><?php echo $this->text('With selected'); ?></option>
        <option value="delete" data-confirm="<?php echo $this->text('Are you sure? It cannot be undone!'); ?>">
          <?php echo $this->text('Delete'); ?>
        </option>
      </select>
      <span class="input-group-btn hidden-js">
        <button class="btn btn-default" name="action[submit]" value="1"><?php echo $this->text('OK'); ?></button>
      </span>
    </div>
    <?php } ?>
    <?php if ($this->access('field_value_add')) { ?>
    <a class="btn btn-default add" href="<?php echo $this->url("admin/content/field/value/{$field['field_id']}/add"); ?>">
      <?php echo $this->text('Add'); ?>
    </a>
    <?php } ?>
  </div>
  <?php } ?>
  <div class="table-responsive">
    <table class="table field-values" data-sortable-weight="true">
      <thead>
        <tr>
          <th><input type="checkbox" id="select-all" value="1"<?php echo $access_actions ? '' : ' disabled'; ?>></th>
          <th><a href="<?php echo $sort_field_value_id; ?>"><?php echo $this->text('ID'); ?> <i class="fa fa-sort"></i></a></th>
          <th><a href="<?php echo $sort_title; ?>"><?php echo $this->text('Title'); ?> <i class="fa fa-sort"></i></a></th>
          <th><a href="<?php echo $sort_image; ?>"><?php echo $this->text('Image'); ?> <i class="fa fa-sort"></i></a></th>
          <th><a href="<?php echo $sort_color; ?>"><?php echo $this->text('Color'); ?> <i class="fa fa-sort"></i></a></th>
          <th><a href="<?php echo $sort_weight; ?>"><?php echo $this->text('Weight'); ?> <i class="fa fa-sort"></i></a></th>
          <?php if ($this->access('field_value_edit')) { ?>
          <th></th>
          <?php } ?>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($values as $value) { ?>
        <tr data-id="<?php echo $value['field_value_id']; ?>">
          <td class="middle">
            <input type="checkbox" class="select-all" name="action[items][]" value="<?php echo $value['field_value_id']; ?>"<?php echo $access_actions ? '' : ' disabled'; ?>>
          </td>
          <td class="middle field-value-id"><?php echo $value['field_value_id']; ?></td>
          <td class="middle title"><?php echo $this->truncate($this->e($value['title'])); ?></td>
          <td class="middle image">
            <div class="view thumb">
              <?php if (!empty($value['thumb'])) { ?>
              <img class="img-rounded" src="<?php echo $this->e($value['thumb']); ?>">
              <?php } ?>
            </div>
          </td>
          <td class="middle color">
            <?php if ($value['color']) { ?>
            <div class="btn btn-default" style="background:<?php echo $this->e($value['color']); ?>;"></div>
            <?php } ?>
          </td>
          <td class="middle weight">
            <?php if ($this->access('field_value_edit')) { ?>
            <i class="fa fa-arrows handle"></i>
            <?php } ?>
            <span class="weight"><?php echo $this->e($value['weight']); ?></span>
          </td>
          <?php if ($this->access('field_value_edit')) { ?>
          <td class="middle">
            <a href="<?php echo $this->url->get("admin/content/field/value/{$value['field_id']}/{$value['field_value_id']}/edit"); ?>" class="edit">
              <?php echo $this->lower($this->text('Edit')); ?>
            </a>
          </td>
          <?php } ?>
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
<?php echo $this->text('There are no items yet'); ?>&nbsp;
<?php if ($this->access('field_value_add')) { ?>
<a class="btn btn-default" href="<?php echo $this->url("admin/content/field/value/{$field['field_id']}/add"); ?>">
  <?php echo $this->text('Add'); ?>
</a>
<?php } ?>
<?php } ?>