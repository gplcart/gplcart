<?php if (!empty($region_bottom)) { ?>
<div class="row">
  <div class="col-md-12">
    <?php foreach ($region_bottom as $item) { ?>
    <?php echo $item; ?>
    <?php } ?>
  </div>
</div>
<?php } ?>
<?php if (!empty($js_bottom)) { ?>
<?php foreach ($js_bottom as $src => $data) { ?>
<script src="<?php echo $src; ?>"></script>
<?php } ?>
<?php } ?>
