<?php if ($reviews) { ?>
<div class="row">
  <div class="col-md-6">
    <h2 class="h3"><?php echo $this->text('Reviews'); ?></h2>
  </div>
  <div class="col-md-6 text-right">
    <ul class="list-inline h3">
      <li>
        <div class="dropdown btn-group">
          <?php echo $this->text('Sort'); ?>
          <a class="dropdown-toggle" href="#" data-toggle="dropdown">
            <?php if (isset($query['sort']) && isset($query['order'])) { ?>
            <?php if ($query['sort'] == 'created' && $query['order'] == 'asc') { ?>
            <?php echo $this->text('older first'); ?>
            <?php } ?>
            <?php if ($query['sort'] == 'created' && $query['order'] == 'desc') { ?>
            <?php echo $this->text('recent first'); ?>
            <?php } ?>
            <?php } else { ?>
            <?php echo $this->text('recent first'); ?>
            <?php } ?>
          </a>
          <ul class="dropdown-menu">
            <li>
              <a rel="nofollow" href="<?php echo $this->url(false, array('sort' => 'created', 'order' => 'desc')); ?>">
                <?php echo $this->text('Recent first'); ?>
              </a>
            </li>
            <li>
              <a rel="nofollow" href="<?php echo $this->url(false, array('sort' => 'created', 'order' => 'asc')); ?>">
                <?php echo $this->text('Older first'); ?>
              </a>
            </li>
          </ul> 
        </div>
      </li>
      <?php if ($this->access('review_edit') && $this->access('admin')) { ?>
      <li>
        <a href="<?php echo $this->url('admin/content/review/add', array('target' => "product/{$product['product_id']}")); ?>">
          <?php echo $this->text('Add review'); ?>
        </a>
      </li>
      <?php } else if ($editable && $this->uid) { ?>
      <li>
        <a rel="nofollow" href="<?php echo $this->url("review/add/{$product['product_id']}"); ?>">
          <?php echo $this->text('Add review'); ?> 
        </a>
      </li>
      <?php } ?>
    </ul>
  </div>
</div>
<div class="row">
  <?php foreach ($reviews as $review) { ?>
  <div class="col-md-12">
    <div class="media">
      <div class="media-body">
        <div class="media-heading">
            <?php echo $review['rating_widget']; ?>
          <b><?php echo $this->escape($review['name']); ?></b>
          <span class="text-muted small"><?php echo $this->date($review['created']); ?></span>
        </div>
        <p class="text"><?php echo $this->escape($review['text']); ?></p>
        <p class="actions">
            <?php if ($this->access('review_edit') && $this->access('admin')) { ?>
            <a href="<?php echo $this->url("admin/content/review/edit/{$review['review_id']}", array('target' => "product/{$product['product_id']}")); ?>">
            <?php echo $this->text('edit'); ?>
            </a>
            <?php } else if ($review['user_id'] == $this->uid && $editable) { ?>
            <a href="<?php echo $this->url("review/edit/{$product['product_id']}/{$review['review_id']}"); ?>">
            <?php echo $this->text('edit'); ?>
            </a>
            <?php } ?>
        </p>
      </div>
    </div>
  </div>
  <?php } ?>
</div>
<div class="row">
  <div class="col-md-12">
    <?php echo $pager; ?>
  </div>
</div>
<?php } ?>