<?php
/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<form method="post" id="edit-imagestyle" class="form-horizontal">
  <input type="hidden" name="token" value="<?php echo $this->token(); ?>">
  <div class="row">
    <div class="col-md-6">
      <div class="panel panel-default">
        <div class="panel-body">
          <div class="form-group">
            <label class="col-md-2 control-label"><?php echo $this->text('Status'); ?></label>
            <div class="col-md-10">
              <div class="btn-group" data-toggle="buttons">
                <label class="btn btn-default<?php echo!empty($imagestyle['status']) ? ' active' : ''; ?>">
                  <input name="imagestyle[status]" type="radio" autocomplete="off" value="1"<?php echo!empty($imagestyle['status']) ? ' checked' : ''; ?>><?php echo $this->text('Enabled'); ?>
                </label>
                <label class="btn btn-default<?php echo empty($imagestyle['status']) ? ' active' : ''; ?>">
                  <input name="imagestyle[status]" type="radio" autocomplete="off" value="0"<?php echo empty($imagestyle['status']) ? ' checked' : ''; ?>><?php echo $this->text('Disabled'); ?>
                </label>
              </div>
              <div class="help-block">
                <?php echo $this->text('Disabled imagestyles will not process images'); ?>
              </div>
            </div>
          </div>
          <div class="form-group required<?php echo $this->error('name', ' has-error'); ?>">
            <label class="col-md-2 control-label"><?php echo $this->text('Name'); ?></label>
            <div class="col-md-10">
              <input name="imagestyle[name]" class="form-control" maxlength="32" value="<?php echo isset($imagestyle['name']) ? $this->escape($imagestyle['name']) : ''; ?>">
              <div class="help-block">
                <?php echo $this->error('name'); ?>
                <div class="text-muted">
                  <?php echo $this->text('Required. A descriptive name of the image style for administrators'); ?>
                </div>
              </div>
            </div>
          </div>
          <div class="form-group required<?php echo $this->error('actions', ' has-error'); ?>">
            <label class="col-md-2 control-label"><?php echo $this->text('Actions'); ?></label>
            <div class="col-md-10">
              <textarea name="imagestyle[actions]" rows="6" class="form-control" placeholder="<?php echo $this->text('Make thumbnail 50X50: thumbnail 50,50'); ?>"><?php echo $this->escape($imagestyle['actions']); ?></textarea>
              <div class="help-block">
                <?php echo $this->error('actions'); ?>
                <div class="text-muted">
                  <?php echo $this->text('Required. A list of actions to be applied to an image during processing with the imagestyle. One action per line. See the legend. Actions are applied from the top to bottom'); ?>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="panel panel-default">
        <div class="panel-body">
          <div class="row">
            <div class="col-md-2">
              <?php if (isset($imagestyle['imagestyle_id']) && $this->access('image_style_delete')) { ?>
              <button class="btn btn-danger delete" name="delete" value="1" onclick="return confirm(GplCart.text('Are you sure?'));">
                <?php if(empty($imagestyle['default'])){ ?>
                <i class="fa fa-trash"></i> <?php echo $this->text('Delete'); ?>
                <?php } else { ?>
                <i class="fa fa-refresh"></i> <?php echo $this->text('Reset'); ?>
                <?php } ?>
              </button>
              <?php } ?>
            </div>
            <div class="col-md-10 text-right">
              <div class="btn-toolbar">
                <a href="<?php echo $this->url('admin/settings/imagestyle'); ?>" class="btn btn-default cancel">
                  <i class="fa fa-reply"></i> <?php echo $this->text('Cancel'); ?>
                </a>
                <?php if ($this->access('image_style_edit') || $this->access('image_style_add')) { ?>
                <button class="btn btn-default save" name="save" value="1">
                  <i class="fa fa-floppy-o"></i> <?php echo $this->text('Save'); ?>
                </button>
                <?php } ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="panel panel-default">
        <div class="panel-heading"><?php echo $this->text('Legend'); ?></div>
        <div class="panel-body">
          <table class="table table-striped table-condensed">
            <thead>
              <tr>
                <td><?php echo $this->text('Key'); ?></td>
                <td><?php echo $this->text('Parameters'); ?></td>
                <td><?php echo $this->text('Description'); ?></td>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>flip</td>
                <td><?php echo $this->text('%x or %y', array('%x' => 'x', '%y' => 'y')); ?></td>
                <td><?php echo $this->text('Flip horizontally (x) or vertically (y)'); ?></td>
              </tr>
              <tr>
                <td>rotate</td>
                <td><?php echo $this->text('Integer 0 - 360'); ?></td>
                <td><?php echo $this->text('Rotate a certain number of degrees clockwise'); ?></td>
              </tr>
              <tr>
                <td>auto_orient</td>
                <td></td>
                <td><?php echo $this->text('Adjust orientation if needed using its EXIF Orientation property'); ?></td>
              </tr>
              <tr>
                <td>resize</td>
                <td><?php echo $this->text('Two integers, width and height, separated by comma'); ?></td>
                <td><?php echo $this->text('Resize to fixed width and height'); ?></td>
              </tr>
              <tr>
                <td>thumbnail</td>
                <td><?php echo $this->text('Two integers, width and height, separated by comma'); ?></td>
                <td><?php echo $this->text('Trim and resize to exact width and height'); ?></td>
              </tr>
              <tr>
                <td>fit_to_width</td>
                <td><?php echo $this->text('Integer'); ?></td>
                <td><?php echo $this->text('Shrink to the specified width while maintaining proportion (width)'); ?></td>
              </tr>
              <tr>
                <td>fit_to_height</td>
                <td><?php echo $this->text('Integer'); ?></td>
                <td><?php echo $this->text('Shrink to the specified height while maintaining proportion (height)'); ?></td>
              </tr>
              <tr>
                <td>best_fit</td>
                <td><?php echo $this->text('Two integers, width and height, separated by comma'); ?></td>
                <td><?php echo $this->text('Shrink proportionally to fit inside a box'); ?></td>
              </tr>
              <tr>
                <td>crop</td>
                <td><?php echo $this->text('Four integers, separated by comma'); ?></td>
                <td><?php echo $this->text('Crop a portion of image from x1, y1 to x2, y2'); ?></td>
              </tr>
              <tr>
                <td>fill</td>
                <td><?php echo $this->text('HEX color code'); ?></td>
                <td><?php echo $this->text('Fill with a color'); ?></td>
              </tr>
              <tr>
                <td>desaturate</td>
                <td></td>
                <td><?php echo $this->text('Desaturate (grayscale)'); ?></td>
              </tr>
              <tr>
                <td>invert</td>
                <td></td>
                <td><?php echo $this->text('Invert'); ?></td>
              </tr>
              <tr>
                <td>brightness</td>
                <td><?php echo $this->text('Integer from -255 to 255'); ?></td>
                <td><?php echo $this->text('Adjust brightness'); ?></td>
              </tr>
              <tr>
                <td>contrast</td>
                <td><?php echo $this->text('Integer from -100 to 100'); ?></td>
                <td><?php echo $this->text('Adjust contrast'); ?></td>
              </tr>
              <tr>
                <td>colorize</td>
                <td><?php echo $this->text('HEX color and decimal opacity, separated by comma, e.g #FF0000, 0.5'); ?></td>
                <td><?php echo $this->text('Colorize with the specified color and opacity'); ?></td>
              </tr>
              <tr>
                <td>edges</td>
                <td></td>
                <td><?php echo $this->text('Edges filter'); ?></td>
              </tr>
              <tr>
                <td>emboss</td>
                <td></td>
                <td><?php echo $this->text('Emboss filter'); ?></td>
              </tr>
              <tr>
                <td>mean_remove</td>
                <td></td>
                <td><?php echo $this->text('Mean removal filter'); ?></td>
              </tr>
              <tr>
                <td>blur</td>
                <td></td>
                <td><?php echo $this->text('Selective blur (one pass)'); ?></td>
              </tr>
              <tr>
                <td>sketch</td>
                <td></td>
                <td><?php echo $this->text('Sketch filter'); ?></td>
              </tr>
              <tr>
                <td>smooth</td>
                <td><?php echo $this->text('One integer, from -10 to 10'); ?></td>
                <td><?php echo $this->text('Smooth filter'); ?></td>
              </tr>
              <tr>
                <td>pixelate</td>
                <td><?php echo $this->text('One positive integer'); ?></td>
                <td><?php echo $this->text('Pixelate using blocks of pixels'); ?></td>
              </tr>
              <tr>
                <td>sepia</td>
                <td></td>
                <td><?php echo $this->text('Sepia effect (simulated)'); ?></td>
              </tr>
              <tr>
                <td>opacity</td>
                <td><?php echo $this->text('One integer or decimal'); ?></td>
                <td><?php echo $this->text('Change opacity'); ?></td>
              </tr>
              <tr>
                <td>overlay</td>
                <td><?php echo $this->text('Five values. 1-th: watermark image path, 2-th: watermark position, 3-th: opacity, 4 and 5-th: horizontal and vertical margin. E.g: "watermark.png,bottom right,.5, -10,-10"'); ?></td>
                <td><?php echo $this->text('Overlay another image (watermarking)'); ?></td>
              </tr>
              <tr>
                <td>text</td>
                <td><?php echo $this->text('7 values. 1-th: Your text, 2-th: font path, 3-th: points(32), 4-th: HEX color, 5-th: position, 6-th and 7-th: offsets. E.g: "Your Text,font.ttf,32,#FFFFFF,top,0,20"'); ?></td>
                <td><?php echo $this->text('Add image caption'); ?></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</form>