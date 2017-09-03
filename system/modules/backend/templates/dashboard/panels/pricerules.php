<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 * @var $this \gplcart\core\controllers\backend\Controller
 * To see available variables <?php print_r(get_defined_vars()); ?>
 */
?>
<?php if($this->access('price_rule')) { ?>
<div class="panel panel-default">
  <div class="panel-heading">
    <?php echo $this->e($content['title']); ?>
  </div>
  <div class="panel-body">
    <?php if (!empty($content['data'])) { ?>
    <table class="table table-condensed">
      <tbody>
        <?php foreach ($content['data'] as $item) { ?>
        <tr>
          <td>
            <?php if($this->access('price_rule_edit')) { ?>
            <a href="<?php echo $this->url("admin/sale/price/edit/{$item['price_rule_id']}"); ?>">
              <?php echo $this->truncate($this->e($item['name']), 50); ?>
            </a>
            <?php } else { ?>
            <?php echo $this->truncate($this->e($item['name']), 50); ?>
            <?php } ?>
          </td>
          <td>
            <?php echo $this->e($item['value_formatted']); ?>
          </td>
          <td>
            <?php echo $this->date($item['created']); ?>
          </td>
        </tr>
        <?php } ?>
      </tbody>
    </table>
    <div class="text-right">
      <a href="<?php echo $this->url('admin/sale/price'); ?>">
        <?php echo $this->text('See all'); ?>
      </a>
    </div>
    <?php } else { ?>
    <?php echo $this->text('There no items yet'); ?>
    <?php } ?>
  </div>
</div>
<?php } ?>