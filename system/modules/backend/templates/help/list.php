<?php if ($help_list) {
    ?>
<ul class="list-unstyled">
  <?php foreach ($help_list as $header => $href) {
    ?>
  <li>
    <a href="<?php echo $this->escape($href);
    ?>"><?php echo $this->escape($header);
    ?></a>
  </li>
  <?php 
}
    ?> 
</ul>
<?php 
} else {
    ?>
<?php echo $this->text('No help files found');
    ?>
<?php 
} ?>




