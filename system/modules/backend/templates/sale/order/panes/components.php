<div class="panel panel-default">
  <div class="panel-heading"><?php echo $this->text('Components'); ?></div>
  <div class="panel-body">
    <div class="row">
        <div class="col-md-12">
          <table class="table table-condensed">
            <tbody>
              <?php foreach($components as $component) { ?>
              <?php echo $component; ?>
              <?php } ?>
            </tbody>
          </table>
        </div>
    </div>
  </div>
</div>