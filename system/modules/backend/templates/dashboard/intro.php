<div class="row">
  <div class="col-md-12">
    <h3><?php echo $this->text('Welcome to your admin area!'); ?></h3>
    <p><?php echo $this->text('Here are some extra steps to set up your store'); ?></p>
    <div class="panel panel-default">
      <div class="panel-body">
        <div class="row">
          <div class="col-md-10">
            <h4><?php echo $this->text('Edit settings'); ?></h4>
            <p><?php echo $this->text('Add company info, change logo, theme, set up Google Analitics...'); ?></p>  
          </div>
          <div class="col-md-2 text-right">
            <a class="btn btn-default btn-block" href="<?php echo $this->url('admin/settings/store/1'); ?>">
              <?php echo $this->text('Edit settings'); ?>
            </a>
          </div>
        </div>
      </div>  
    </div>
    <div class="panel panel-default">
      <div class="panel-body">
        <div class="row">
          <div class="col-md-10">
            <h4><?php echo $this->text('Add products'); ?></h4>
            <p><?php echo $this->text('Add products to sell or populate your store with <a href="!href">demo content</a>', array('!href' => $this->url('admin/tool/import'))); ?></p>
          </div>
          <div class="col-md-2 text-right">
            <a class="btn btn-default btn-block" href="<?php echo $this->url('admin/content/product/add'); ?>">
              <?php echo $this->text('Add products'); ?>
            </a>
          </div>
        </div>
      </div> 
    </div>
    <div class="panel panel-default">
      <div class="panel-body">
        <div class="row">
          <div class="col-md-10">
            <h4><?php echo $this->text('Manage modules'); ?></h4>
            <p><?php echo $this->text('Extend your store by installing new modules and themes'); ?></p>
          </div>
          <div class="col-md-2 text-right">
            <a class="btn btn-default btn-block" href="<?php echo $this->url('admin/module'); ?>">
              <?php echo $this->text('Manage modules'); ?>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="row">
  <div class="col-md-12 text-center">
    <a href="<?php echo $this->url('', array('skip_intro' => 1)); ?>">
      <?php echo $this->text('Skip these steps'); ?>
    </a>
  </div>
</div>

