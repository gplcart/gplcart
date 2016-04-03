<div class="row">
  <div class="col-md-3">
    <div class="list-group">
      <a href="<?php echo $this->url("account/{$user['user_id']}"); ?>" class="list-group-item">
        <h4 class="list-group-item-heading"><span class="fa fa-user"></span> <?php echo $this->truncate($this->escape($user['name']), 20); ?></h4>
        <p class="list-group-item-text"><?php echo $this->escape($user['email']); ?></p>
      </a>
      <a href="<?php echo $this->url("account/{$user['user_id']}/address"); ?>" class="list-group-item active">
        <h4 class="list-group-item-heading"><?php echo $this->text('Addresses'); ?></h4>
        <p class="list-group-item-text"><?php echo $this->text('View and manage addressbook'); ?></p>
      </a>
      <a href="<?php echo $this->url("account/{$user['user_id']}/edit"); ?>" class="list-group-item">
        <h4 class="list-group-item-heading"><?php echo $this->text('Settings'); ?></h4>
        <p class="list-group-item-text"><?php echo $this->text('Edit account details'); ?></p>
      </a>
    </div>
    <a href="<?php echo $this->url('logout'); ?>">
      <span class="fa fa-sign-out"></span> <?php echo $this->text('Log out'); ?>
    </a>
  </div>
  <div class="col-md-9">
    <form method="post" id="edit-address" class="form-horizontal margin-top-20">
      <input type="hidden" name="token" value="<?php echo $token; ?>">
      <?php foreach ($format as $key => $value) { ?>
      <div class="record <?php echo $key; ?>"<?php echo empty($value['status']) ? ' style="display:none;"' : ''; ?>>
        <div class="form-group <?php echo $key; ?><?php echo isset($form_errors[$key]) ? ' has-error' : ''; ?>">
          <label class="col-md-2 control-label">
            <?php if (!empty($value['required'])) { ?><span class="text-danger">* </span><?php } ?>
            <?php echo $this->escape($value['name']); ?>
          </label>
          <div class="col-md-6">
            <?php if ($key == 'country') { ?>
            <select class="form-control" name="address[country]">
              <?php foreach ($countries as $code => $name) { ?>
              <option value="<?php echo $code; ?>"<?php echo (isset($address['country']) && $address['country'] == $code) ? ' selected' : ''; ?>><?php echo $this->escape($name); ?></option>
              <?php } ?>
            </select>
            <?php } elseif ($key == 'state_id') { ?>
            <select class="form-control" name="address[state_id]">
              <option value=""><?php echo $this->text('None'); ?></option>
              <?php foreach ($states as $state_id => $state) { ?>
              <option value="<?php echo $state_id; ?>"<?php echo (isset($address['state_id']) && $address['state_id'] == $state_id) ? ' selected' : ''; ?>><?php echo $this->escape($state['name']); ?></option>
              <?php } ?>
            </select>
            <?php } else { ?>
            <input name="address[<?php echo $key; ?>]" maxlength="255" class="form-control" value="<?php echo isset($address[$key]) ? $this->escape($address[$key]) : ''; ?>">
            <?php } ?>
            <?php if (isset($form_errors[$key])) { ?>
            <div class="help-block"><?php echo $form_errors[$key]; ?></div>
            <?php } ?>  
          </div>
        </div>
      </div>
      <?php } ?>
      <div class="row">
        <div class="col-md-4 col-md-offset-2">
          <a class="btn btn-default" href="<?php echo $this->url("account/{$user['user_id']}/address"); ?>">
              <?php echo $this->text('Cancel'); ?>
          </a>
        </div>
        <div class="col-md-2 text-right">
          <button class="btn btn-primary save" name="save" value="1">
            <i class="fa fa-floppy-o"></i> <?php echo $this->text('Save'); ?>
          </button>
        </div>
      </div>
    </form>
  </div>
</div>
<script>
$(function () {

    $('#edit-address select[name$="[country]"]').change(function () {

        var form = $(this).closest('form');
        var selectState = form.find('select[name$="[state_id]"]');
        
        // Clear up old errors
        $('#edit-address .form-group.has-error .help-block').remove();
        $('#edit-address .form-group.has-error').removeClass('has-error');

        $.ajax({
            url: '<?php echo $this->url('ajax'); ?>',
            method: 'POST',
            dataType: 'json',
            data: {action: 'getCountryData', token: '<?php echo $token; ?>', country: $(this).val()},
            success: function (data) {
                if (typeof data === 'object' && 'states' in data) {

                    var options = '';
                    $.each(data.states, function (code, state) {
                        options += '<option value="' + code + '">' + state.name + '</option>';
                    });

                    selectState.html(options);

                    form.find('div.record').hide();
                    $.each(data.format, function (i, field) {
                        form.find('div.record.' + field).show();
                    });
                }
            }
        });
    });
});
</script>