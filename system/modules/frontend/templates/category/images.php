<?php if (!empty($category['images'])) {
    ?>
<div class="category-images">
  <div id="carousel-category-images" class="carousel slide" data-ride="carousel" data-interval="false">
    <div class="carousel-inner">
      <?php $index = 0;
    ?>
      <?php foreach ($category['images'] as $image) {
    ?>
      <div class="item<?php echo ($index == 0) ? ' active' : '';
    ?>">
        <img class="thumbnail" src="<?php echo $this->escape($image['thumb']);
    ?>" alt="<?php echo $this->escape($category['title']);
    ?>">
      </div>
      <?php $index++;
    ?>
      <?php 
}
    ?>
    </div>
    <?php if (count($category['images']) > 1) {
    ?>
    <ol class="carousel-indicators">
      <?php $index = 0;
    ?>
      <?php foreach ($category['images'] as $image) {
    ?>
      <li data-target="#carousel-category-images" data-slide-to="<?php echo $index;
    ?>" class="<?php echo ($index == 0) ? 'active' : '';
    ?>">
      </li>
      <?php $index++;
    ?>
      <?php 
}
    ?>
    </ol>
    <?php 
}
    ?>
  </div>
</div>
<?php 
} ?>