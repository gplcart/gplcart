<div class="panel panel-default">
  <div class="panel-body">
    <?php if ($products) { ?>
    <?php foreach ($products as $product_class_id => $items) { ?>
    <div class="row products">
    <?php foreach ($items as $product_id => $product) { ?>
    <?php echo $product['rendered']; ?>
    <?php } ?>
    </div>
    <div class="row">
      <div class="col-md-12">
        <?php if (count($items) > 1) { ?>
        <a href="<?php echo $this->url('compare/' . implode(',', array_keys($items))); ?>" class="btn btn-default">
          <i class="fa fa-balance-scale"></i> <?php echo $this->text('Compare'); ?>
        </a>
        <?php } else { ?>
        <span class="btn btn-default disabled">
          <i class="fa fa-balance-scale"></i> <?php echo $this->text('Add more similar products to compare'); ?>
        </span>
        <?php } ?>
      </div>
    </div>
    <?php } ?>
    <?php } else { ?>
    <?php echo $this->text('Nothing to compare'); ?>
    <?php } ?>
  </div>
</div>