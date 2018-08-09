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
<?php if($this->access('report')) { ?>
<div class="card">
  <div class="card-header">
    <?php echo $this->text($content['title']); ?>
  </div>
  <div class="card-body">
    <ul class="list-unstyled">
      <li>
        <?php echo $this->text('Orders'); ?>:
        <?php if ($this->access('order')) { ?>
        <a href="<?php echo $this->url('admin/sale/order'); ?>"><?php echo $this->e($content['data']['order_total']); ?></a>
        <?php } else { ?>
        <?php echo $this->e($content['data']['order_total']); ?>
        <?php } ?>
      </li>
      <li>
        <?php echo $this->text('Users'); ?>:
        <?php if ($this->access('user')) { ?>
        <a href="<?php echo $this->url('admin/user/list'); ?>"><?php echo $this->e($content['data']['user_total']); ?></a>
        <?php } else { ?>
        <?php echo $this->e($content['data']['user_total']); ?>
        <?php } ?>
      </li>
      <li>
        <?php echo $this->text('Reviews'); ?>:
        <?php if ($this->access('review')) { ?>
        <a href="<?php echo $this->url('admin/content/review'); ?>"><?php echo $this->e($content['data']['review_total']); ?></a>
        <?php } else { ?>
        <?php echo $this->e($content['data']['review_total']); ?>
        <?php } ?>
      </li>
      <li>
        <?php echo $this->text('Products'); ?>:
        <?php if ($this->access('product')) { ?>
        <a href="<?php echo $this->url('admin/content/product'); ?>"><?php echo $this->e($content['data']['product_total']); ?></a>
        <?php } else { ?>
        <?php echo $this->e($content['data']['product_total']); ?>
        <?php } ?>
      </li>
    </ul>
  </div>
</div>
<?php } ?>