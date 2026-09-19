<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2.5 bg-red-600 border border-transparent rounded-lg font-semibold text-sm text-white shadow-sm hover:bg-red-500 hover:shadow active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-all duration-150']) }}>
    {{ $slot }}
</button>
