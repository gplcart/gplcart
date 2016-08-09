<?php if (!empty($marketplace['items']) || $filtering) { ?>
<div class="panel panel-default">
  <div class="panel-body table-responsive">
    <table class="table marketplace">
      <thead>
        <tr>
          <th class="middle">
            <a href="<?php echo $sort_title; ?>">
              <?php echo $this->text('Title'); ?> <i class="fa fa-sort"></i>
            </a>
          </th>
          <th class="middle">
            <a href="<?php echo $sort_category_id; ?>">
              <?php echo $this->text('Category'); ?> <i class="fa fa-sort"></i>
            </a>
          </th>
          <th class="middle">
            <a href="<?php echo $sort_rating; ?>">
              <?php echo $this->text('Rating'); ?> <i class="fa fa-sort"></i>
            </a>
          </th>
          <th class="middle">
            <a href="<?php echo $sort_views; ?>">
              <?php echo $this->text('Views'); ?> <i class="fa fa-sort"></i>
            </a>
          </th>
          <th class="middle">
            <a href="<?php echo $sort_downloads; ?>">
              <?php echo $this->text('Downloads'); ?> <i class="fa fa-sort"></i>
            </a>
          </th>
          <th class="middle">
            <a href="<?php echo $sort_price; ?>">
              <?php echo $this->text('Price'); ?> <i class="fa fa-sort"></i>
            </a>
          </th>
          <th class="middle"></th>
        </tr>
        <tr class="filters active">
          <th>
            <input class="form-control" name="title" placeholder="<?php echo $this->text('Any'); ?>" maxlength="10" value="<?php echo $filter_title; ?>">
          </th>
          <th class="middle">
            <?php if (!empty($marketplace['categories'])) { ?>
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
          </th>
          <th class="middle">
            <select class="form-control" name="rating">
              <option value="any"><?php echo $this->text('- Any -'); ?></option>
              <?php for ($i = 1; $i <= 5; $i++) { ?>
              <?php if ($filter_rating == $i) { ?>
              <option value="<?php echo $i; ?>" selected><?php echo $i; ?></option>
              <?php } else { ?>
              <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
              <?php } ?> 
              <?php } ?> 
            </select>
          </th>
          <th class="middle"></th>
          <th class="middle"></th>
          <th class="middle"></th>
          <th class="middle">
            <button type="button" class="btn btn-default clear-filter" title="<?php echo $this->text('Reset filter'); ?>">
              <i class="fa fa-refresh"></i>
            </button>
            <button type="button" class="btn btn-default filter" title="<?php echo $this->text('Filter'); ?>">
              <i class="fa fa-search"></i>
            </button>
          </th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($marketplace['items']) && $filtering) { ?>
        <tr>
          <td colspan="7">
            <?php echo $this->text('No results'); ?>
            <a href="#" class="clear-filter"><?php echo $this->text('Reset'); ?></a>
          </td>
        </tr>
        <?php } ?>
        <?php if (!empty($marketplace['items'])) { ?>
        <?php foreach ($marketplace['items'] as $marketplace_id => $item) { ?>
        <tr>
          <td>
            <?php echo $this->truncate($this->escape($item['title']), 50); ?>
          </td>
          <td>
          <?php if (isset($marketplace['categories'][$item['category_id']])) { ?>
          <?php echo $this->escape($marketplace['categories'][$item['category_id']]); ?>
          <?php } ?>
          </td>
          <td>
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
          </td>
          <td>
            <?php echo $item['views']; ?>
          </td>
          <td>
            <?php echo $item['downloads']; ?>
          </td>
          <td>
            <?php if (empty($item['price'])) { ?>
            <?php echo $this->text('FREE'); ?>
            <?php } else { ?>
            $ <?php echo $this->escape($item['price']); ?>
            <?php } ?>
          </td>
          <td>
            <ul class="list-inline">
              <li><a href="#details-<?php echo $marketplace_id; ?>" data-toggle="collapse" ><?php echo strtolower($this->text('Details')); ?></a></li>
              <li><a target="_blank" href="<?php echo $this->escape($item['url']); ?>"><?php echo strtolower($this->text('View')); ?></a></li>
            </ul>
          </td>
        </tr>
        <tr class="collapse active" id="details-<?php echo $marketplace_id; ?>">
          <td colspan="7">
            <?php echo $this->truncate($this->escape(strip_tags($item['summary'])), 200); ?>
          </td>
        </tr>
        <?php } ?>
        <?php } ?>
      </tbody>
    </table>
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