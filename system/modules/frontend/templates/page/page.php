<div id="page">
  <?php if ($page['images']) { ?>
  <?php if (count($page['images']) > 1) { ?>
      <div id="carousel" class="carousel slide" data-ride="carousel">
        <?php $i = 0; ?>
        <ol class="carousel-indicators">
            <?php foreach ($page['images'] as $image) { ?>
              <li data-target="#carousel" data-slide-to="<?php echo $i; ?>"<?php echo ($i == 0) ? ' class="active"' : ''; ?>></li>
              <?php $i++; ?>
          <?php } ?>
        </ol>
        <div class="carousel-inner" role="listbox">
            <?php $i = 0; ?>
            <?php foreach ($page['images'] as $image) { ?>
              <div class="item<?php echo ($i == 0) ? ' active' : ''; ?>">
                <img class="img-responsive" src="<?php echo $image['thumb']; ?>">
                <div class="carousel-caption"></div>
              </div>
              <?php $i++; ?>
          <?php } ?>
        </div>
      </div>
  <?php } else { ?>
  <div class="image"><img class="img-responsive" src="<?php echo $page['thumb']; ?>"></div>
  <?php } ?>
  <?php } ?>
  <?php echo $page['description']; ?>
</div>