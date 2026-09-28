<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'name',
    'id',
    'value' => '',
    'required' => false,
    'placeholder' => null,
    'errorName' => null,
    'showHint' => false,
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'name',
    'id',
    'value' => '',
    'required' => false,
    'placeholder' => null,
    'errorName' => null,
    'showHint' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $errorName = $errorName ?? $name;
    $googleKey = config('services.google.maps_api_key') ?? '';
?>

<div class="relative z-50">
    <div class="relative" data-address-autocomplete-wrapper>
        <input
            type="text"
            id="<?php echo e($id); ?>"
            name="<?php echo e($name); ?>"
            <?php if (! ($attributes->has('x-model'))): ?>
                value="<?php echo e($value); ?>"
            <?php endif; ?>
            data-address-autocomplete
            data-google-key="<?php echo e($googleKey); ?>"
            <?php if($required): ?> required <?php endif; ?>
            <?php if($placeholder): ?> placeholder="<?php echo e($placeholder); ?>" <?php endif; ?>
            maxlength="2000"
            autocomplete="off"
            <?php echo e($attributes->class('w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-ink-heading placeholder-gray-400 dark:placeholder-gray-500 rounded-lg focus:ring-1 focus:ring-[#5542ff] focus:border-[#5542ff] text-sm')); ?>

        />
    </div>
    <?php if($showHint): ?>
    <p class="mt-1 text-[11px] text-ink-muted">
        Start typing for suggestions. Results favor the Philippines; other countries are included.
        <?php if($googleKey === ''): ?>
            <span class="text-amber-700 dark:text-amber-400">Using OpenStreetMap search. Set <code class="text-xs">GOOGLE_MAPS_API_KEY</code> for Google Places (richer street-level data where available).</span>
        <?php endif; ?>
    </p>
    <?php endif; ?>
    <?php $__errorArgs = [$errorName];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
        <p class="mt-1 text-sm text-red-600 dark:text-red-400"><?php echo e($message); ?></p>
    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
</div>
<?php /**PATH C:\laragon\www\GE-System-1.1\resources\views/components/address-input.blade.php ENDPATH**/ ?>