<?php if (empty($rule)) {
    ?>
<tr>
  <td colspan="2"><span class="text-danger"><?php echo $this->text('Unknown');
    ?></span></td>
</tr>
<?php 
} else {
    ?>
<tr class="active">
</tr>
<tr>
  <td>
    <?php if (empty($rule['name'])) {
    ?>
    <span class="text-danger"><?php echo $this->text('Unknown');
    ?></span>
    <?php 
} else {
    ?>
    <?php echo $this->text($rule['name']);
    ?>
    <?php 
}
    ?>
  </td>
  <td>
    <?php echo $this->escape($price);
    ?>
  </td>
</tr>
<?php 
} ?>
