<?php if ($pages) { ?> 
<div class="fullscreen-carousel">
  <div id="page-carousel" class="carousel slide carousel-fade">
    <?php if (count($pages) > 1) { ?>
    <ol class="carousel-indicators">
      <?php $control_index = 0; ?>
      <?php foreach ($pages as $page) { ?>
      <li data-target="#page-carousel" data-slide-to="<?php echo $control_index; ?>" class="<?php echo ($control_index == 0) ? 'active' : ''; ?>"></li>
      <?php $control_index++; ?>
      <?php } ?> 
    </ol>
    <?php } ?>
    <div class="carousel-inner">
      <?php $slide_index = 0; ?>
      <?php foreach ($pages as $page) { ?>
      <div class="item clickable<?php echo ($slide_index == 0) ? ' active' : ''; ?>" data-url="<?php echo $this->escape($page['url']); ?>">
        <?php if (empty($page['thumb'])) { ?>
          <div class="container">
            <div class="description">
              <div class="inner"><div class="content"><?php echo $this->xss($page['description']); ?></div></div>
            </div>
          </div>
        <?php } else { ?>
        <div class="fill" style="background-image:url('<?php echo $this->escape($page['thumb']); ?>');">
          <div class="container">
            <div class="description">
              <div class="inner"><div class="content"><?php echo $this->xss($page['description']); ?></div></div>
            </div>
          </div>
        </div>
        <?php } ?>
      </div>
      <?php $slide_index++; ?>
      <?php } ?>
    </div>
  </div>
</div>
<?php } ?>