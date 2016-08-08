<div class="panel panel-default">
  <div class="panel-heading"><?php echo $this->text('Top keywords'); ?></div>
  <div class="panel-body table-responsive">
    <?php if (!empty($items)) { ?>
    <table class="table table-striped">
      <thead>
        <tr>
          <th><?php echo $this->text('Keyword'); ?></th>
          <th><?php echo $this->text('Sessions'); ?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($items as $item) { ?>
        <tr>
          <td>
            <?php if (in_array($item[0], array('(not provided)', '(not set)'), true)) { ?>
            <?php echo $this->escape($item[0]); ?>
            <?php } else { ?>
            <a target="_blank" href="https://google.com/search?q=<?php echo $this->escape($item[0]); ?>"><?php echo $this->escape($item[0]); ?></a>
            <?php } ?>
          </td>
          <td><?php echo $this->escape($item[1]); ?></td>
        </tr>
        <?php } ?>
      </tbody>
    </table>
    <?php } else { ?>
    <?php echo $this->text('No data available'); ?>
    <?php } ?>
  </div>
</div>