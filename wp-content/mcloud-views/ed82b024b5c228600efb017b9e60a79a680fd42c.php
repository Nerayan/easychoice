<?php /** @var string $instructions */ ?>
<?php /** @var string $warning */ ?>
<?php /** @var string $commandLine */ ?>
<?php /** @var string $commandLink */ ?>
<?php /** @var string $taskClass */ ?>
<?php /** @var string $instructionsView */ ?>

<?php echo $__env->make($instructionsView, array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>
<?php if(!empty($warning)): ?>
    <div class="info-warning">
        <h4>Warning</h4>
        <?php echo $warning; ?>

    </div>
<?php endif; ?>
<?php if(!empty($commandLine)): ?>
    <div class="wp-cli-callout">
        <h3>Using WP-CLI</h3>
        <p>You can run this importer process from the command line using <a href="https://wp-cli.org" target="_blank">WP-CLI</a>:</p>
        <code>
            <?php echo e($commandLine); ?>

        </code>
        <?php if(!empty($commandLink)): ?>
            <p><a href="<?php echo e($commandLink); ?>" target="_blank">Command documentation</a></p>
        <?php endif; ?>
    </div>
<?php endif; ?>
<?php if(!empty($taskClass::taskOptions())): ?>
    <div id="task-options" class="task-options">
        <h3>Options</h3>
        <ul>
            <?php $__currentLoopData = $taskClass::taskOptions(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $optionName => $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li>
                    <div>
                        <?php echo $option['title']; ?>

                    </div>
                    <div>
                        <div class="option-ui option-ui-<?php echo e($option['type']); ?>">
                            <?php if($option['type'] == 'checkbox'): ?>
                                <?php echo $__env->make('base/ui/checkbox', ['name' => $optionName, 'value' => $option['default'], 'description' => '', 'enabled' => true], array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>
                            <?php elseif($option['type'] == 'select'): ?>
                                <select name="<?php echo e($optionName); ?>">
                                    <?php $__currentLoopData = $option['options']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $suboptionValue => $suboptionName): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($suboptionValue); ?>"><?php echo e($suboptionName); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            <?php elseif($option['type'] == 'browser'): ?>
                                <input type="text" name="<?php echo e($optionName); ?>" disabled="disabled" value="<?php echo e($option['default']); ?>"><button type="button" class="button button-small button-primary" data-nonce="<?php echo e(wp_create_nonce('storage-browser')); ?>">Browse</button>
                            <?php elseif($option['type'] == 'media-select'): ?>
                                <div id="<?php echo e($optionName); ?>-display" class="media-select-label">All Media Items</div><input type="hidden" name="<?php echo e($optionName); ?>"><button type="button" class="button button-small button-primary button-select-media" <?php if(!empty($option['media-types'])): ?>data-media-types="<?php echo e(implode(',',$option['media-types'])); ?>" <?php endif; ?>>Select Media</button><button type="button" class="button button-small button-primary button-clear-media">Clear Selection</button>
                            <?php endif; ?>
                        </div>
                        <div class="description"><?php echo $option['description']; ?></div>
                    </div>
                </li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    </div>
<?php endif; ?>