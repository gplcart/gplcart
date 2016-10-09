<?php if (!empty($files)) { ?>
<div class="row section collection collection-file">
  <ul class="slider" data-slider="true" data-slider-settings='{
    "auto": false,
    "loop": true,
    "pager": false,
    "autoWidth": true,
    "pauseOnHover": true,
    "item": 2
  }'>
    <?php foreach ($files as $file) { ?>
    <li><?php echo $file['rendered']; ?></li>
    <?php } ?>
  </ul>
</div>
<?php } ?>