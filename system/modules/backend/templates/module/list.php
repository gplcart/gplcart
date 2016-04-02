<?php if ($modules) {
    ?>
<div class="row margin-top-20">
  <div class="col-md-12">
    <table class="table modules">
      <thead>
        <tr>
          <th><?php echo $this->text('Name');
    ?></th>
          <th><?php echo $this->text('Description');
    ?></th>
          <th><?php echo $this->text('Version');
    ?></th>
          <th><?php echo $this->text('Core');
    ?></th>
          <th><?php echo $this->text('Type');
    ?></th>
          <th><?php echo $this->text('Status');
    ?></th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($modules as $module_id => $info) {
    ?>
        <tr>
          <td>
            <div class="icon-name">
              <div class="pull-left icon">
                <?php if ($info['image']) {
    ?>
                <img src="<?php echo $this->escape($info['image']);
    ?>">
                <?php 
} else {
    ?>
                <i class="fa fa-cube fa-2x fa-border placeholder"></i>
                <?php 
}
    ?>
              </div>
              <div class="name pull-left">
                <a href="#" onclick="return false;" data-toggle="collapse" data-target="#module-details-<?php echo $module_id;
    ?>">
                  <?php echo $this->truncate($this->escape($info['name']));
    ?>
                </a>
              </div>
            </div>
          </td>
          <td class="middle">
            <?php echo $info['description'] ? $this->truncate($this->xss($info['description'])) : '';
    ?>
          </td>
          <td class="middle">
            <?php echo $info['version'] ? $this->escape($info['version']) : $this->text('Unknown');
    ?>
          </td>
          <td class="middle">
            <?php echo $this->escape($info['core']);
    ?>
          </td>
          <td class="middle">
            <?php echo empty($info['type']) ? $this->text('Module') : $this->escape($info['type_name']);
    ?>
          </td>
          <td class="middle">
            <?php if (isset($info['status'])) {
    ?>
            <?php if ($info['status']) {
    ?>
            <span class="text-success"><?php echo $this->text('Enabled');
    ?></span>
            <?php 
} else {
    ?>
            <span class="text-danger"><?php echo $this->text('Disabled');
    ?></span>
            <?php 
}
    ?>
            <?php 
} else {
    ?>
            <span class="text-warning"><?php echo $this->text('Not installed');
    ?></span>
            <?php 
}
    ?>
          </td>
          <td class="middle">
            <div class="btn-group">
              <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                <i class="fa fa-bars"></i>
              </button>
              <ul class="dropdown-menu dropdown-menu-right">
                <?php if (isset($info['status'])) {
    ?>
                <?php if ($info['status']) {
    ?>
                <?php if ($this->access('module_disable') && empty($info['always_enabled'])) {
    ?>
                <li>
                  <a href="<?php echo $this->url(false, array('action' => 'disable', 'module_id' => $module_id, 'token' => $token));
    ?>">
                    <?php echo $this->text('Disable');
    ?>
                  </a>
                </li>
                <?php 
}
    ?>
                <?php 
} else {
    ?>
                <?php if ($this->access('module_enable')) {
    ?>
                <li>
                  <a href="<?php echo $this->url(false, array('action' => 'enable', 'module_id' => $module_id, 'token' => $token));
    ?>">
                  <?php echo $this->text('Enable');
    ?>
                  </a>
                </li>
                <?php 
}
    ?>
                <?php if ($this->access('module_uninstall') && empty($info['always_enabled'])) {
    ?>
                <li>
                  <a href="<?php echo $this->url(false, array('action' => 'uninstall', 'module_id' => $module_id, 'token' => $token));
    ?>">
                  <?php echo $this->text('Uninstall');
    ?>
                  </a>
                </li>
                <?php 
}
    ?>
                <?php 
}
    ?>
                <?php 
} else {
    ?>
                <?php if ($this->access('module_install')) {
    ?>
                <li>
                  <a href="<?php echo $this->url(false, array('action' => 'install', 'module_id' => $module_id, 'token' => $token));
    ?>">
                    <?php echo $this->text('Install');
    ?>
                  </a>
                </li>
                <?php 
}
    ?>
                <?php 
}
    ?>
                <?php if (isset($info['status']) && $info['configure'] && $this->access('module_edit')) {
    ?>
                <li>
                  <a href="<?php echo $this->url($info['configure']);
    ?>">
                    <?php echo $this->text('Configure');
    ?>
                  </a>
                </li>
                <?php 
}
    ?>
                <li>
                  <a href="#" onclick="return false;" data-toggle="collapse" data-target="#module-details-<?php echo $module_id;
    ?>">
                    <?php echo $this->text('Details');
    ?>
                  </a>
                </li>
              </ul>
            </div>
          </td>
        </tr>
        <tr class="collapse active" id="module-details-<?php echo $module_id;
    ?>">
          <td colspan="7">
            <?php if ($info['author']) {
    ?>
            <b><?php echo $this->text('Author');
    ?></b>: <?php echo $this->escape($info['author']);
    ?><br>
            <?php 
}
    ?>
            <?php if ($info['description']) {
    ?>
            <b><?php echo $this->text('Description');
    ?></b>: <?php echo $this->xss($info['description']);
    ?><br>
            <?php 
}
    ?>
            <?php if ($info['dependencies']) {
    ?>
            <b><?php echo $this->text('Dependencies');
    ?></b>: <?php echo $this->escape(implode(',', $info['dependencies']));
    ?><br>
            <?php 
}
    ?>
            <?php if (isset($info['weight'])) {
    ?>
            <b><?php echo $this->text('Weight');
    ?></b>: <?php echo $this->escape($info['weight']);
    ?>
            <?php 
}
    ?>
          </td>
        </tr>
        <?php 
}
    ?>
      </tbody>
    </table>
  </div>
</div>
<?php 
} else {
    ?>
<div class="row">
  <div class="col-md-12"><?php echo $this->text('No modules');
    ?></div>
</div>
<?php 
} ?>