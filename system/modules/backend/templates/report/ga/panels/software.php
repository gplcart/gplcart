<div class="panel panel-default">
  <div class="panel-heading"><?php echo $this->text('Top software'); ?></div>
  <div class="panel-body table-responsive">
    <?php if (!empty($items)) { ?>
    <table class="table ga-software">
      <thead>
        <tr>
          <th><?php echo $this->text('OS'); ?></th>
          <th><?php echo $this->text('Browser'); ?></th>
          <th><?php echo $this->text('Sessions'); ?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($items as $item) { ?>
        <tr>
          <td><?php echo $this->escape($item[0]); ?></td>
          <td><?php echo $this->escape($item[1]); ?></td>
          <td><?php echo $this->escape($item[2]); ?></td>
        </tr>
        <?php } ?>
      </tbody>
    </table>
    <?php } else { ?>
    <?php echo $this->text('No data available'); ?>
    <?php } ?>
  </div>
</div>