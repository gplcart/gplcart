<div class="row">
  <form class="navbar-form navbar-left" id="page-search-form">
    <div class="input-group">
      <input class="form-control" name="q" placeholder="<?php echo $this->text('Search'); ?>" value="<?php echo $query; ?>">
      <span class="input-group-btn">
        <select class="form-control" name="search_id">
          <?php foreach ($search_handlers as $handler_id => $handler) { ?>
          <option value="<?php echo $handler_id; ?>"<?php echo ($search_id == $handler_id) ? ' selected' : ''; ?>>
          <?php echo $this->escape($handler['name']); ?>
          </option>
          <?php } ?>
        </select>
      </span>
      <span class="input-group-btn">
        <button class="btn btn-default"><i class="fa fa-search"></i></button>
      </span>
    </div>
  </form>
</div>
<div class="row margin-top-20">
  <div class="col-md-12">
    <?php if($results) { ?>
    <?php foreach($results as $result) { ?>
    <?php echo $result; ?>
    <?php } ?>
    <?php } else { ?>
    <?php echo $this->text('No results'); ?>
    <?php } ?>
  </div>
</div>
<div class="row">
  <div class="col-md-12">
    <?php echo $pager; ?>
  </div>
</div>
