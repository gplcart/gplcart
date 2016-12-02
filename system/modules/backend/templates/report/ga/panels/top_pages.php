<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<div class="panel panel-default">
  <div class="panel-heading"><?php echo $this->text('Top pages'); ?></div>
  <div class="panel-body table-responsive">
    <?php if (!empty($items)) { ?>
    <table class="table ga-top-pages">
      <thead>
        <tr>
          <th><?php echo $this->text('Url'); ?></th>
          <th><?php echo $this->text('Page views'); ?></th>
          <th><?php echo $this->text('Unique page views'); ?></th>
          <th><?php echo $this->text('Time on page'); ?></th>
          <th><?php echo $this->text('Bounces'); ?></th>
          <th><?php echo $this->text('Entrances'); ?></th>
          <th><?php echo $this->text('Exits'); ?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($items as $item) { ?>
        <tr>
          <td>
            <?php if (isset($item['url'])) { ?>
            <a target="_blank" href="http://<?php echo $this->escape($item['url']); ?>"><?php echo $this->escape($item[0] . $item[1]); ?></a>
            <?php } else { ?>
            <?php echo $item[1]; ?>
            <?php } ?>
          </td>
          <td><?php echo $this->escape($item[2]); ?></td>
          <td><?php echo $this->escape($item[3]); ?></td>
          <td><?php echo $this->escape($item[4]); ?></td>
          <td><?php echo $this->escape($item[5]); ?></td>
          <td><?php echo $this->escape($item[6]); ?></td>
          <td><?php echo $this->escape($item[7]); ?></td>
        </tr>
        <?php } ?>
      </tbody>
    </table>
    <?php } else { ?>
    <?php echo $this->text('No data available'); ?>
    <?php } ?>
  </div>
</div>