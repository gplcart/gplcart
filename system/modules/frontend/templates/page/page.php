<div class="panel panel-default">
  <div class="panel-body">
    <div class="row page">
      <?php if(!empty($images)) { ?>
      <div class="col-md-3"><?php echo $images; ?></div>
      <?php } ?>
      <div class="<?php echo empty($images) ? 'col-md-12' : 'col-md-9'; ?>">
        <?php echo $this->xss($page['description']); ?>
      </div>
    </div>
  </div>
</div>
