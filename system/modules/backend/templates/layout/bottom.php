<footer class="footer">
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-6">
        <p class="text-muted small">
          &copy; <?php echo (date('Y') == 2015) ? date('Y') : '2015 - ' . date('Y'); ?>
          GPL Cart All Rights Reserved. Version <?php echo GC_VERSION; ?>
        </p>	  
      </div>
      <div class="col-md-6 text-right">
        <p id="session-expires" class="text-muted small"></p>
      </div>
    </div>
  </div>
</footer>
<?php if(!empty($js_bottom)) { ?>
<?php foreach ($js_bottom as $key => $info) { ?>
<?php if ($info['text']) { ?>
<script><?php echo $info['text']; ?></script>
<?php } else { ?>
<script src="<?php echo $key; ?>"></script>
<?php } ?>
<?php } ?>
<?php } ?>