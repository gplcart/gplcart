<?php if (!$missing_credentials && !$missing_settings) { ?>
<div class="panel panel-default">
  <div class="panel-body clearfix">
    <form class="form-inline">
      <div class="form-group pull-left">
        <label class="control-label"><?php echo $this->text('Store'); ?></label>
        <select class="form-control" name="store_id" onchange="$(this).closest('form').submit();">
          <?php foreach ($stores as $store_id => $store_data) { ?>
          <?php if ($store_data['domain'] == $store['domain']) { ?>
          <option value="<?php echo $store_id; ?>" selected>
          <?php echo $this->escape($store_data['domain']); ?>
          </option>
          <?php } else { ?>
          <option value="<?php echo $store_id; ?>">
          <?php echo $this->escape($store_data['domain']); ?>
          </option>
          <?php } ?>
          <?php } ?>
        </select> 
      </div>
      <div class="form-group pull-right">
        <a class="btn btn-default" href="<?php echo $this->url(false, array('ga_update' => 1, 'ga_view' => $ga_view, 'store_id' => $store['store_id'])); ?>">
          <i class="fa fa-refresh"></i> <?php echo $this->text('Update'); ?>
        </a>       
      </div>
    </form>
  </div>
</div>
<div class="row">
  <div class="col-md-6">
    <?php if(isset($panel_traffic)) { ?>
    <?php echo $panel_traffic; ?>
    <?php } ?>
    <?php if(isset($panel_top_pages)) { ?>
    <?php echo $panel_top_pages; ?>
    <?php } ?>
  </div>
  <div class="col-md-6">
    <?php if(isset($panel_sources)) { ?>
    <?php echo $panel_sources; ?>
    <?php } ?>
    <?php if(isset($panel_software)) { ?>
    <?php echo $panel_software; ?>
    <?php } ?>
  </div>
</div>
<?php } else { ?>
<div class="row">
  <div class="col-md-12">
    <?php if ($missing_settings) { ?>
    <?php echo $this->text('<a href="!href">Google Analytics</a> is not properly set up', array('!href' => $this->url("admin/settings/store/{$store['store_id']}"))); ?>
    <?php } ?>
    <?php if ($missing_credentials) { ?>
    <br><?php echo $this->text('<a href="!href">Google API credentials</a> are not properly set up', array('!href' => $this->url('admin/settings/common'))); ?>
    <?php } ?>
  </div>
</div>
<?php } ?>