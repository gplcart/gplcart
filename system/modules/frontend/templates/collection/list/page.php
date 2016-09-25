<?php if (!empty($pages)) { ?>
<div class="panel panel-default collection collection-page pages">
  <div class="panel-heading"><?php echo $this->escape($title); ?></div>
  <div class="panel-body">
    <div class="row">
      <?php foreach ($pages as $page) { ?>
      <?php echo $page['rendered']; ?>
      <?php } ?>
    </div>
  </div>
</div>
<?php } ?>

