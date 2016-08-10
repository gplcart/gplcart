<div class="panel panel-default">
  <div class="panel-body">
    <ul>
      <?php foreach ($operations as $id => $operation) { ?>
      <li>
        <a href="<?php echo $this->url("admin/tool/import/$id"); ?>">
        <?php echo $this->escape($operation['name']); ?>
        </a>
      </li>
      <?php } ?>
    </ul>
  </div>
</div>