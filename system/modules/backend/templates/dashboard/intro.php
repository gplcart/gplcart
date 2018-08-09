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
<h1 class="h3"><?php echo $this->text('Welcome!'); ?></h1>
<p><?php echo $this->text('Here are some extra steps to set up your store'); ?></p>
<h4><a href="<?php echo $this->url('admin/module/list'); ?>"><?php echo $this->text('Modules'); ?></a></h4>
<p><?php echo $this->text('Extend your store by installing extra modules and themes'); ?></p>
<h4>
  <a href="<?php echo $this->url('admin/content/product/add'); ?>"><?php echo $this->text('Products'); ?></a>
</h4>
<p><?php echo $this->text('Add products to sell'); ?></p>
<h4>
  <a href="<?php echo $this->url('admin/settings/store/edit/1'); ?>"><?php echo $this->text('Settings'); ?></a>
</h4>
<p><?php echo $this->text('Add company info, change logo, theme'); ?></p>
<a class="btn" href="<?php echo $this->url('', array('skip_intro' => 1)); ?>">
  <?php echo $this->text('Skip'); ?>
</a>