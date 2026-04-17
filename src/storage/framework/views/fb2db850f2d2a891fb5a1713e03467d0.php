<?php if (isset($component)) { $__componentOriginal166a02a7c5ef5a9331faf66fa665c256 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal166a02a7c5ef5a9331faf66fa665c256 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament-panels::components.page.index','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament-panels::page'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <?php
        $data = $this->getData();
    ?>

    <div class="space-y-4">
        
        <div class="space-y-4">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Monitoring Patrol</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Rekap harian patrol 1 bulan</p>
                </div>
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                    <select wire:model.live="selectedMonth" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $this->getMonths(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $num => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($num); ?>" <?php if($num == $data['month']): echo 'selected'; endif; ?>><?php echo e($name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                    </select>
                    <select wire:model.live="selectedYear" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $this->getYears(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $year): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($year); ?>" <?php if($year == $data['year']): echo 'selected'; endif; ?>><?php echo e($year); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                    </select>
                </div>
            </div>

            
            <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
                <table class="w-full border-collapse text-sm">
                    
                    <thead>
                        <tr class="bg-gray-100 dark:bg-gray-700">
                            <th class="sticky left-0 z-20 border border-gray-200 dark:border-gray-600 px-3 py-2 text-left text-xs font-semibold text-gray-900 dark:text-white bg-gray-100 dark:bg-gray-700" style="min-width: 140px" rowspan="2">
                                Nama PIC
                            </th>
                            <th class="sticky left-[140px] z-20 border border-gray-200 dark:border-gray-600 px-3 py-2 text-left text-xs font-semibold text-gray-900 dark:text-white bg-gray-100 dark:bg-gray-700" style="min-width: 160px" rowspan="2">
                                Checkpoint
                            </th>
                            <!--[if BLOCK]><![endif]--><?php for($day = 1; $day <= $data['days_in_month']; $day++): ?>
                                <?php 
                                    $firstRowKey = array_key_first($data['table_data']) ?? null;
                                    $date = $firstRowKey && isset($data['table_data'][$firstRowKey]['daily_data'][$day]) 
                                        ? $data['table_data'][$firstRowKey]['daily_data'][$day]['date'] 
                                        : null;
                                ?>
                                <th colspan="<?php echo e(count($data['shifts'])); ?>" class="border border-gray-200 dark:border-gray-600 px-1 py-2 text-center text-xs font-semibold text-gray-900 dark:text-white bg-gray-100 dark:bg-gray-700">
                                    <span class="block text-sm font-bold leading-tight"><?php echo e($day); ?></span>
                                    <span class="text-[10px] font-normal text-gray-400 dark:text-gray-500"><?php echo e($date?->format('D') ?? ''); ?></span>
                                </th>
                            <?php endfor; ?><!--[if ENDBLOCK]><![endif]-->
                        </tr>
                        
                        
                        <tr class="bg-gray-100 dark:bg-gray-700 border-b-2 border-gray-300 dark:border-gray-500">
                            <!--[if BLOCK]><![endif]--><?php for($day = 1; $day <= $data['days_in_month']; $day++): ?>
                                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $data['shifts']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $shift): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <th class="border border-gray-200 dark:border-gray-600 px-1 py-1.5 text-center text-[10px] font-semibold text-gray-500 dark:text-gray-400" style="min-width: 38px">
                                        <?php echo e($shift->name); ?>

                                    </th>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                            <?php endfor; ?><!--[if ENDBLOCK]><![endif]-->
                        </tr>
                    </thead>

                    
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <!--[if BLOCK]><![endif]--><?php $__empty_1 = true; $__currentLoopData = $data['table_data']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rowKey => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                                
                                <td class="sticky left-0 z-10 border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-2 text-sm font-medium text-gray-900 dark:text-white">
                                    <?php echo e($row['user_name']); ?>

                                </td>

                                
                                <td class="sticky left-[140px] z-10 border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-2">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white"><?php echo e($row['location_name']); ?></p>
                                        <!--[if BLOCK]><![endif]--><?php if(count($row['shifts_used']) > 0): ?>
                                            <div class="mt-1 flex gap-1 flex-wrap">
                                                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $data['shifts']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $shift): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <span class="inline-block px-1.5 py-0.5 text-[10px] font-medium rounded
                                                        <?php echo e(in_array($shift->id, $row['shifts_used'])
                                                            ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300'
                                                            : 'bg-gray-100 text-gray-400 line-through dark:bg-gray-700 dark:text-gray-500'); ?>">
                                                        <?php echo e($shift->name); ?>

                                                    </span>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                            </div>
                                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                    </div>
                                </td>

                                
                                <!--[if BLOCK]><![endif]--><?php for($day = 1; $day <= $data['days_in_month']; $day++): ?>
                                    <?php 
                                        $dayInfo = $row['daily_data'][$day];
                                        $isWeekend = in_array($dayInfo['date']->dayOfWeek, [0, 6]);
                                    ?>
                                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $data['shifts']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $shift): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php $status = $dayInfo['shifts_status'][$shift->id] ?? -1; ?>
                                        <td class="border border-gray-200 dark:border-gray-600 px-1 py-2 text-center
                                            <?php echo e($isWeekend ? 'bg-gray-50 dark:bg-gray-700/40' : 'bg-white dark:bg-gray-800'); ?>">
                                            <!--[if BLOCK]><![endif]--><?php if($status === 1): ?>
                                                
                                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-md
                                                    bg-green-700 dark:bg-green-600
                                                    text-white text-xs font-bold
                                                    ring-1 ring-green-800/30 dark:ring-green-500/30
                                                    shadow-sm">
                                                    ✓
                                                </span>
                                            <?php elseif($status === 0): ?>
                                                
                                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-md
                                                    bg-red-700 dark:bg-red-600
                                                    text-white text-xs font-bold
                                                    ring-1 ring-red-800/30 dark:ring-red-500/30
                                                    shadow-sm">
                                                    ✗
                                                </span>
                                            <?php else: ?>
                                                
                                                <span class="text-base font-medium text-gray-300 dark:text-gray-600 select-none">—</span>
                                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                        </td>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                <?php endfor; ?><!--[if ENDBLOCK]><![endif]-->
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="<?php echo e(2 + ($data['days_in_month'] * count($data['shifts']))); ?>"
                                    class="border border-gray-200 dark:border-gray-600 px-4 py-10 text-center text-sm text-gray-400 dark:text-gray-500">
                                    Tidak ada data untuk periode ini
                                </td>
                            </tr>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    </tbody>
                </table>
            </div>

            
            <div class="flex flex-wrap gap-6 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/60 p-4">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-md bg-green-700 dark:bg-green-600 text-white text-xs font-bold ring-1 ring-green-800/30 shadow-sm">✓</span>
                    <span class="text-sm text-gray-600 dark:text-gray-300">Patrol dilakukan</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-md bg-red-700 dark:bg-red-600 text-white text-xs font-bold ring-1 ring-red-800/30 shadow-sm">✗</span>
                    <span class="text-sm text-gray-600 dark:text-gray-300">Shift tidak patrol</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-base font-medium text-gray-300 dark:text-gray-600 select-none">—</span>
                    <span class="text-sm text-gray-600 dark:text-gray-300">Shift tidak ditugaskan</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="h-5 w-10 rounded bg-gray-50 dark:bg-gray-700/40 border border-gray-200 dark:border-gray-600"></div>
                    <span class="text-sm text-gray-600 dark:text-gray-300">Akhir pekan</span>
                </div>
            </div>
        </div>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal166a02a7c5ef5a9331faf66fa665c256)): ?>
<?php $attributes = $__attributesOriginal166a02a7c5ef5a9331faf66fa665c256; ?>
<?php unset($__attributesOriginal166a02a7c5ef5a9331faf66fa665c256); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal166a02a7c5ef5a9331faf66fa665c256)): ?>
<?php $component = $__componentOriginal166a02a7c5ef5a9331faf66fa665c256; ?>
<?php unset($__componentOriginal166a02a7c5ef5a9331faf66fa665c256); ?>
<?php endif; ?><?php /**PATH /var/www/html/resources/views/filament/admin/pages/dashboard.blade.php ENDPATH**/ ?>