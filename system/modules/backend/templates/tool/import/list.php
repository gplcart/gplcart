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
      <?php if ($this->access('category_add') && $this->access('product_add')) { ?>
      <li>
        <a href="<?php echo $this->url('', array('demo' => 1)); ?>" onclick="return confirm();">
          <?php echo $this->text('Categories + products (demo)'); ?>
        </a>
      </li>
      <?php } ?>
    </ul>
  </div>
</div>
<?php if (!empty($job)) { ?>
<?php echo $job; ?>
<?php } ?>