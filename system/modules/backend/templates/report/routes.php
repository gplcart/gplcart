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
<div class="table-responsive">
  <table class="table routes">
    <thead>
      <tr>
        <th><?php echo $this->text('Pattern'); ?></th>
        <th><?php echo $this->text('Access'); ?></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($routes as $pattern => $route) { ?>
      <tr>
        <td><?php echo $this->e($pattern); ?></td>
        <td><?php echo $this->e(implode(' + ', $route['permission_name'])); ?></td>
      </tr>
      <?php } ?>
    </tbody>
  </table>
</div>