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
<div class="panel panel-default">
  <div class="panel-heading"><?php echo $this->text('Components'); ?></div>
  <div class="panel-body">
    <div class="row">
        <div class="col-md-12">
          <table class="table table-condensed">
            <tbody>
              <?php if(!empty($order['data']['components'])) { ?>
              <?php foreach($order['data']['components'] as $component) { ?>
              <?php echo $component['rendered']; ?>
              <?php } ?>
              <?php } ?>
              <tr>
                <td><b><?php echo $this->text('Grand total'); ?></b></td>
                <td><b><?php echo $this->e($order['total_formatted']); ?></b></td>
              </tr>
            </tbody>
          </table>
        </div>
    </div>
  </div>
</div>