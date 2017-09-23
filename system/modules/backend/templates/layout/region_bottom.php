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
<footer class="footer hidden-print">
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-6">
        <p class="text-muted small">
          &copy; <?php echo date('Y') == 2015 ? date('Y') : '2015 - ' . date('Y'); ?>
          <a href="http://gplcart.com"><?php echo $this->text('GPLCart'); ?></a> v<?php echo gplcart_version(); ?>
        </p>
      </div>
    </div>
  </div>
</footer>
<?php if(!empty($_js_bottom)) { ?>
<?php foreach ($_js_bottom as $key => $js) { ?>
<?php if (!empty($js['text'])) { ?>
<script><?php echo $js['asset']; ?></script>
<?php } else { ?>
<script src="<?php echo $this->url($js['asset'], array('v' => $js['version']), false, true); ?>"></script>
<?php } ?>
<?php } ?>
<?php } ?>