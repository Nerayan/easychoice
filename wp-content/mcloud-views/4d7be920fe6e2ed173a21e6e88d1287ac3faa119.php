<?php $__env->startSection('main'); ?>
    <div class="settings-body">
        <div id="s3-importer-manual-warning" style="display:none">
            <p><strong>IMPORTANT:</strong> You are running the import process in the web browser.  <strong>Do not navigate away from this page or the import may not finish.</strong></p>
        </div>
        <div id="s3-importer-instructions" <?php echo ($status=="running") ? 'style="display:none"':''; ?>>
            <?php echo $instructions; ?>

            <?php if(!empty($warning)): ?>
            <div class="s3-importer-info-warning">
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
            <?php if(!empty($options)): ?>
                <div id="s3-importer-options">
                    <h3>Options</h3>
                    <ul>
                        <?php $__currentLoopData = $options; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $optionName => $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
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
                                    <?php endif; ?>
                                    </div>
                                    <div class="description"><?php echo $option['description']; ?></div>
                                </div>
                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>
            <div style="margin-top: 2em;">
                <?php if($enabled): ?>
                    <div style="display:flex; align-items:center">
                        <a id="s3-importer-start-import" href="#" class="ilab-ajax button button-primary"><?php echo e($commandTitle); ?></a><img id="s3-importer-start-spinner" src="<?php echo admin_url('/images/spinner-2x.gif'); ?>" height="24px" style="margin-left:10px; display: none;">
                    </div>
                <?php else: ?>
                    <strong class="tool-disabled">Please <a href="admin.php?page=media-tools-top"><?php echo e($disabledText); ?></a> before using this tool.</strong>
                <?php endif; ?>
            </div>

        </div>
        <div id="s3-importer-progress" <?php echo ($status!="running") ? 'style="display:none"':''; ?>>
            <div id="s3-importer-thumbnails">
                <div id="s3-importer-thumbnails-container">
                </div>
                <div id="s3-importer-thumbnails-fade"></div>
                <img id="s3-importer-thumbnails-cloud" src="<?php echo e(ILAB_PUB_IMG_URL); ?>/icon-cloud.svg">
            </div>
            <div class="s3-importer-progress-container">
                <div id="s3-importer-progress-bar"></div>
                <div id="s3-importer-status-text">

                </div>
            </div>
            <button id="s3-importer-cancel-import" class="button button-whoa" title="Cancel"><?php echo e($cancelCommandTitle); ?></button>
        </div>
    </div>
    <?php if (\ILAB\MediaCloud\Utilities\LicensingManager::OptedIn('mcloud-opt-in-crisp', 'pro')): ?>
    <?php echo $__env->make('support.crisp', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>
    <?php endif; ?>
<?php $__env->stopSection(); ?>

<script src="<?php echo e(ILAB_PUB_JS_URL); ?>/mcloud-admin.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function(){
        new Importer({
            batchType: '<?php echo e($batchType); ?>',

            commandTitle: "<?php echo e($commandTitle); ?>",
            statusText: "",

            systemTestUrl: "<?php echo e(admin_url('admin.php?page=media-tools-troubleshooter')); ?>",

            importing: <?php echo e(($status == 'running') ? 'true' : 'false'); ?>,
            backgroundImport: <?php echo e(($background) ? 'true' : 'false'); ?>,
            fromSelection: <?php echo e(($fromSelection) ? 'true' : 'false'); ?>,

            index: 0,
            currentPage: 0,
            currentBatch: {
                posts: [],
                total: <?php echo e($total); ?>,
                pages: 0,
                shouldRun: <?php echo e(($shouldRun) ? 'true' : 'false'); ?>,
                fromSelection: <?php echo e(($fromSelection) ? 'true' : 'false'); ?>

            },

            currentFile: null,
            timingInfo: {
                totalTime: <?php echo e($totalTime); ?>,
                postsPerSecond: <?php echo e($postsPerMinute / 60.0); ?>,
                postsPerMinute: <?php echo e($postsPerMinute); ?>,
                eta: <?php echo e($eta); ?>

            },

            nonce: "<?php echo e(wp_create_nonce('importer-action')); ?>",
            nextBatchAction: "<?php echo e($nextBatchAction); ?>",
            manualAction: "<?php echo e($manualAction); ?>",
            startAction: "<?php echo e($startAction); ?>",
            cancelAction: "<?php echo e($cancelAction); ?>",
            progressAction: "<?php echo e($progressAction); ?>",

            thumbnailContainer: document.getElementById('s3-importer-thumbnails-container'),
            startButton: document.getElementById('s3-importer-start-import'),
            cancelButton: document.getElementById('s3-importer-cancel-import'),
            spinner: document.getElementById('s3-importer-start-spinner'),
            optionsContainer: document.getElementById('s3-importer-options'),
            instructionsContainer: document.getElementById('s3-importer-instructions'),
            manualWarningContainer: document.getElementById('s3-importer-manual-warning'),

            progressContainer: document.getElementById('s3-importer-progress'),
            progressBar: document.getElementById('s3-importer-progress-bar'),
            statusTextContainer: document.getElementById('s3-importer-status-text')
        });
    });
</script>
<?php echo $__env->make('../templates/sub-page', ['title' => $title], array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>