<?php if (!empty($items)) { ?>
<div class="panel panel-default">
  <div class="panel-heading clearfix">
    <div class="btn-group pull-left">
      <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
        <?php echo $this->text('With selected'); ?> <span class="caret"></span>
      </button>
      <ul class="dropdown-menu">
        <?php if ($this->access('collection_item_edit')) { ?>
        <li>
          <a data-action="status" data-action-value="1" href="#">
            <?php echo $this->text('Status'); ?>: <?php echo $this->text('Enabled'); ?>
          </a>
        </li>
        <li>
          <a data-action="status" data-action-value="0" href="#">
            <?php echo $this->text('Status'); ?>: <?php echo $this->text('Disabled'); ?>
          </a>
        </li>
        <?php } ?>
        <?php if ($this->access('collection_item_delete')) { ?>
        <li>
          <a data-action="delete" href="#">
            <?php echo $this->text('Delete'); ?>
          </a>
        </li>
        <?php } ?>
      </ul>
    </div>
    <div class="btn-toolbar pull-right">
      <?php if ($this->access('collection_item_add')) { ?>
      <a href="<?php echo $this->url("admin/content/collection-item/{$collection['collection_id']}/add"); ?>" class="btn btn-default add">
        <i class="fa fa-plus"></i> <?php echo $this->text('Add'); ?>
      </a>
      <?php } ?>
    </div>  
  </div>
  <div class="panel-body table-responsive">  
    <table class="table table-striped collection-items">
      <thead>
        <tr>
          <th><input type="checkbox" id="select-all" value="1"></th>
          <th><?php echo $this->text('ID'); ?></th>
          <th><?php echo $this->text('Title'); ?></th>
          <th><?php echo $this->text('Status'); ?></th>
          <th><?php echo $this->text('Entity status'); ?></th>
          <th><?php echo $this->text('Weight'); ?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($items as $item) { ?>
        <tr data-collection-item-id="<?php echo $this->escape($item['collection_item']['collection_item_id']); ?>">
          <td class="middle"><input type="checkbox" class="select-all" name="selected[]" value="<?php echo $this->escape($item['collection_item']['collection_item_id']); ?>"></td>
          <td class="middle"><?php echo $this->truncate($this->escape($item['collection_item']['collection_item_id'])); ?></td>
          <td class="middle"><?php echo $this->truncate($this->escape($item['title'])); ?></td>
          <td class="middle">
            <?php if (empty($item['collection_item']['status'])) { ?>
            <i class="fa fa-square-o"></i>
            <?php } else { ?>
            <i class="fa fa-check-square-o"></i>
            <?php } ?>
          </td>
          <td class="middle">
            <?php if(!isset($item['status'])) { ?>
            <i class="fa fa-question-circle-o"></i>
            <?php } else if (empty($item['status'])){ ?>
            <i class="fa fa-square-o"></i>
            <?php } else { ?>
            <i class="fa fa-check-square-o"></i>
            <?php } ?>
          </td>
          <td class="middle">
            <i class="fa fa-arrows handle"></i> <span class="weight"><?php echo $item['collection_item']['weight']; ?></span>
          </td>
        </tr>
        <?php } ?>
      </tbody>
    </table>  
  </div> 
</div>
<?php } else { ?>
<div class="row">
  <div class="col-md-12">
    <?php echo $this->text('This collection has no items'); ?>
    <?php if ($this->access('collection_item_add')) { ?>
    <a class="btn btn-default" href="<?php echo $this->url("admin/content/collection-item/{$collection['collection_id']}/add"); ?>">
      <?php echo $this->text('Add'); ?>
    </a>
    <?php } ?>
  </div>
</div>
<?php } ?>