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
<?php $this->jstext('Are you sure?'); ?>
<?php if (empty($languages)) { ?>
<?php echo $this->text('There are no items yet'); ?>
<?php if ($this->access('language_add')) { ?>
<a class="btn btn-default" href="<?php echo $this->url("admin/settings/language/add"); ?>">
  <?php echo $this->text('Add'); ?>
</a>
<?php } ?>
<?php } else { ?>
<?php if ($this->access('language_add')) { ?>
<div class="btn-toolbar actions">
  <a class="btn btn-default" href="<?php echo $this->url("admin/settings/language/add"); ?>">
    <?php echo $this->text('Add'); ?>
  </a>
</div>
<?php } ?>
<div class="table-responsive">
  <table class="table languages">
    <thead>
      <tr>
        <th><?php echo $this->text('Name'); ?></th>
        <th><?php echo $this->text('Native name'); ?></th>
        <th><?php echo $this->text('Code'); ?></th>
        <th><?php echo $this->text('Right-to-left'); ?></th>
        <th><?php echo $this->text('Default'); ?></th>
        <th><?php echo $this->text('Enabled'); ?></th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($languages as $code => $language) { ?>
      <tr data-code="<?php echo $code; ?>">
        <td class="middle"><?php echo $this->e($language['name']); ?></td>
        <td class="middle"><?php echo $this->e($language['native_name']); ?></td>
        <td class="middle"><?php echo $this->e($code); ?></td>
        <td class="middle">
          <?php if (empty($language['rtl'])) { ?>
          <i class="fa fa-square-o"></i>
          <?php } else { ?>
          <i class="fa fa-check-square-o"></i>
          <?php } ?>
        </td>
        <td class="middle">
          <?php if (empty($language['default'])) { ?>
          <i class="fa fa-square-o"></i>
          <?php } else { ?>
          <i class="fa fa-check-square-o"></i>
          <?php } ?>
        </td>
        <td class="middle">
          <?php if (empty($language['status'])) { ?>
          <i class="fa fa-square-o"></i>
          <?php } else { ?>
          <i class="fa fa-check-square-o"></i>
          <?php } ?>
        </td>
        <td class="middle">
          <ul class="list-inline">
            <?php if ($this->access('language_edit')) { ?>
            <li>
              <a href="<?php echo $this->url("admin/settings/language/edit/$code"); ?>">
                <?php echo $this->lower($this->text('Edit')); ?>
              </a>
            </li>
            <li>
              <a href="<?php echo $this->url('', array('refresh' => $code, 'token' => $_token)); ?>" onclick="return confirm('<?php echo $this->text('Are you sure? All compiled translations for this language will be re-created, their existing translations will be lost!'); ?>');">
                <?php echo $this->lower($this->text('Refresh')); ?>
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
<?php } ?>
