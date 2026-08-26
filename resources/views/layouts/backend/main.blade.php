<!doctype html>
<html lang="en" class="h-full bg-[#f7f7f7]">
  <head>
    <meta charset="utf-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <meta name="robots" content="noindex, nofollow" />
    <title>{{ settings()['title'] ?? config('app.name') }}</title>
    <meta name="author" content="{{ settings()['author'] ?? '' }}">
    <meta name="description" content="{{ settings()['description'] ?? '' }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png"
      href="{{ settings()['favicon'] ? asset('storage/' . settings()['favicon']) : asset('images/no-image.png') }}">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" />

    {{-- Data Table --}}
    <link rel="stylesheet" href="{{ asset('assets/vendor/datatables/css/datatables.min.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.2/css/responsive.dataTables.min.css">

    {{-- Select2 --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/css/select2.min.css" rel="stylesheet"/>

    {{-- Alpine.js --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
  </head>
  <body class="min-h-screen text-slate-900 antialiased flex font-satoshi" data-admin-panel="true">
    <!-- Sidebar component -->
    <x-layout.admin.sidebar />

    <!-- Main Container -->
    <div class="flex-1 flex flex-col min-w-0 lg:pl-[280px] min-h-screen">
      <!-- Header component -->
      <x-layout.admin.header :sub_title="View::yieldContent('sub_title')" />

      <!-- Main Body -->
      <main class="flex-1 p-6 md:p-8 flex flex-col">
        <!-- Breadcrumb section -->
        <div class="mb-6">
          @yield('breadcrumb')
        </div>

        <!-- Content slot -->
        <div class="flex-1 ">
          @yield('content')
        </div>
      </main>

      <!-- Footer component -->
      <x-layout.admin.footer />
    </div>

    <script>
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
    </script>

    {{-- jQuery --}}
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    {{-- Data table --}}
    <script src="{{ asset('assets/vendor/datatables/js/datatables.min.js') }}"></script>
    <script src="https://cdn.datatables.net/responsive/3.0.2/js/dataTables.responsive.min.js"></script>

    {{-- Select2 --}}
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.min.js"></script>

    {{-- SweetAlert2 (global: dipakai konfirmasi & notifikasi semua modul) --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- Custom Components --}}
    <x-ui.notification />
    <x-ui.modal-confirm />

    @stack('scripts')
  </body>
</html>
