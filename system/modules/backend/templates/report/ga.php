<div class="row">
  <?php if (empty($missing_credentials) && empty($missing_settings)) { ?>
  <div class="col-md-2">
    <select class="form-control" onchange="if (this.value) window.location.href=this.value">
      <?php foreach ($stores as $store_id => $store_data) { ?>
      <?php if ($store_data['domain'] == $store['domain']) { ?>
      <option value="<?php echo $this->url(false, array('store_id' => $store_id)); ?>" selected>
      <?php echo $this->escape($store_data['domain']); ?>
      </option>
      <?php } else { ?>
      <option value="<?php echo $this->url(false, array('store_id' => $store_id)); ?>">
      <?php echo $this->escape($store_data['domain']); ?>
      </option>
      <?php } ?>
      <?php } ?>
    </select>  
  </div>
  <div class="col-md-10 text-right">
    <a class="btn btn-default" href="<?php echo $this->url(false, array('ga_update' => 1, 'ga_view' => $ga_view, 'store_id' => $store['store_id'])); ?>">
      <i class="fa fa-refresh"></i> <?php echo $this->text('Update'); ?>
    </a>
  </div>
  <?php } ?>
</div>
<?php if (empty($missing_credentials) && empty($missing_settings)) { ?>

<div class="row">
  <div class="col-md-6">
    <div class="panel panel-default">
      <div class="panel-heading"><?php echo $this->text('Traffic'); ?></div>
      <div class="panel-body">
        <canvas id="chart-traffic"></canvas>
      </div>
    </div>
    

    

    
    <div class="panel panel-default">
      <div class="panel-heading"><?php echo $this->text('Top pages'); ?></div>
      <div class="panel-body">
          
        <?php if (!empty($top_pages)) { ?>
        <table class="table table-striped">
          <thead>
            <tr>
              <th><?php echo $this->text('Url'); ?></th>
              <th><?php echo $this->text('Page views'); ?></th>
              <th><?php echo $this->text('Unique page views'); ?></th>
              <th><?php echo $this->text('Time on page'); ?></th>
              <th><?php echo $this->text('Bounces'); ?></th>
              <th><?php echo $this->text('Entrances'); ?></th>
              <th><?php echo $this->text('Exits'); ?></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($top_pages as $page) { ?>
            <tr>
              <td>
                <?php if (preg_match('!^[\w.]*$!', $page[0])) { ?>
                <a target="_blank" href="http://<?php echo $this->escape($page[0] . $page[1]); ?>"><?php echo $this->escape($page[0] . $page[1]); ?></a>
                <?php } else { ?>
                <?php echo $page[1]; ?>
                <?php } ?>
              </td>
              <td><?php echo $this->escape($page[2]); ?></td>
              <td><?php echo $this->escape($page[3]); ?></td>
              <td><?php echo $this->escape($page[4]); ?></td>
              <td><?php echo $this->escape($page[5]); ?></td>
              <td><?php echo $this->escape($page[6]); ?></td>
              <td><?php echo $this->escape($page[7]); ?></td>
            </tr>
            <?php } ?>
          </tbody>
        </table>
        <?php } ?>
      </div>
    </div>
    

    
  </div>
  <div class="col-md-6">
      
    
    <div class="panel panel-default">
      <div class="panel-heading"><?php echo $this->text('Top keywords'); ?></div>
      <div class="panel-body">
        <?php if (!empty($keywords)) { ?>
        <table class="table table-striped">
          <thead>
            <tr>
              <th><?php echo $this->text('Keyword'); ?></th>
              <th><?php echo $this->text('Sessions'); ?></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($keywords as $value) { ?>
            <tr>
              <td>
                <?php if (in_array($value[0], array('(not provided)', '(not set)'), true)) { ?>
                <?php echo $this->escape($value[0]); ?>
                <?php } else { ?>
                <a target="_blank" href="https://google.com/search?q=<?php echo $this->escape($value[0]); ?>"><?php echo $this->escape($value[0]); ?></a>
                <?php } ?>
              </td>
              <td><?php echo $this->escape($value[1]); ?></td>
            </tr>
            <?php } ?>
          </tbody>
        </table>
        <?php } ?> 
        
      </div>
    </div>
    
    
    <div class="panel panel-default">
      <div class="panel-heading"><?php echo $this->text('Top sources'); ?></div>
      <div class="panel-body">
        <?php if (!empty($sources)) { ?>
        <table class="table table-striped">
          <thead>
            <tr>
              <th><?php echo $this->text('Source'); ?></th>
              <th><?php echo $this->text('Medium'); ?></th>
              <th><?php echo $this->text('Sessions'); ?></th>
              <th><?php echo $this->text('Page views'); ?></th>
              <th><?php echo $this->text('Session duration'); ?></th>
              <th><?php echo $this->text('Exits'); ?></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($sources as $source) { ?>
            <tr>
              <td><?php echo $this->escape($source[0]); ?></td>
              <td><?php echo $this->escape($source[1]); ?></td>
              <td><?php echo $this->escape($source[2]); ?></td>
              <td><?php echo $this->escape($source[3]); ?></td>
              <td><?php echo $this->escape($source[4]); ?></td>
              <td><?php echo $this->escape($source[5]); ?></td>
            </tr>
            <?php } ?>
          </tbody>
        </table>
        <?php } ?> 
        
      </div>
    </div>
    
    
    <div class="panel panel-default">
      <div class="panel-heading"><?php echo $this->text('Top software'); ?></div>
      <div class="panel-body">
        <?php if (!empty($software)) { ?>
        <table class="table table-striped">
          <thead>
            <tr>
              <th><?php echo $this->text('OS'); ?></th>
              <th><?php echo $this->text('Browser'); ?></th>
              <th><?php echo $this->text('Sessions'); ?></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($software as $result) { ?>
            <tr>
              <td><?php echo $this->escape($result[0]); ?></td>
              <td><?php echo $this->escape($result[1]); ?></td>
              <td><?php echo $this->escape($result[2]); ?></td>
            </tr>
            <?php } ?>
          </tbody>
        </table>
        <?php } ?>
      </div>
    </div>
    
    
  </div>
</div>

<?php } else { ?>
<div class="row margin-top-20">
  <div class="col-md-12">
    <?php if (isset($missing_settings)) { ?>
    <?php echo $missing_settings; ?>
    <?php } ?>
    <?php if (isset($missing_credentials)) { ?>
    <?php echo $missing_credentials; ?>
    <?php } ?>
  </div>
</div>
<?php } ?>