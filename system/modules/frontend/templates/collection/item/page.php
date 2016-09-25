<div class="grid item page col-md-3 col-sm-4 col-xs-6">
  <div class="thumbnail">
    <div class="caption text-center">
      <div class="title">
        <a href="<?php echo $this->escape($page['url']); ?>">
          <?php echo $this->truncate($this->escape($page['title']), 50); ?>
        </a>
      </div>
      <p><?php echo $this->escape(strip_tags($page['description'])); ?></p>
    </div>
  </div>
</div>
