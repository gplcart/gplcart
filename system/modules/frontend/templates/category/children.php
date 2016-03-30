<?php if ($children) { ?>
<div class="row section">
  <?php foreach ($children as $child) { ?>
  <div class="col-md-2">
    <a href="<?php echo $this->escape($child['url']); ?>">
      <?php if (isset($child['thumb'])) { ?>
      <img class="img-responsive thumbnail" src="<?php echo $this->escape($child['thumb']); ?>" alt="<?php echo $this->escape($child['title']); ?>">
      <?php } ?>
      <div class="clearfix"><?php echo $this->escape($child['title']); ?></div>
    </a>
  </div>
  <?php } ?>
</div>
<?php } ?>