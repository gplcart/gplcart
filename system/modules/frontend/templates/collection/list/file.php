<?php if (!empty($files)) { ?>
<div class="row section collection collection-file banners">
<ul class="slider">
  <?php foreach ($files as $file) { ?>
  <li><?php echo $file['rendered']; ?></li>
  <?php } ?>
</ul>
</div>
<?php } ?>