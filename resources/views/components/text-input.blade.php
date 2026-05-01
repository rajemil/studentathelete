@props(['disabled' => false])

<input
    @disabled($disabled)
    {{ $attributes->merge([
        'class' =>
            'rounded-md shadow-sm transition ' .
            'border-gray-300 bg-white text-gray-900 placeholder:text-gray-400 ' .
            'focus:border-indigo-500 focus:ring-indigo-500 ' .
            'dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:placeholder:text-white/35 ' .
            'dark:focus:border-indigo-600 dark:focus:ring-indigo-600',
    ]) }}
>
