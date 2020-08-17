<?php /** @var \ILAB\MediaCloud\Tasks\TaskManager $manager */?>


<?php $__env->startSection('main'); ?>
    <div id="task-manager" class="settings-body" data-status-nonce="<?php echo e(wp_create_nonce('mcloud_task_status')); ?>">
        <p>The Task Manager allows you to view all of your currently running tasks, tasks that have completed and any scheduled tasks about to happen.</p>
        <p>To test your system, try launching a Test Task to see how it performs.</p>
        <div class="available-tasks">
            <h2>Available Tasks</h2>
            <div class="buttons">
                <?php $__currentLoopData = \ILAB\MediaCloud\Tasks\TaskManager::registeredTasks(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $taskClass): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if(!$taskClass::runFromTaskManager()) continue; ?>
                    <button type="button" class="button button-primary button-small button-start-task" data-task-id="<?php echo e($id); ?>" data-has-options="<?php echo e(!empty($taskClass::taskOptions())); ?>" data-nonce="<?php echo e(wp_create_nonce('mcloud_start_task')); ?>"><?php echo e($taskClass::title()); ?></button>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>

        <div class="task-list running-tasks">
            <h2>
                Running Tasks
                <div class="actions">
                    <button type="button" class="button button-cancel-all button-small" data-cancel-all-nonce="<?php echo e(wp_create_nonce('mcloud_cancel_all_tasks')); ?>" disabled="disabled">Cancel All</button>
                </div>
            </h2>
            <table class="task-table" id="running-tasks" style="display:none">
                <thead>
                <tr>
                    <th class="task">Task</th>
                    <th class="status">Status</th>
                    <th class="started">Started</th>
                    <th class="total-time">Total Time</th>
                    <th class="remaining">Remaining</th>
                    <th class="time-per">Time Per</th>
                    <th class="memory-per">Memory Per</th>
                    <th class="current">Processed</th>
                    <th class="progress">Progress</th>
                    <th class="actions">Actions</th>
                </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
            <div class="no-results" style="display:none">
                No Running Tasks
            </div>
        </div>

        <div class="task-list scheduled-tasks">
            <h2>
                Scheduled Tasks
            </h2>
            <table class="task-table" id="scheduled-tasks" style="display:none">
                <thead>
                <tr>
                    <th class="task">Task</th>
                    <th class="recurring">Recurring</th>
                    <th class="last-run">Last Run</th>
                    <th class="next-run">Next Run</th>
                    <th class="schedule">Schedule</th>
                    <th class="actions">Actions</th>
                </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
            <div class="no-results" style="display:none">
                No Scheduled Tasks
            </div>
        </div>

        <div class="task-list past-tasks">
            <h2>
                Past Tasks
                <div class="actions">
                    <button type="button" class="button button-clear-history button-small" data-clear-history-nonce="<?php echo e(wp_create_nonce('mcloud_clear_task_history')); ?>" disabled="disabled">Clear History</button>
                </div>
            </h2>
            <table class="task-table" id="past-tasks" style="display:none">
                <thead>
                <tr>
                    <th class="task">Task</th>
                    <th class="status">Status</th>
                    <th class="started">Started</th>
                    <th class="completed">Completed</th>
                    <th class="total-time">Total Time</th>
                    <th class="current">Processed</th>
                    <th class="time-per">Time Per</th>
                    <th class="memory-per">Memory Per</th>
                    <th class="progress">Progress</th>
                </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
            <div class="no-results" style="display:none">
                No Past Tasks
            </div>
        </div>
    </div>

    <script type="application/json" id="task-manager-running-tasks">
        <?php echo json_encode(array_values($manager->runningTasks()), JSON_PRETTY_PRINT); ?>

    </script>
    <script type="application/json" id="task-manager-past-tasks">
        <?php echo json_encode(\ILAB\MediaCloud\Tasks\Task::completeTasks(), JSON_PRETTY_PRINT); ?>

    </script>
    <script type="application/json" id="task-manager-scheduled-tasks">
        <?php echo json_encode(\ILAB\MediaCloud\Tasks\TaskSchedule::scheduledTasks(), JSON_PRETTY_PRINT); ?>

    </script>

    <script type="text/html" id="tmpl-running-task-template">
        <tr>
            <td class="task">{{ data.title }}</td>
            <td class="status status-{{ data.stateName }}">{{ data.stateName }}</td>
            <td class="started"></td>
            <td class="total-time"></td>
            <td class="remaining"></td>
            <td class="time-per"></td>
            <td class="memory-per">{{ data.memoryPerString }}</td>
            <td class="current">{{ data.currentItem }} of {{ data.totalItems }}</td>
            <td class="progress">
                <div class="progress-bar">
                    <div class="bar"></div>
                    <span class="amount"></span>
                </div>
            </td>
            <td class="actions">
                <button type="button" class="button button-small button-cancel-task" data-cancel-nonce="<?php echo e(wp_create_nonce('mcloud_cancel_task')); ?>">Cancel</button>
            </td>
        </tr>
    </script>

    <script type="text/html" id="tmpl-past-task-template">
        <tr class="status-{{ data.stateName }}">
            <td class="task">{{ data.title }}</td>
            <td class="status status-{{ data.stateName }}">{{ data.stateName }}</td>
            <td class="started"></td>
            <td class="completed"></td>
            <td class="total-time"></td>
            <td class="current">{{ data.currentItem }} of {{ data.totalItems }}</td>
            <td class="time-per"></td>
            <td class="memory-per">{{ data.memoryPerString }}</td>
            <td class="progress">
                <div class="progress-bar">
                    <div class="bar"></div>
                    <span class="amount"></span>
                </div>
            </td>
        </tr>
    </script>

    <script type="text/html" id="tmpl-scheduled-task-template">
        <tr>
            <td class="task">{{data.taskTitle}}</td>
            <td class="recurring"><# if (data.recurring) { #>Yes<# } else { #>No<# } #></td>
            <td class="last-run">{{data.lastRun}}</td>
            <td class="next-run">{{data.nextRun}}</td>
            <td class="schedule">{{data.description}}</td>
            <td class="actions">
                <button type="button" class="button button-small button-execute-task" data-execute-nonce="<?php echo e(wp_create_nonce('mcloud_execute_scheduled_task')); ?>">Execute</button>
                <button type="button" class="button button-small button-delete-task" data-delete-nonce="<?php echo e(wp_create_nonce('mcloud_delete_scheduled_task')); ?>">Delete</button>
            </td>
        </tr>
    </script>

    <?php $__currentLoopData = \ILAB\MediaCloud\Tasks\TaskManager::registeredTasks(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $identifier => $taskClass): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php if(empty($taskClass::taskOptions())) continue; ?>
    <script type="text/html" id="tmpl-<?php echo e($identifier); ?>-modal" data-options-for="<?php echo e($identifier); ?>">
        <div class="task-options-modal">
            <div class="task-modal">
                <h2><?php echo e($taskClass::title()); ?> Options</h2>
                <form>
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
                                            <div id="<?php echo e($optionName); ?>-display" class="media-select-label">All Media Items</div><input type="hidden" name="<?php echo e($optionName); ?>"><button type="button" class="button button-small button-primary button-select-media">Select Media</button><button type="button" class="button button-small button-primary button-clear-media">Clear Selection</button>
                                        <?php elseif($option['type'] == 'number'): ?>
                                            <input type="number" name="<?php echo e($optionName); ?>" value="<?php echo e($option['default']); ?>" min="<?php echo e($option['min']); ?>" max="<?php echo e($option['max']); ?>" step="<?php echo e($option['step']); ?>">
                                        <?php endif; ?>
                                    </div>
                                    <div class="description"><?php echo $option['description']; ?></div>
                                </div>
                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </form>
                <div class="buttons">
                    <button type="button" class="button button-modal-cancel">Cancel</button>
                    <button type="button" class="button button-primary button-modal-run-task">Run Task</button>
                </div>
            </div>
        </div>
    </script>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('../templates/sub-page', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>