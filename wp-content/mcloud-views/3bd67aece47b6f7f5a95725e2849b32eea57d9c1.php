<?php $__env->startSection('header'); ?>
    <div class="header-actions">
        <a href="https://kb.mediacloud.press/articles/documentation/tools/image-size-manager" target="_blank"  data-article-sidebar="https://kb.mediacloud.press/articles/documentation/tools/image-size-manager" class="button button-primary"><?php \ILAB\MediaCloud\Utilities\View::InlineImage('ilab-ui-icon-help.svg'); ?> Help</a>
        <a href="https://www.youtube.com/watch?v=blFUKzOsbXs&t=809s" class="button button-primary mediabox"><?php \ILAB\MediaCloud\Utilities\View::InlineImage('ilab-ui-icon-youtube.svg'); ?> Tutorial</a>
        <div class="spacer"></div>
        <a href="<?php echo e(parse_url(get_admin_url(null, 'admin-ajax.php'), PHP_URL_PATH) . "?action=ilab_new_image_size_page"); ?>" class="button button-primary ilab-thickbox"><?php \ILAB\MediaCloud\Utilities\View::InlineImage('ilab-ui-icon-add.svg'); ?> Add New Image Size</a>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('main'); ?>
    <div class="settings-body">
        <p>This page will allow you to manage WordPress image sizes.</p>
    </div>
    <div class="settings-body">
        <table class="ilab-image-sizes">
            <thead>
                <th>Type</th>
                <th>Title</th>
                <th>Size</th>
                <th>Width</th>
                <th>Height</th>
                <th>Crop</th>
                <th>Crop X Axis</th>
                <th>Crop Y Axis</th>
                <th></th>
            </thead>
            <tbody>
            <?php $__currentLoopData = $wpSizes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $size): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr class="ilab-size-row ilab-fixed-size-row">
                    <input type="hidden" name="nonce" value="<?php echo e(wp_create_nonce('custom-size')); ?>">
                    <input type="hidden" name="size" value="<?php echo e($size['size']); ?>">
                    <td><?php echo e($size['type']); ?></td>
                    <td><?php echo e($size['title']); ?></td>
                    <td><?php echo e($size['size']); ?></td>
                    <td><?php echo e($size['width']); ?></td>
                    <td><?php echo e($size['height']); ?></td>
                    <td class="center"><?php echo $__env->make('base/ui/checkbox', ['name' => 'crop', 'value' => !empty($size['crop']), 'description' => 'Crop Enabled', 'enabled' => false], array_except(get_defined_vars(), array('__data', '__path')))->render(); ?></td>
                    <td class="center">
                        <select name="x-axis" disabled="disabled">
                            <option <?php echo e(($size['x-axis'] == 'left') ? 'selected' : ''); ?> value="left">Left</option>
                            <option <?php echo e((empty($size['x-axis']) || ($size['x-axis'] == 'center')) ? 'selected' : ''); ?> value="center">Center</option>
                            <option <?php echo e(($size['x-axis'] == 'right') ? 'selected' : ''); ?> value="right">Right</option>
                        </select>
                    </td>
                    <td class="center">
                        <select name="y-axis" disabled="disabled">
                            <option <?php echo e(($size['y-axis'] == 'top') ? 'selected' : ''); ?> value="top">Top</option>
                            <option <?php echo e((empty($size['y-axis']) || ($size['y-axis'] == 'center')) ? 'selected' : ''); ?> value="center">Center</option>
                            <option <?php echo e(($size['y-axis'] == 'bottom') ? 'selected' : ''); ?> value="bottom">Bottom</option>
                        </select>
                    </td>
                    <td class="center">
                        <a class="ilab-delete-size-button disabled">Delete</a>
                        <?php if($hasDynamic): ?>
                        <a href="#" class="ilab-size-settings-button">Settings</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php $__currentLoopData = $sizes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $size): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr class="ilab-size-row ilab-custom-size-row">
                    <input type="hidden" name="nonce" value="<?php echo e(wp_create_nonce('custom-size')); ?>">
                    <input type="hidden" name="size" value="<?php echo e($size['size']); ?>">
                    <td><?php echo e($size['type']); ?></td>
                    <td><?php echo e($size['title']); ?></td>
                    <td><?php echo e($size['size']); ?></td>
                    <td><input type="number" min="0" max="99999" name="width" value="<?php echo e($size['width']); ?>"></td>
                    <td><input type="number" min="0" max="99999" name="height" value="<?php echo e($size['height']); ?>"></td>
                    <td class="center"><?php echo $__env->make('base/ui/checkbox', ['name' => $size['size'].'__crop', 'value' => !empty($size['crop']), 'description' => 'Crop Enabled', 'enabled' => true], array_except(get_defined_vars(), array('__data', '__path')))->render(); ?></td>
                    <td class="center">
                        <select name="x-axis">
                            <option <?php echo e(($size['x-axis'] == 'left') ? 'selected' : ''); ?> value="left">Left</option>
                            <option <?php echo e((empty($size['x-axis']) || ($size['x-axis'] == 'center')) ? 'selected' : ''); ?> value="center">Center</option>
                            <option <?php echo e(($size['x-axis'] == 'right') ? 'selected' : ''); ?> value="right">Right</option>
                        </select>
                    </td>
                    <td class="center">
                        <select name="y-axis">
                            <option <?php echo e(($size['y-axis'] == 'top') ? 'selected' : ''); ?> value="top">Top</option>
                            <option <?php echo e((empty($size['y-axis']) || ($size['y-axis'] == 'center')) ? 'selected' : ''); ?> value="center">Center</option>
                            <option <?php echo e(($size['y-axis'] == 'bottom') ? 'selected' : ''); ?> value="bottom">Bottom</option>
                        </select>
                    </td>
                    <td class="center">
                        <a href="#" class="ilab-delete-size-button">Delete</a>
                        <?php if($hasDynamic): ?>
                        <a href="#" class="ilab-size-settings-button">Settings</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('../templates/sub-page', ['title' => $title], array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>