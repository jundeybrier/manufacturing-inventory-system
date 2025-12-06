<button
    {{ $attributes->merge([
        'type' => 'button',
        'class' =>
            'px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg shadow
             disabled:opacity-50 disabled:cursor-not-allowed'
    ]) }}
>
    {{ $slot }}
</button>
