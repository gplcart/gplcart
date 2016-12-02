<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<div class="panel panel-default">
  <div class="panel-heading"><?php echo $this->text('Top sources'); ?></div>
  <div class="panel-body table-responsive">
    <?php if (!empty($items)) { ?>
    <table class="table ga-sources">
      <thead>
        <tr>
          <th><?php echo $this->text('Source'); ?></th>
          <th><?php echo $this->text('Medium'); ?></th>
          <th><?php echo $this->text('Sessions'); ?></th>
          <th><?php echo $this->text('Page views'); ?></th>
          <th><?php echo $this->text('Session duration'); ?></th>
          <th><?php echo $this->text('Exits'); ?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($items as $item) { ?>
        <tr>
          <td><?php echo $this->escape($item[0]); ?></td>
          <td><?php echo $this->escape($item[1]); ?></td>
          <td><?php echo $this->escape($item[2]); ?></td>
          <td><?php echo $this->escape($item[3]); ?></td>
          <td><?php echo $this->escape($item[4]); ?></td>
          <td><?php echo $this->escape($item[5]); ?></td>
        </tr>
        <?php } ?>
      </tbody>
    </table>
    <?php } else { ?>
    <?php echo $this->text('No data available'); ?>
    <?php } ?>
  </div>
</div>
