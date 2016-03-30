<div class="row import-operations">
  <div class="col-md-12">
    <ul class="fa-ul">
      <?php foreach ($operations as $id => $operation) { ?>
      <li>
        <i class="fa-li fa fa-square-o"></i> <a href="<?php echo $this->url("admin/tool/export/$id"); ?>">
        <?php echo $this->escape($operation['name']); ?>
        </a>
      </li>
      <?php } ?>
    </ul>
  </div>
</div>