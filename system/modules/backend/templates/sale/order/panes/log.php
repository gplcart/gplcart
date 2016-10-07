<?php if (!empty($items)) { ?>
<div class="panel panel-default">
  <div class="panel-heading"><?php echo $this->text('Logs'); ?></div>
  <div class="panel-body">
    <table class="table table-condensed">
      <?php foreach ($items as $item) { ?>
      <tr>
        <td>
          <div class="small created">
            <span class="fa fa-clock-o"></span> <?php echo $this->date($item['created']); ?>
          </div>
          <div class="text"><?php echo $this->escape($item['text']); ?></div>
        </td>
      </tr>
      <?php } ?>
    </table>
  </div>
</div>
<?php } ?>

