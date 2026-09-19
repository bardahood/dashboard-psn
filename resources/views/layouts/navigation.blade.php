<nav x-data="{ open: false }" class="sticky top-0 z-30 bg-white/95 backdrop-blur border-b border-gray-200 border-t-4 border-t-gold-500 shadow-sm">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center pe-6 sm:pe-8">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden gap-0.5 sm:-my-px sm:flex sm:items-center">
                    <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                    @can('psn.view')
                        <x-nav-link :href="route('admin.psn.index')" :active="request()->routeIs('admin.psn.*')">
                            {{ __('Data PSN') }}
                        </x-nav-link>
                        <x-nav-link :href="route('admin.matriks-sandingan')" :active="request()->routeIs('admin.matriks-sandingan')">
                            {{ __('Matriks Sandingan') }}
                        </x-nav-link>
                    @endcan
                    @can('pengendalian.manage')
                        <x-nav-link :href="route('admin.kunjungan-pengendalian.index')" :active="request()->routeIs('admin.kunjungan-pengendalian.*')">
                            {{ __('Kunjungan Pengendalian') }}
                        </x-nav-link>
                    @endcan
                    @can('perencanaan.manage')
                        <x-nav-link :href="route('admin.kunjungan-perencanaan.index')" :active="request()->routeIs('admin.kunjungan-perencanaan.*')">
                            {{ __('Kunjungan Perencanaan') }}
                        </x-nav-link>
                    @endcan
                    @canany(['pengendalian.manage', 'pengguna.manage', 'audit.view', 'sinkronisasi.manage', 'laporan.export'])
                        @php
                            $lainnyaAktif = request()->routeIs('admin.dokumen', 'admin.pengguna.*', 'admin.audit-log', 'admin.sinkronisasi-psi', 'admin.laporan.*');
                        @endphp
                        <x-dropdown align="left" width="56">
                            <x-slot name="trigger">
                                <button type="button" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-sm font-medium transition-colors duration-150 whitespace-nowrap {{ $lainnyaAktif ? 'text-blue-800 bg-blue-50 font-semibold' : 'text-gray-600 hover:text-blue-800 hover:bg-gray-50' }}">
                                    {{ __('Lainnya') }}
                                    <svg class="h-4 w-4 fill-current" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                                </button>
                            </x-slot>
                            <x-slot name="content">
                                @can('pengendalian.manage')
                                    <x-dropdown-link :href="route('admin.dokumen')">{{ __('Dokumen') }}</x-dropdown-link>
                                @endcan
                                @can('pengguna.manage')
                                    <x-dropdown-link :href="route('admin.pengguna.index')">{{ __('Pengguna') }}</x-dropdown-link>
                                @endcan
                                @can('audit.view')
                                    <x-dropdown-link :href="route('admin.audit-log')">{{ __('Audit Log') }}</x-dropdown-link>
                                @endcan
                                @can('sinkronisasi.manage')
                                    <x-dropdown-link :href="route('admin.sinkronisasi-psi')">{{ __('Sinkronisasi PSI') }}</x-dropdown-link>
                                @endcan
                                @can('laporan.export')
                                    <x-dropdown-link :href="route('admin.laporan.index')">{{ __('Reporting') }}</x-dropdown-link>
                                @endcan
                            </x-slot>
                        </x-dropdown>
                    @endcanany
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-4">
                <x-dropdown align="right" width="56">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center gap-2 pe-2 ps-1.5 py-1.5 border border-transparent text-sm rounded-full hover:bg-gray-50 focus:outline-none transition-colors duration-150">
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-800 text-xs font-semibold text-white">
                                {{ collect(explode(' ', Auth::user()->name))->map(fn ($p) => mb_substr($p, 0, 1))->take(2)->implode('') }}
                            </span>
                            <span class="font-medium text-gray-700 max-w-[10rem] truncate">{{ Auth::user()->name }}</span>
                            <svg class="fill-current h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="px-4 py-3 border-b border-gray-100">
                            <div class="text-sm font-semibold text-gray-800 truncate">{{ Auth::user()->name }}</div>
                            <div class="text-xs text-gray-500 truncate">{{ Auth::user()->email }}</div>
                        </div>

                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <x-dropdown-link :href="route('beranda')">
                            {{ __('Lihat Situs Publik') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            @can('psn.view')
                <x-responsive-nav-link :href="route('admin.psn.index')" :active="request()->routeIs('admin.psn.*')">
                    {{ __('Data PSN') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.matriks-sandingan')" :active="request()->routeIs('admin.matriks-sandingan')">
                    {{ __('Matriks Sandingan') }}
                </x-responsive-nav-link>
            @endcan
            @can('pengendalian.manage')
                <x-responsive-nav-link :href="route('admin.kunjungan-pengendalian.index')" :active="request()->routeIs('admin.kunjungan-pengendalian.*')">
                    {{ __('Kunjungan Pengendalian') }}
                </x-responsive-nav-link>
            @endcan
            @can('perencanaan.manage')
                <x-responsive-nav-link :href="route('admin.kunjungan-perencanaan.index')" :active="request()->routeIs('admin.kunjungan-perencanaan.*')">
                    {{ __('Kunjungan Perencanaan') }}
                </x-responsive-nav-link>
            @endcan
            @can('pengendalian.manage')
                <x-responsive-nav-link :href="route('admin.dokumen')" :active="request()->routeIs('admin.dokumen')">
                    {{ __('Dokumen') }}
                </x-responsive-nav-link>
            @endcan
            @can('pengguna.manage')
                <x-responsive-nav-link :href="route('admin.pengguna.index')" :active="request()->routeIs('admin.pengguna.*')">
                    {{ __('Pengguna') }}
                </x-responsive-nav-link>
            @endcan
            @can('audit.view')
                <x-responsive-nav-link :href="route('admin.audit-log')" :active="request()->routeIs('admin.audit-log')">
                    {{ __('Audit Log') }}
                </x-responsive-nav-link>
            @endcan
            @can('sinkronisasi.manage')
                <x-responsive-nav-link :href="route('admin.sinkronisasi-psi')" :active="request()->routeIs('admin.sinkronisasi-psi')">
                    {{ __('Sinkronisasi PSI') }}
                </x-responsive-nav-link>
            @endcan
            @can('laporan.export')
                <x-responsive-nav-link :href="route('admin.laporan.index')" :active="request()->routeIs('admin.laporan.*')">
                    {{ __('Reporting') }}
                </x-responsive-nav-link>
            @endcan
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4 flex items-center gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-800 text-sm font-semibold text-white">
                    {{ collect(explode(' ', Auth::user()->name))->map(fn ($p) => mb_substr($p, 0, 1))->take(2)->implode('') }}
                </span>
                <div>
                    <div class="font-semibold text-base text-gray-800">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
                </div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('beranda')">
                    {{ __('Lihat Situs Publik') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
