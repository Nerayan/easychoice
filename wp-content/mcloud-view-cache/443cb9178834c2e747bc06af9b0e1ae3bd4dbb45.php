<?php /** @var string $taskClass */ ?>
<?php echo $__env->make('tasks.batch-info', [
    'instructionsView' => $taskClass::instructionView(),
    'commandLine' => 'wp mediacloud regenerate',
    'commandTitle' => 'Regenerate Thumbnails',
    'commandLink'=> 'https://kb.mediacloud.press/articles/advanced-usage/command-line/regenerate-thumbnails',
    'warning' => $warning,
    'taskClass' => $taskClass
], array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>