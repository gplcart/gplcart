<?php if ($this->access('currency_add')) {
    ?>
<div class="row">
  <div class="col-md-6 col-md-offset-6 text-right">
    <div class="btn-group">
      <a href="<?php echo $this->url('admin/settings/currency/add');
    ?>" class="btn btn-success">
        <i class="fa fa-plus"></i> <?php echo $this->text('Add');
    ?>
      </a>
    </div>
  </div>
</div>
<?php 
} ?>
<div class="row">
  <div class="col-md-12">
    <table class="table table-responsive margin-top-20 currencies">
      <thead>
        <tr>
          <th><?php echo $this->text('Name'); ?></th>
          <th><?php echo $this->text('Code'); ?></th>
          <th><?php echo $this->text('Symbol'); ?></th>
          <th><?php echo $this->text('Convertion rate'); ?></th>
          <th><?php echo $this->text('Default'); ?></th>
          <th><?php echo $this->text('Enabled'); ?></th>
          <th><?php echo $this->text('Action'); ?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($currencies as $code => $currency) {
    ?>
        <tr>
          <td class="middle"><?php echo $this->escape($currency['name']);
    ?></td>
          <td class="middle"><?php echo $this->escape($code);
    ?></td>
          <td class="middle"><?php echo $this->escape($currency['symbol']);
    ?></td>
          <td class="middle"><?php echo $this->escape($currency['convertion_rate']);
    ?></td>
          <td class="middle"><?php echo($default_currency == $code) ? '<i class="fa fa-check-square-o"></i>' : '<i class="fa fa-square-o"></i>';
    ?></td>
          <td class="middle"><?php echo (!empty($currency['status']) || $default_currency == $code) ? '<i class="fa fa-check-square-o"></i>' : '<i class="fa fa-square-o"></i>';
    ?></td>
          <td class="middle">
            <?php if ($this->access('currency_edit')) {
    ?>
            <a href="<?php echo $this->url("admin/settings/currency/edit/$code");
    ?>" title="<?php echo $this->text('Edit');
    ?>" class="btn btn-default edit">
              <i class="fa fa-edit"></i>
            </a>
            <?php 
}
    ?>
          </td>
        </tr>
        <?php 
} ?>
      </tbody>
    </table>
  </div>
</div>