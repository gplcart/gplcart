<div class="row">
  <div class="col-md-12">
    <?php if($results) { ?>
    <?php foreach($results as $result) { ?>
    <?php echo $result; ?>
    <?php } ?>
    <?php } else { ?>
    <?php echo $this->text('No results'); ?>
    <?php } ?>
  </div>
</div>
<div class="row">
  <div class="col-md-12">
    <?php echo $pager; ?>
  </div>
</div>
