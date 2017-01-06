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
    <table class="table routes">
      <thead>
        <tr>
          <th><?php echo $this->text('Pattern'); ?></th>
          <th><?php echo $this->text('Controller'); ?></th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($routes as $pattern => $route) { ?>
        <tr>
          <td><?php echo $this->escape($pattern); ?></td>
          <td><?php echo empty($route['handlers']['controller']) ? $this->text('Unknown') : implode('::', $route['handlers']['controller']); ?></td>
        </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>
</div>