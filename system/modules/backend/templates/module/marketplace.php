<?php if (!empty($marketplace['items']) || $filtering) { ?>
<div class="panel panel-default">
  <div class="panel-heading clearfix">
    <form class="navbar-form navbar-right" onchange="$(this).submit();">
      <?php if (!empty($marketplace['categories'])) { ?>
      <span class="form-control-static"><?php echo $this->text('Category'); ?> </span>
      <select class="form-control" name="category_id">
        <option value="any"><?php echo $this->text('- Any -'); ?></option>
        <?php foreach ($marketplace['categories'] as $category_id => $category_name) { ?>
        <?php if ($filter_category_id == $category_id) { ?>
        <option value="<?php echo $this->escape($category_id); ?>" selected><?php echo $this->escape($category_name); ?></option>
        <?php } else { ?>
        <option value="<?php echo $this->escape($category_id); ?>"><?php echo $this->escape($category_name); ?></option>
        <?php } ?>
        <?php } ?>
      </select>
      <?php } ?>
      <span class="form-control-static"><?php echo $this->text('Sort'); ?> </span>
      <select name="sort" class="form-control">
        <option value="views-desc"<?php echo ($sort == 'views-desc') ? ' selected' : ''; ?>>
          <?php echo $this->text('Most popular first'); ?>
        </option>
        <option value="views-asc"<?php echo ($sort == 'views-asc') ? ' selected' : ''; ?>>
          <?php echo $this->text('Least popular first'); ?>
        </option>
        <option value="price-desc"<?php echo ($sort == 'price-desc') ? ' selected' : ''; ?>>
          <?php echo $this->text('High prices first'); ?>
        </option>
        <option value="price-asc"<?php echo ($sort == 'price-asc') ? ' selected' : ''; ?>>
          <?php echo $this->text('Free / low prices first'); ?>
        </option>
        <option value="rating-desc"<?php echo ($sort == 'rating-desc') ? ' selected' : ''; ?>>
          <?php echo $this->text('Most rated first'); ?>
        </option>
        <option value="rating-asc"<?php echo ($sort == 'rating-asc') ? ' selected' : ''; ?>>
          <?php echo $this->text('Least rated first'); ?>
        </option>
      </select>
      <a href="<?php echo $this->url(); ?>" class="btn btn-default" title="<?php echo $this->text('Reset'); ?>"><span class="fa fa-refresh"></span></a>
    </form>
  </div>
  <div class="panel-body">
    <?php if (!empty($marketplace['items'])) { ?>
    <div class="row">
      <?php foreach ($marketplace['items'] as $marketplace_id => $item) { ?>
      <div class="col-md-4">
        <div class="item margin-bottom-20">
          <div class="row">
            <div class="col-md-12">
              <p>
                <a target="_blank" href="<?php echo $this->escape($item['url']); ?>">
                  <?php echo $this->truncate($this->escape($item['title']), 50); ?>
                </a>
              </p>
              <p>
                <?php if (empty($item['price'])) { ?>
                <span class="label label-success"><?php echo $this->text('FREE'); ?></span>
                <?php } else { ?>
                $ <?php echo $this->escape($item['price']); ?>
                <?php } ?>
              </p>
              <p class="group inner list-group-item-heading">
                <?php echo $this->truncate($this->escape(strip_tags($item['summary'])), 200); ?>
              </p>
              <div class="star-rating static">
                <div class="star-rating-wrap">
                  <?php for ($stars = 0; $stars < 5; $stars++) { ?>
                  <?php if ($stars < $item['rating']) { ?>
                  <span class="star-rating-ico fa fa-star"></span>
                  <?php } else { ?>
                  <span class="star-rating-ico fa fa-star-o"></span>
                  <?php } ?>
                  <?php } ?>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <?php } ?>
    </div>
    <?php } ?>
  </div>
  <?php if (!empty($pager)) { ?>
  <div class="panel-footer"><?php echo $pager; ?></div>  
  <?php } ?>
</div>
<?php } else { ?>
<div class="row">
  <div class="col-md-12">
    <?php echo $this->text('Nothing to display. Check your internet connection'); ?>
  </div>
</div>
<?php } ?>