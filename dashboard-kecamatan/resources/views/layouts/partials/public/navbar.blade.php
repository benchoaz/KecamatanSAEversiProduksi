<div class="navbar bg-white shadow-md px-6 py-3 sticky top-0 z-50 border-b border-gray-200">
    <div class="navbar-start">
        <a href="/" class="flex items-center gap-3">
            @if(appProfile()->logo_path)
                <img src="{{ asset('storage/' . appProfile()->logo_path) }}"
                    alt="Logo {{ appProfile()->region_level }} {{ appProfile()->region_name }}"
                    style="height: 60px; width: auto; object-fit: contain;" class="flex-shrink-0">
            @else
                <div
                    class="w-12 h-12 bg-gradient-to-br from-teal-500 to-teal-600 rounded-lg flex items-center justify-center shadow-sm">
                    <i class="fas fa-landmark text-white text-lg"></i>
                </div>
            @endif
            <div>
                <div class="text-xs font-semibold text-gray-700 uppercase tracking-wide">
                    {{ strtoupper(appProfile()->full_region_name) }}
                </div>
                <div class="text-[10px] text-gray-500">{{ appProfile()->app_name }}</div>
            </div>
        </a>
    </div>
    <div class="navbar-center hidden lg:flex">
        <ul class="menu menu-horizontal px-1 gap-1">
            <li><a href="{{ request()->is('/') ? '#layanan' : '/#layanan' }}"
                    class="text-sm font-medium text-gray-600 hover:text-teal-600 hover:bg-teal-50 rounded-lg">Layanan</a>
            </li>
            <li><a href="{{ route('landing.wilayah') }}"
                    class="text-sm font-medium text-gray-600 hover:text-teal-600 hover:bg-teal-50 rounded-lg">Wilayah</a>
            </li>
            <li><a href="{{ route('economy.index') }}"
                    class="text-sm font-medium {{ request()->is('ekonomi*') ? 'text-teal-600 bg-teal-50' : 'text-gray-600 hover:text-teal-600 hover:bg-teal-50' }} rounded-lg">Pusat
                    Ekonomi</a>
            </li>
            <li><a href="{{ request()->is('/') ? '#berita' : '/#berita' }}"
                    class="text-sm font-medium text-gray-600 hover:text-teal-600 hover:bg-teal-50 rounded-lg">Berita</a>
            </li>
        </ul>
    </div>
    <div class="navbar-end">
        <a href="{{ route('login') }}"
            class="btn btn-sm bg-teal-600 hover:bg-teal-700 text-white border-0 rounded-lg px-5 font-medium shadow-sm">Masuk</a>
    </div>
</div>