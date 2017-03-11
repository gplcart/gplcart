<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<div class="panel panel-default">
  <div class="panel-body table-responsive">
    <table class="table table-condensed filters">
      <thead>
        <tr>
          <th><?php echo $this->text('ID'); ?></th>
          <th><?php echo $this->text('Name'); ?></th>
          <th><?php echo $this->text('Description'); ?></th>
          <th><?php echo $this->text('Role'); ?></th>
          <th><?php echo $this->text('Enabled'); ?></th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($filters as $filter_id => $filter) { ?>
        <tr>
          <td class="middle"><?php echo $this->escape($filter_id); ?></td>
          <td class="middle">
            <a href="#" onclick="return false;" data-toggle="collapse" data-target="#configuration-<?php echo $filter_id; ?>">
              <?php echo $this->escape($filter['name']); ?>
            </a>
          </td>
          <td class="middle"><?php echo empty($filter['description']) ? '' : $this->escape($filter['description']); ?></td>
          <td class="middle">
            <?php if (empty($filter['role_name'])) { ?>
            <b><?php echo $this->text('None'); ?></b>
            <?php } else { ?>
            <?php echo $this->escape($filter['role_name']); ?>
            <?php } ?>
          </td>
          <td class="middle">
            <?php if (empty($filter['status'])) { ?>
            <i class="fa fa-square-o"></i>
            <?php } else { ?>
            <i class="fa fa-check-square-o"></i>
            <?php } ?>
          </td>
          <td class="middle">
            <ul class="list-inline">
              <?php if ($this->access('filter_edit')) { ?>
              <li>
                <a href="<?php echo $this->url("admin/settings/filter/edit/$filter_id"); ?>">
                  <?php echo mb_strtolower($this->text('Edit')); ?>
                </a>
              </li>
              <?php } ?>
            </ul>
          </td>
        </tr>
        <tr class="collapse" id="configuration-<?php echo $filter_id; ?>">
          <td colspan="6">
            <pre><?php echo $this->escape($filter['config_formatted']); ?></pre>
            <p>
              <a target="_blank" href="http://htmlpurifier.org/live/configdoc/plain.html">
                <?php echo $this->text('HTML Purifier\'s configuration documentation'); ?>
              </a>
            </p>
          </td>
        </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>
</div>
