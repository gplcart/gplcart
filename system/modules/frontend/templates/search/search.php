<div class="panel panel-default">
  <div class="panel-body">
    <?php if (!empty($results)) { ?>
    <?php echo $navbar; ?>
    <?php echo $results; ?>
    <?php if ($pager) { ?>
    <div class="row">
      <div class="col-md-12 text-right">
        <?php echo $pager; ?>
      </div>
    </div>
    <?php } ?>
    <?php } else { ?>
    <?php echo $this->text('No products found. Try another search keyword'); ?>
    <?php } ?>
  </div>
</div>