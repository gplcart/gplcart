<?php if (empty($service)) {
    ?>
<tr>
  <td colspan="2"><span class="text-danger"><?php echo $this->text('Unknown');
    ?></span></td>
</tr>
<?php 
} else {
    ?>
<tr class="active">
  <td colspan="2"><?php echo $this->escape($service['cart']['type']);
    ?></td>
</tr>
<tr>
  <td>
    <?php if (empty($service['name'])) {
    ?>
    <span class="text-danger"><?php echo $this->text('Unknown');
    ?></span>
    <?php 
} else {
    ?>
    <?php echo $this->text($service['name']);
    ?>
    <?php if (!empty($service['description'])) {
    ?>
    <br><span class="small text-muted"><?php echo $this->xss($service['description']);
    ?></span>
    <?php 
}
    ?>
    <?php 
}
    ?>
  </td>
  <td>
    <?php echo $this->escape($service['cart']['price_formatted']);
    ?>
  </td>
</tr>
<?php 
} ?>
