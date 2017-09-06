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
<?php if($this->access('cart')) { ?>
<div class="panel panel-default">
  <div class="panel-heading">
    <?php echo $this->e($content['title']); ?>
  </div>
  <div class="panel-body">
    <?php if (!empty($content['data'])) { ?>
    <table class="table table-condensed">
      <tbody>
        <?php foreach ($content['data'] as $item) { ?>
        <tr>
          <td>
            <?php if($this->access('product_edit')) { ?>
            <a href="<?php echo $this->url("admin/content/product/edit/{$item['product_id']}"); ?>">
              <?php echo $this->truncate($this->e($item['title']), 50); ?>
            </a>
            <?php } else { ?>
            <?php echo $this->truncate($this->e($item['title']), 50); ?>
            <?php } ?>
          </td>
          <td>
            <?php if(isset($item['user_email'])) { ?>
            <?php if($this->access('user_edit')) { ?>
            <a href="<?php echo $this->url("admin/user/edit/{$item['user_id']}"); ?>">
              <?php echo $this->truncate($this->e($item['user_email']), 50); ?>
            </a>
            <?php } else { ?>
            <?php echo $this->truncate($this->e($item['user_email']), 50); ?>
            <?php } ?>
            <?php } else { ?>
            <?php echo $this->text('Anonymous'); ?>
            <?php } ?>
          </td>
          <td>
            <?php echo $this->date($item['created']); ?>
          </td>
        </tr>
        <?php } ?>
      </tbody>
    </table>
    <div class="text-right">
      <a href="<?php echo $this->url('admin/sale/cart'); ?>">
        <?php echo $this->text('See all'); ?>
      </a>
    </div>
    <?php } else { ?>
    <?php echo $this->text('There are no items yet'); ?>
    <?php } ?>
  </div>
</div>
<?php } ?>