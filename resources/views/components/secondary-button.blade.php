<button
    {{ $attributes->merge([
        'type' => 'button',
        'class' =>
            'px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg
             dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600'
    ]) }}
>
    {{ $slot }}
</button>
