<?php if (!empty($images['main'])) { ?>
<div id="lg-gallery" class="row">
  <?php if (!empty($images['extra'])) { ?>
  <div class="col-md-2 hidden-xs hidden-sm">
    <?php foreach ($images['extra'] as $image) { ?>
    <a class="item" href="<?php echo $image['url_original']; ?>">
      <img class="img-responsive thumbnail" alt="<?php echo $this->escape($image['title']); ?>" title="<?php echo $this->escape($image['description']); ?>" src="<?php echo $this->escape($image['url_extra']); ?>">
    </a>
    <?php } ?>
  </div>
  <div class="col-md-10">
    <a class="item" href="<?php echo $images['main']['url_original']; ?>" data-src="<?php echo $images['main']['url_original']; ?>">
      <img class="img-responsive" alt="<?php echo $this->escape($images['main']['title']); ?>" title="<?php echo $this->escape($images['main']['description']); ?>" src="<?php echo $this->escape($images['main']['url_big']); ?>">
    </a>
  </div>
  <?php } else { ?>
  <div class="col-md-12">
    <a class="item" href="<?php echo $images['main']['url_original']; ?>">
      <img class="img-responsive" alt="<?php echo $this->escape($images['main']['title']); ?>" title="<?php echo $this->escape($images['main']['description']); ?>" src="<?php echo $this->escape($images['main']['url_big']); ?>">
    </a>
  </div> 
  <?php } ?>
</div>
<?php } ?>