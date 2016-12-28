<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<footer class="footer hidden-print">
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-6">
        <p class="text-muted small">
          &copy; <?php echo (date('Y') == 2015) ? date('Y') : '2015 - ' . date('Y'); ?>
          GPL Cart All Rights Reserved. Version <?php echo GC_VERSION; ?>
        </p>	  
      </div>
    </div>
  </div>
</footer>
<?php if(!empty($js_bottom)) { ?>
<?php foreach ($js_bottom as $key => $info) { ?>
<?php if (!empty($info['text'])) { ?>
<script><?php echo $info['asset']; ?></script>
<?php } else { ?>
<script src="<?php echo $key; ?>"></script>
<?php } ?>
<?php } ?>
<?php } ?>