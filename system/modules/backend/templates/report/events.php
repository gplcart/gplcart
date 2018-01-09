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
<?php if (!empty($records) || $_filtering) { ?>
<form method="post">
  <input type="hidden" name="token" value="<?php echo $_token; ?>">
  <div class="form-inline actions">
    <div class="input-group">
      <select name="action[name]" class="form-control" onchange="Gplcart.action(this);">
        <option value=""><?php echo $this->text('With selected'); ?></option>
        <option value="delete">
          <?php echo $this->text('Delete'); ?>
        </option>
      </select>
      <span class="input-group-btn hidden-js">
        <button class="btn btn-default" name="action[submit]" value="1"><?php echo $this->text('OK'); ?></button>
      </span>
    </div>
    <a class="btn btn-default" href="<?php echo $this->url('', array('clear' => true, 'token' => $_token)); ?>">
      <?php echo $this->text('Clear all'); ?>
    </a>
  </div>
  <div class="table-responsive">
    <table class="table report-events">
      <thead>
        <tr>
          <th class="middle"><input type="checkbox" onchange="Gplcart.selectAll(this);"></th>
          <th><a href="<?php echo $sort_text; ?>"><?php echo $this->text('Message'); ?> <i class="fa fa-sort"></i></a></th>
          <th><a href="<?php echo $sort_type; ?>"><?php echo $this->text('Type'); ?> <i class="fa fa-sort"></i></a></th>
          <th><a href="<?php echo $sort_severity; ?>"><?php echo $this->text('Severity'); ?> <i class="fa fa-sort"></i></a></th>
          <th><a href="<?php echo $sort_created; ?>"><?php echo $this->text('Created'); ?> <i class="fa fa-sort"></i></a></th>
          <th></th>
        </tr>
        <tr class="filters active">
          <th></th>
          <th>
            <input class="form-control" name="text" value="<?php echo $filter_text; ?>" placeholder="<?php echo $this->text('Any'); ?>">
          </th>
          <th>
            <select name="type" class="form-control">
              <option value=""><?php echo $this->text('Any'); ?></option>
              <?php foreach ($types as $type) { ?>
              <option value="<?php echo $type; ?>"<?php echo $type == $filter_type ? ' selected' : ''; ?>>
              <?php echo $type; ?>
              </option>
              <?php } ?>
            </select>
          </th>
          <th>
            <select name="severity" class="form-control">
              <option value=""><?php echo $this->text('Any'); ?></option>
              <?php foreach ($severities as $severity => $severity_name) { ?>
              <option value="<?php echo $severity; ?>"<?php echo $severity == $filter_severity ? ' selected' : ''; ?>>
              <?php echo $severity_name; ?>
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
        <?php if ($_filtering && empty($records)) { ?>
        <tr>
          <td class="middle" colspan="6">
            <?php echo $this->text('No results'); ?>
            <a href="<?php echo $this->url($_path); ?>" class="clear-filter"><?php echo $this->text('Reset'); ?></a>
          </td>
        </tr>
        <?php } ?>
        <?php foreach ($records as $record) { ?>
        <tr>
          <td class="middle">
            <input type="checkbox" class="select-all" name="action[items][]" value="<?php echo $record['log_id']; ?>">
          </td>
          <td>
            <a href="#" onclick="return false;" data-toggle="collapse" data-target="#message-<?php echo $record['log_id']; ?>">
              <?php echo $this->e(strip_tags($record['summary'])); ?>
            </a>
          </td>
          <td><?php echo $this->e($record['type']); ?></td>
          <td>
            <span class="label label-<?php echo $record['severity']; ?>">
              <?php echo $this->e($record['severity_text']); ?>
            </span>
          </td>
          <td><?php echo $record['created']; ?></td>
          <td></td>
        </tr>
        <tr class="collapse active" id="message-<?php echo $record['log_id']; ?>">
          <td colspan="6">
            <ul class="list-unstyled">
              <li><b><?php echo $this->text('Message'); ?></b> : <?php echo $this->filter($record['text']); ?></li>
              <?php if (!empty($record['data']['file'])) { ?>
              <li><b><?php echo $this->text('File'); ?></b> : <?php echo $this->e($record['data']['file']); ?></li>
              <?php } ?>
              <?php if (!empty($record['data']['line'])) { ?>
              <li><b><?php echo $this->text('Line'); ?></b> : <?php echo $this->e($record['data']['line']); ?></li>
              <?php } ?>
              <?php if (!empty($record['data']['code'])) { ?>
              <li><b><?php echo $this->text('Code'); ?></b> : <?php echo $this->e($record['data']['code']); ?></li>
              <?php } ?>
              <?php if (!empty($record['data']['backtrace'])) { ?>
              <li>
                <b><?php echo $this->text('Backtrace'); ?></b>
                <ol>
                <?php foreach($record['data']['backtrace'] as $line) { ?>
                  <li><?php echo $this->e($line); ?></li>
                <?php } ?>
                </ol>
              </li>
              <?php } ?>
            </ul>
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