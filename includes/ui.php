<?php
function section_header($title, $subtitle, $actionHtml = '') {
    echo '<div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">';
    echo '<div><h1 class="text-2xl font-semibold tracking-tight text-slate-900">' . e($title) . '</h1>';
    echo '<p class="mt-1 text-sm text-slate-500">' . e($subtitle) . '</p></div>';
    echo $actionHtml;
    echo '</div>';
}

function search_bar($placeholder = 'Search records', $extra = '') {
    $q = getq('q');
    echo '<form method="get" class="mb-5 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">';
    echo '<div class="relative w-full md:max-w-sm"><span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">⌕</span>';
    echo '<input name="q" value="' . e($q) . '" class="input pl-9" placeholder="' . e($placeholder) . '"></div>';
    echo $extra;
    echo '</form>';
}

function input_field($label, $name, $value = '', $type = 'text', $attrs = '') {
    echo '<label><span class="form-label">' . e($label) . '</span><input type="' . e($type) . '" name="' . e($name) . '" value="' . e($value) . '" class="input" ' . $attrs . '></label>';
}

function select_field($label, $name, $value, $options) {
    echo '<label><span class="form-label">' . e($label) . '</span><select name="' . e($name) . '" class="input">';
    foreach ($options as $option) {
        echo '<option value="' . e($option) . '" ' . selected($value, $option) . '>' . e($option) . '</option>';
    }
    echo '</select></label>';
}
