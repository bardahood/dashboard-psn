<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2.5 bg-blue-800 border border-transparent rounded-lg font-semibold text-sm text-white shadow-sm hover:bg-blue-700 hover:shadow focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2 transition-all duration-150']) }}>
    {{ $slot }}
</button>
