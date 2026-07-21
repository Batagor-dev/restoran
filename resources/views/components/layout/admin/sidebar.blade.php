@php
    use App\Helpers\MenuHelper;
@endphp

<aside id="admin-sidebar" class="group fixed inset-y-0 left-0 z-60 flex w-72 flex-col bg-white transition-all duration-300 ease-in-out lg:translate-x-0 -translate-x-full">

  <!-- Brand Header -->
  <div class="relative flex h-20 items-center px-5 border-b border-slate-100 group-[.sidebar-collapsed]:justify-center group-[.sidebar-collapsed]:px-0 transition-all duration-300">
    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 overflow-hidden">
      <img src="{{ asset('assets/img/logo/logo.png') }}" alt="Logo" class="h-10 w-auto object-contain flex-shrink-0" onerror="this.style.display='none'">
      <span class="text-2xl font-satoshi-bold tracking-tight text-slate-900 transition-all duration-300 group-[.sidebar-collapsed]:w-0 group-[.sidebar-collapsed]:opacity-0 truncate">Techarea</span>
    </a>

    <button
      type="button"
      id="sidebar-toggle-btn"
      class="hidden lg:flex h-8 w-8 items-center justify-center text-slate-500 hover:text-slate-900 hover:bg-slate-50 rounded-lg transition-all duration-300 ease-in-out cursor-pointer absolute top-1/2 -translate-y-1/2 right-5 opacity-100"
      onclick="toggleSidebarMode()"
    >
      <i id="sidebar-toggle-icon" class="ri-arrow-left-double-line text-2xl"></i>
    </button>
  </div>

  <!-- Navigation Menu List -->
  <div class="flex-1 overflow-y-auto px-4 py-6 space-y-6 overflow-x-hidden group-[.sidebar-collapsed]:px-2 transition-all duration-300">
    <div>
      <ul class="mt-2 space-y-1">
        <li>
          <a href="{{ route('dashboard') }}" class="group/item flex items-center gap-3 px-3 py-2.5 rounded-xl text-base font-satoshi-medium transition-all duration-200 {{ request()->routeIs('dashboard') ? 'bg-slate-900 text-white shadow-md shadow-slate-900/15' : 'text-slate-600 hover:bg-slate-50/70 hover:text-slate-900' }} group-[.sidebar-collapsed]:justify-center group-[.sidebar-collapsed]:px-0">
            <i class="ri-dashboard-line text-xl w-5 text-center flex-shrink-0 transition-colors duration-200 {{ request()->routeIs('dashboard') ? 'text-white' : 'text-slate-400 group-hover/item:text-slate-600' }}"></i>
            <span class="transition-all duration-300 group-[.sidebar-collapsed]:w-0 group-[.sidebar-collapsed]:opacity-0 overflow-hidden whitespace-nowrap">Dashboard</span>
          </a>
        </li>
        @if(isset($groupedMenus) && count($groupedMenus) > 0)
          @foreach($groupedMenus as $groupId => $groupItems)
            @php
              $groupName = $groupItems->first()?->menuGroup?->name;
            @endphp
            @if($groupName)
              <li class="pt-4 pb-1 px-3 text-xs font-satoshi-bold uppercase tracking-wider text-slate-400 group-[.sidebar-collapsed]:hidden truncate select-none">
                {{ $groupName }}
              </li>
              <li class="hidden group-[.sidebar-collapsed]:block my-2 border-t border-slate-100"></li>
            @endif
            @foreach($groupItems->sortBy('sort') as $menu)
              @php
                  $permissionName = optional($menu->permissionGroup)->name . ' Access';
              @endphp

              @can($permissionName)
                  @include('components.layout.admin.children', ['menu' => $menu])
              @endcan
            @endforeach
          @endforeach
        @else
          @foreach($menus as $menu)
            @php
                $permissionName = optional($menu->permissionGroup)->name . ' Access';
            @endphp

            @can($permissionName)
                @include('components.layout.admin.children', ['menu' => $menu])
            @endcan
          @endforeach
        @endif
      </ul>
    </div>
  </div>
</aside>

<!-- Mobile Overlay -->
<div id="sidebar-overlay" class="fixed inset-0 z-30 bg-slate-900/40 backdrop-blur-xs transition-opacity duration-300 lg:hidden hidden" onclick="toggleSidebar()"></div>

@push('scripts')
<script>
  // ============ MOBILE DRAWER (tidak diubah) ============
  function toggleSidebar() {
    const sidebar = document.getElementById('admin-sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    if (sidebar && overlay) {
      const isHidden = sidebar.classList.contains('-translate-x-full');
      if (isHidden) {
        sidebar.classList.remove('-translate-x-full');
        overlay.classList.remove('hidden');
      } else {
        sidebar.classList.add('-translate-x-full');
        overlay.classList.add('hidden');
      }
    }
  }

  // ============ DESKTOP COLLAPSE / HOVER-PEEK STATE MACHINE ============
  (function () {
    const sidebar   = document.getElementById('admin-sidebar');
    const mainEl    = document.querySelector('.flex-1.flex.flex-col');
    const toggleBtn = document.getElementById('sidebar-toggle-btn');
    const toggleIcon = document.getElementById('sidebar-toggle-icon');

    // Source of truth ada di JS, bukan di classList, biar gak ambigu
    let isPermanentCollapsed = false; // state permanen (klik tombol)
    let isHoverPeek = false;          // state sementara (hover saat collapsed)

    function applyState() {
      const showAsExpanded = !isPermanentCollapsed || isHoverPeek;

      // Lebar sidebar
      sidebar.classList.remove('w-72', 'w-20');
      sidebar.classList.add(showAsExpanded ? 'w-72' : 'w-20');

      // Class 'sidebar-collapsed' hanya dipakai buat styling teks/icon menu (group-[.sidebar-collapsed]:...)
      // Sengaja dilepas saat hover-peek supaya isi menu ikut full text (bukan cuma icon)
      sidebar.classList.toggle('sidebar-collapsed', isPermanentCollapsed && !isHoverPeek);

      // Efek "melayang" di atas konten saat hover-peek (konten TIDAK ikut geser)
      sidebar.classList.toggle('shadow-2xl', isHoverPeek);
      sidebar.classList.toggle('ring-1', isHoverPeek);
      sidebar.classList.toggle('ring-slate-200', isHoverPeek);

      // Posisi tombol: kanan saat benar-benar expanded permanen, kiri saat collapsed (termasuk saat hover-peek)
      toggleBtn.classList.toggle('right-5', showAsExpanded);
      toggleBtn.classList.toggle('left-5', !showAsExpanded);

      // Icon ikut hilang/muncul kayak teks: hilang total saat w-20 (collapsed, tidak hover),
      // muncul lagi saat hover-peek ATAU saat expanded permanen
      toggleBtn.classList.toggle('opacity-0', isPermanentCollapsed && !isHoverPeek);
      toggleBtn.classList.toggle('opacity-100', showAsExpanded);
      toggleBtn.classList.toggle('pointer-events-none', isPermanentCollapsed && !isHoverPeek);

      // Icon ikut berubah arah sesuai aksi yang akan terjadi kalau diklik
      toggleIcon.className = isPermanentCollapsed
        ? 'ri-arrow-right-double-line text-2xl'
        : 'ri-arrow-left-double-line text-2xl';

      // Padding konten utama HANYA mengikuti state permanen, bukan hover-peek
      if (mainEl) {
        mainEl.classList.toggle('lg:pl-[280px]', !isPermanentCollapsed);
        mainEl.classList.toggle('lg:pl-24', isPermanentCollapsed);
      }
    }

    // Klik tombol = ubah state permanen (dan otomatis keluar dari mode peek)
    window.toggleSidebarMode = function () {
      isPermanentCollapsed = !isPermanentCollapsed;
      isHoverPeek = false;
      applyState();
    };

    // Hover masuk: hanya berlaku kalau sedang collapsed permanen
    sidebar.addEventListener('mouseenter', function () {
      if (isPermanentCollapsed) {
        isHoverPeek = true;
        applyState();
      }
    });

    // Hover keluar: balik ke collapsed, TAPI hanya kalau belum di-pin jadi permanen
    sidebar.addEventListener('mouseleave', function () {
      if (isPermanentCollapsed && isHoverPeek) {
        isHoverPeek = false;
        applyState();
      }
    });

    applyState();
  })();
</script>
@endpush