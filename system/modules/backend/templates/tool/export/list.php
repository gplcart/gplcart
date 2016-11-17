<div class="panel panel-default">
  <div class="panel-body">
    <div class="btn-toolbar">
      <?php foreach ($operations as $id => $operation) { ?>
      <a class="btn btn-default" href="<?php echo $this->url("admin/tool/export/$id"); ?>">
        <?php echo $this->escape($operation['name']); ?>
      </a>
      <?php } ?>
    </div>
  </div>
</div>