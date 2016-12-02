<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 * 
 * To see available variables: <?php print_r(get_defined_vars()); ?>
 * To see the current controller object: <?php print_r($this); ?>
 * To call a controller method: <?php $this->exampleMethod(); ?>
 */
?>
<?php if (!empty($reviews)) { ?>
<div id="reviews" class="panel panel-default reviews">
  <div class="panel-heading">
    <div class="row">
      <div class="col-md-6">
        <?php echo $this->text('Reviews'); ?>
      </div>
      <div class="col-md-6 text-right">
        <ul class="list-inline">
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
                  <a rel="nofollow" href="<?php echo $this->url(false, array('sort' => 'created', 'order' => 'desc')); ?>#reviews">
                    <?php echo $this->text('Recent first'); ?>
                  </a>
                </li>
                <li>
                  <a rel="nofollow" href="<?php echo $this->url(false, array('sort' => 'created', 'order' => 'asc')); ?>#reviews">
                    <?php echo $this->text('Older first'); ?>
                  </a>
                </li>
              </ul> 
            </div>
          </li>
          <?php if ($editable && $this->uid) { ?>
          <li>
            <a rel="nofollow" href="<?php echo $this->url("review/add/{$product['product_id']}"); ?>">
              <?php echo $this->text('Add review'); ?>
            </a>
          </li>
          <?php } ?>
        </ul>
      </div>
    </div>
  </div>
  <div class="panel-body">
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
              <?php if ($review['user_id'] == $this->uid && $editable) { ?>
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
  </div>
  <?php if ($pager) { ?>
  <div class="panel-footer">
    <div class="text-right"><?php echo $pager; ?></div>
  </div>
  <?php } ?>
</div>
<?php } ?>