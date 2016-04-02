<?php if (!empty($images['main'])) {
    ?>
<div class="row">
  <?php if (!empty($images['extra'])) {
    ?>
  <div class="col-md-2 hidden-xs hidden-sm">
    <?php $index = 1;
    ?>
    <?php foreach ($images['extra'] as $image) {
    ?>
    <a class="gallery" href="<?php echo $image['url_original'];
    ?>" data-size-w="<?php echo $image['size'][0];
    ?>" data-size-h="<?php echo $image['size'][1];
    ?>" data-index="<?php echo $index;
    ?>">
      <img class="img-responsive thumbnail" alt="<?php echo $this->escape($image['title']);
    ?>" title="<?php echo $this->escape($image['description']);
    ?>" src="<?php echo $this->escape($image['url_extra']);
    ?>">
    </a>
    <?php $index++;
    ?>
    <?php 
}
    ?>
  </div>
  <div class="col-md-10">
    <a class="gallery" href="<?php echo $images['main']['url_original'];
    ?>" data-size-w="<?php echo $images['main']['size'][0];
    ?>" data-size-h="<?php echo $images['main']['size'][1];
    ?>" data-index="0">
      <img class="img-responsive thumbnail" alt="<?php echo $this->escape($images['main']['title']);
    ?>" title="<?php echo $this->escape($images['main']['description']);
    ?>" src="<?php echo $this->escape($images['main']['url_big']);
    ?>">
    </a>
  </div>
  <?php 
} else {
    ?>
  <div class="col-md-12">
    <a class="gallery" href="<?php echo $images['main']['url_original'];
    ?>" data-size-w="<?php echo $images['main']['size'][0];
    ?>" data-size-h="<?php echo $images['main']['size'][1];
    ?>" data-index="0">
      <img class="img-responsive thumbnail" alt="<?php echo $this->escape($images['main']['title']);
    ?>" title="<?php echo $this->escape($images['main']['description']);
    ?>" src="<?php echo $this->escape($images['main']['url_big']);
    ?>">
    </a>
  </div> 
  <?php 
}
    ?>
</div>
<div class="pswp" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="pswp__bg"></div>
  <div class="pswp__scroll-wrap">
    <div class="pswp__container">
      <div class="pswp__item"></div>
      <div class="pswp__item"></div>
      <div class="pswp__item"></div>
    </div>
    <div class="pswp__ui pswp__ui--hidden">
      <div class="pswp__top-bar">
        <div class="pswp__counter"></div>
        <button class="pswp__button pswp__button--close" title="<?php echo $this->text('Close');
    ?>"></button>
        <button class="pswp__button pswp__button--share" title="<?php echo $this->text('Share');
    ?>"></button>
        <button class="pswp__button pswp__button--fs" title="<?php echo $this->text('Toggle fullscreen');
    ?>"></button>
        <button class="pswp__button pswp__button--zoom" title="<?php echo $this->text('Zoom in/out');
    ?>"></button>
        <div class="pswp__preloader">
          <div class="pswp__preloader__icn">
            <div class="pswp__preloader__cut">
              <div class="pswp__preloader__donut"></div>
            </div>
          </div>
        </div>
      </div>
      <div class="pswp__share-modal pswp__share-modal--hidden pswp__single-tap">
        <div class="pswp__share-tooltip"></div> 
      </div>
      <button class="pswp__button pswp__button--arrow--left" title="<?php echo $this->text('Previous');
    ?>">
      </button>
      <button class="pswp__button pswp__button--arrow--right" title="<?php echo $this->text('Next');
    ?>">
      </button>
      <div class="pswp__caption">
        <div class="pswp__caption__center"></div>
      </div>
    </div>
  </div>
</div>
<script>
$(function () {
    var getItems = function () {
        var items = [];
        $('a.gallery').each(function () {
            var $href = $(this).attr('href'),
                $width = $(this).data('size-w'),
                $height = $(this).data('size-h'),
                $index = $(this).data('index'),
                $title = $(this).find('img').attr('title');

            var item = {
                src: $href,
                w: $width,
                h: $height,
                pid: $index,
                title: $title
            };

            items[$index] = item;
        });

        return items;
    };

    items = getItems();

    var $pswp = $('.pswp')[0];
    $('a.gallery').click(function (event) {
        event.preventDefault();
        var options = {index: parseInt($(this).data('index')), galleryPIDs: true};
        var lightBox = new PhotoSwipe($pswp, PhotoSwipeUI_Default, items, options);
        lightBox.init();
    });
});
</script>
<?php 
} ?>