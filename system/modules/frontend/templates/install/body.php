<body class="install">
<div class="container">
  <form method="post" class="form-horizontal" autocomplete="off">
	<div class="header clearfix">
	  <div class="pull-left">
	    <a target="_blank" class="btn btn-default" href="<?php echo $url_wiki; ?>"><?php echo $this->text('Documentation'); ?></a>
		<a target="_blank" class="btn btn-default" href="<?php echo $url_licence; ?>"><?php echo $this->text('Licence'); ?></a>
	  </div>
	  <?php if($languages) { ?>
	  <div class="pull-right">
	    <div class="form-group form-inline">
		  <div class="col-md-12">
		    <label><?php echo $this->text('Language'); ?></label>
            <select class="form-control select-language">
			  <option value=""><?php echo $this->text('English'); ?></option>
			  <?php foreach($languages as $code => $info) { ?>
			  <?php if(isset($settings['store']['language']) && $settings['store']['language'] === $code) { ?>
			  <option value="<?php echo $code; ?>" selected><?php echo $this->escape($info['name']); ?></option>
			  <?php } else { ?>
			  <option value="<?php echo $code; ?>"><?php echo $this->escape($info['name']); ?></option>
			  <?php } ?>
			  <?php } ?>
			</select>
		  </div>
		</div>
	  </div>
	  <?php } ?>
	</div>
	  <div class="row">
	    <div class="col-md-12">
		  <h2><?php echo $this->text('Installing GPL Cart'); ?></h2>
		</div>
	  </div>
	  <?php if(!empty($messages)) { ?>
	  <div class="row" id="message">
	    <div class="col-md-12">
		  <?php foreach($messages as $type => $strings) { ?>
		  <div class="alert alert-<?php echo $type;?> alert-dismissible fade in">
		    <button type="button" class="close"><span aria-hidden="true">×</span></button>
		    <?php foreach($strings as $string) { ?>
			<?php echo $string; ?><br>
			<?php } ?>
		  </div>
		  <?php } ?>
		</div>
	  </div>
	  <?php } ?>
	  <div class="row">
        <div class="col-md-12">
		  <?php if($issues) { ?>
		  <div class="panel issues panel-<?php echo $issue_severity; ?>">
		    <div class="panel-heading">
			  <h4><?php echo $this->text('Issues'); ?></h4>
			</div>
			<div class="panel-body">
			  <table class="table-condensed">
			    <tbody>
				  <?php foreach($requirements as $section => $items) { ?>
				  <tr><td colspan="2"><h4><?php echo $this->escape($section); ?></h4></td></tr>
				  <?php foreach($items as $name => $info) { ?>
				  <tr class="<?php echo empty($info['status']) ? 'bg-' . $info['severity'] : ''; ?>">
				    <td><?php echo $this->text($info['message']); ?></td>
					<td><?php echo empty($info['status']) ? $this->text('No') : $this->text('Yes'); ?></td>
				  </tr>
				  <?php } ?>
				  <?php } ?>
				</tbody>
			  </table>
			</div>
		  </div>
		  <?php } ?>
		  <?php if($issue_severity == 'danger') { ?>
		  <div class="alert alert-danger">
		    <?php echo $this->text('Please fix all critical errors in your environment before continue'); ?>
		  </div>
		  <?php } else { ?>
		  <div class="panel panel-default database">
		    <div class="panel-heading">
			  <h4><?php echo $this->text('Database'); ?></h4>
			</div>
			<div class="panel-body">
			  <div class="required form-group<?php echo isset($this->errors['database']['name']) ? ' has-error' : ''; ?>">
			    <label class="col-md-3 control-label"><?php echo $this->text('Database'); ?></label>
				<div class="col-md-6">
				  <input name="settings[database][name]" class="form-control" autocomplete="off" value="<?php echo isset($settings['database']['name']) ? $settings['database']['name'] : ''; ?>">
				  <?php if(isset($this->errors['database']['name'])) { ?>
				  <div class="help-block"><?php echo $this->errors['database']['name']; ?></div>
				  <?php } ?>
				</div>
			  </div>
			  <div class="required form-group<?php echo isset($this->errors['database']['user']) ? ' has-error' : ''; ?>">
			    <label class="col-md-3 control-label"><?php echo $this->text('User'); ?></label>
				<div class="col-md-6">
				  <input name="settings[database][user]" class="form-control" autocomplete="off" value="<?php echo isset($settings['database']['user']) ? $settings['database']['user'] : 'root'; ?>">
				  <?php if(isset($this->errors['database']['user'])) { ?>
				  <div class="help-block"><?php echo $this->errors['database']['user']; ?></div>
				  <?php } ?>
				</div>
			  </div>
			  <div class="form-group<?php echo isset($this->errors['database']['password']) ? ' has-error' : ''; ?>">
			    <label class="col-md-3 control-label"><?php echo $this->text('Password'); ?></label>
				<div class="col-md-6">
				  <input type="password" name="settings[database][password]" autocomplete="off" class="form-control" value="">
				  <?php if(isset($this->errors['database']['password'])) { ?>
				  <div class="help-block"><?php echo $this->errors['database']['password']; ?></div>
				  <?php } ?>
				</div>
			  </div>
			  <?php if(empty($this->errors)) { ?>
			  <div class="form-group">
			    <div class="col-md-6 col-md-offset-3">
				  <a href="#db-advanced" data-toggle="collapse"><?php echo $this->text('Advanced'); ?> <span class="caret"></span></a>
				</div>
			  </div>
			  <?php } ?>
			  <div id="db-advanced" class="<?php echo empty($this->errors) ? ' collapse' : ''; ?>">
			    <div class="form-group">
				  <label class="col-md-3 control-label"><?php echo $this->text('Type'); ?></label>
				  <div class="col-md-3">
				    <select name="settings[database][type]" class="form-control">
					  <option value="mysql"<?php echo (isset($settings['database']['type']) && $settings['database']['type'] == 'mysql') ? ' selected' : ''; ?>><?php echo $this->text('mysql'); ?></option>
					  <option value="sqlite"<?php echo (isset($settings['database']['type']) && $settings['database']['type'] == 'sqlite') ? ' selected' : ''; ?>><?php echo $this->text('sqlite'); ?></option>
					</select>
				  </div>
				</div>
				<div class="required form-group<?php echo isset($this->errors['database']['port']) ? ' has-error' : ''; ?>">
				  <label class="col-md-3 control-label"><?php echo $this->text('Port'); ?></label>
				  <div class="col-md-3">
				    <input name="settings[database][port]" class="form-control" value="<?php echo isset($settings['database']['port']) ? $settings['database']['port'] : '3306'; ?>">
					<?php if(isset($this->errors['database']['port'])) { ?>
					<div class="help-block"><?php echo $this->errors['database']['port']; ?></div>
					<?php } ?>
				  </div>
				</div>
				<div class="required form-group<?php echo isset($this->errors['database']['host']) ? ' has-error' : ''; ?>">
				  <label class="col-md-3 control-label"><?php echo $this->text('Host'); ?></label>
				  <div class="col-md-6">
				    <input name="settings[database][host]" class="form-control" value="<?php echo isset($settings['database']['host']) ? $settings['database']['host'] : 'localhost'; ?>">
					<?php if(isset($this->errors['database']['host'])) { ?>
					<div class="help-block"><?php echo $this->errors['database']['host']; ?></div>
					<?php } ?>
				  </div>
				</div>
			  </div>
			  <?php if(isset($this->errors['database']['connect'])) { ?>
			  <div class="alert alert-danger"><?php echo $this->errors['database']['connect']; ?></div>
			  <?php } ?>
			</div>
		  </div>
		  <div class="panel panel-default store">
		    <div class="panel-heading">
			  <h4><?php echo $this->text('Site'); ?></h4>
			</div>
			<div class="panel-body">
			  <div class="required form-group<?php echo isset($this->errors['user']['email']) ? ' has-error' : ''; ?>">
			    <label class="col-md-3 control-label"><?php echo $this->text('E-mail'); ?></label>
				<div class="col-md-6">
				  <input name="settings[user][email]" class="form-control" value="<?php echo isset($settings['user']['email']) ? $settings['user']['email'] : ''; ?>">
				  <?php if(isset($this->errors['user']['email'])) { ?>
				  <div class="help-block"><?php echo $this->errors['user']['email']; ?></div>
				  <?php } ?>
				</div>
			  </div>
			  <div class="form-group<?php echo isset($this->errors['user']['password']) ? ' has-error' : ''; ?>">
			    <label class="col-md-3 control-label"><?php echo $this->text('Password'); ?></label>
				<div class="col-md-6">
				  <input type="password" name="settings[user][password]" class="form-control" value="">
				  <?php if(isset($this->errors['user']['password'])) { ?>
				  <div class="help-block"><?php echo $this->errors['user']['password']; ?></div>
				  <?php } ?>
				</div>
			  </div>
			  <hr>
			  <div class="required form-group<?php echo isset($this->errors['store']['title']) ? ' has-error' : ''; ?>">
				<label class="col-md-3 control-label"><?php echo $this->text('Store title'); ?></label>
				<div class="col-md-6">
				  <input name="settings[store][title]" class="form-control" value="<?php echo isset($settings['store']['title']) ? $settings['store']['title'] : 'GPL Cart'; ?>">
				  <?php if(isset($this->errors['store']['title'])) { ?>
				  <div class="help-block"><?php echo $this->errors['store']['title']; ?></div>
				  <?php } ?>
				</div>
			  </div>
			  <div class="form-group">
				<label class="col-md-3 control-label"><?php echo $this->text('Timezone'); ?></label>
				<div class="col-md-6">
				  <select name="settings[store][timezone]" class="form-control">
					<?php foreach($timezones as $value => $label) { ?>
					<option value="<?php echo $value; ?>"<?php echo ($settings['site']['timezone']) == $value ? ' selected' : ''; ?>><?php echo $this->escape($label); ?></option>
					<?php } ?>
				  </select>
				</div>
			  </div>
			</div>
		  </div>
		  <div class="row">
		    <div class="col-md-12">
			  <button class="btn btn-lg btn-success" name="install" value="1"><?php echo $this->text('Install now!'); ?></button>
			</div>
		  </div>
		  <?php } ?>
        </div>
	  </div>

  </form>
  
	<footer class="footer">
	  <p>&copy; <?php echo (2015 == date('Y')) ? date('Y') : '2015 - ' . date('Y'); ?>  GPL Cart.</p>
	</footer>
</div>
  
</body>