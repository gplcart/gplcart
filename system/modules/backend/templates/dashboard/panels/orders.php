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
<?php if($this->access('order')) { ?>
<div class="card">
  <div class="card-header">
    <?php echo $this->text($content['title']); ?>
  </div>
  <div class="card-body">
    <?php if (!empty($content['data'])) { ?>
    <table class="table table-sm">
      <tbody>
        <?php foreach ($content['data'] as $item) { ?>
        <tr>
          <td>
            <?php if($this->access('order')) { ?>
            <a href="<?php echo $this->url("admin/sale/order/{$item['order_id']}"); ?>">
              <?php echo $this->text('Order #@order_id', array('@order_id' => $item['order_id'])); ?>
            </a>
            <?php } else { ?>
            <?php echo $this->text('Order #@order_id', array('@order_id' => $item['order_id'])); ?>
            <?php } ?>
          </td>
          <td>
            <?php echo $this->e($item['total_formatted']); ?>
          </td>
          <td>
            <?php echo $this->date($item['created']); ?>
          </td>
        </tr>
        <?php } ?>
      </tbody>
    </table>
    <div class="text-right">
      <a href="<?php echo $this->url('admin/sale/order'); ?>">
        <?php echo $this->text('See all'); ?>
      </a>
    </div>
    <?php } else { ?>
    <?php echo $this->text('There are no items yet'); ?>
    <?php } ?>
  </div>
</div>
<?php } ?>