@php
    $sub_title = ($breadcrumb = Breadcrumbs::current()) ? $breadcrumb->title : 'Dashboard';
    $breadcrumbsData = Breadcrumbs::generate(Request::route()->getName(), $role);
    $breadcrumb_parent = $breadcrumbsData->where('title','!=',$breadcrumb->title)->last();

    // Bangun data permission untuk Alpine.js
    $permissionData = [];

    // Permission tanpa grup
    foreach ($permissions as $p) {
        $permissionData[] = [
            'id'       => (string)$p->id,
            'name'     => $p->name,
            'group'    => null,
            'checked'  => $role->permissions->contains($p->id),
        ];
    }

    // Permission dengan grup
    $groupData = [];
    foreach ($permission_groups as $grp) {
        $items = [];
        foreach ($grp->permissions as $perm) {
            $items[] = [
                'id'      => (string)$perm->id,
                'name'    => $perm->name,
                'checked' => $role->permissions->contains($perm->id),
            ];
        }
        $groupData[] = [
            'name'  => $grp->name,
            'items' => $items,
            'menu_group_names' => $grp->menuGroups->pluck('name')->implode(', '),
        ];
    }
@endphp

@extends('layouts.backend.main')

@section('title', 'User Roles')
@section('sub_title', $sub_title)

@section('breadcrumb')
    <x-layout.admin.breadcrumb :breadcrumbs="$breadcrumbsData" />
@endsection

@section('content')
    <div class="space-y-6" x-data="permissionTree()">
        <x-ui.card>
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h5 class="text-lg font-satoshi-bold text-slate-900">Permissions : {{ $role->name }}</h5>
                    <p class="text-sm text-slate-400 mt-1 font-satoshi">Manage permissions for this role</p>
                </div>
                {{-- Toggle Select All --}}
                <button type="button" @click="toggleAll()"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-satoshi-medium transition-all duration-200"
                    :class="isAllSelected
                        ? 'bg-slate-900 text-white hover:bg-slate-800'
                        : 'bg-slate-100 text-slate-600 hover:bg-slate-200'">
                    <i class="ri-checkbox-multiple-line text-base"></i>
                    <span x-text="isAllSelected ? 'Deselect All' : 'Select All'"></span>
                </button>
            </div>

            <form id="formPermission" method="POST" action="{{ $action }}" @submit.prevent="submitForm()">
                @csrf
                <input type="hidden" name="permission" id="checkedPermissions">

                {{-- Info bar --}}
                <div class="flex items-center gap-3 px-4 py-3 mb-6 rounded-xl bg-slate-50 border border-slate-100">
                    <i class="ri-information-line text-lg text-slate-400"></i>
                    <span class="text-sm text-slate-500 font-satoshi">
                        <span class="font-satoshi-bold text-slate-700" x-text="checkedCount"></span> of
                        <span class="font-satoshi-bold text-slate-700" x-text="totalCount"></span> permissions selected
                    </span>
                </div>

                {{-- Permission tanpa grup --}}
                @if(count($permissionData) > 0)
                <div class="mb-6">
                    <div class="flex items-center gap-2 mb-3">
                        <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-slate-100">
                            <i class="ri-key-2-line text-slate-500"></i>
                        </div>
                        <h6 class="text-sm font-satoshi-bold text-slate-700 uppercase tracking-wide">General Permissions</h6>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                        @foreach($permissionData as $perm)
                        <label class="group flex items-center gap-3 px-4 py-3 rounded-xl border border-slate-100 cursor-pointer transition-all duration-200 hover:border-slate-300 hover:bg-slate-50"
                               :class="checked.includes('{{ $perm['id'] }}') && 'border-slate-900 bg-slate-50 ring-1 ring-slate-900/5'">
                            <div class="relative flex items-center">
                                <input type="checkbox" value="{{ $perm['id'] }}"
                                    x-model="checked"
                                    class="peer h-5 w-5 cursor-pointer appearance-none rounded-md border border-slate-300 bg-white transition-all duration-200 checked:border-slate-900 checked:bg-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-400 focus:ring-offset-2" />
                                <span class="pointer-events-none absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 scale-50 text-white opacity-0 transition-all duration-200 peer-checked:scale-100 peer-checked:opacity-100">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" class="h-3.5 w-3.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                </span>
                            </div>
                            <span class="text-sm font-satoshi-medium text-slate-600 group-hover:text-slate-900 transition-colors"
                                  :class="checked.includes('{{ $perm['id'] }}') && '!text-slate-900'">{{ $perm['name'] }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Permission per grup --}}
                @foreach($groupData as $idx => $group)
                <div class="mb-6" x-data="{ open: true }">
                    {{-- Header grup --}}
                    <div class="flex items-center justify-between mb-3">
                        <button type="button" @click="open = !open" class="flex items-center gap-2 group">
                            <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-slate-100 group-hover:bg-slate-200 transition-colors">
                                <i class="ri-folder-3-line text-slate-500"></i>
                            </div>
                            <h6 class="text-sm font-satoshi-bold text-slate-700 uppercase tracking-wide group-hover:text-slate-900 transition-colors">
                                {{ $group['name'] }}
                                @if(!empty($group['menu_group_names']))
                                    <span class="text-xs font-satoshi-medium text-slate-400 normal-case ml-2">(Menu Group: {{ $group['menu_group_names'] }})</span>
                                @endif
                            </h6>
                            <i class="text-slate-400 transition-transform duration-200"
                               :class="open ? 'ri-arrow-down-s-line' : 'ri-arrow-right-s-line'"></i>
                        </button>
                        {{-- Toggle grup --}}
                        <button type="button" @click="toggleGroup({{ json_encode(collect($group['items'])->pluck('id')) }})"
                            class="text-xs font-satoshi-medium px-3 py-1 rounded-lg transition-all duration-200"
                            :class="isGroupSelected({{ json_encode(collect($group['items'])->pluck('id')) }})
                                ? 'bg-slate-900 text-white hover:bg-slate-800'
                                : 'bg-slate-100 text-slate-500 hover:bg-slate-200 hover:text-slate-700'">
                            <span x-text="isGroupSelected({{ json_encode(collect($group['items'])->pluck('id')) }}) ? 'Deselect Group' : 'Select Group'"></span>
                        </button>
                    </div>

                    {{-- Items grid --}}
                    <div x-show="open" x-collapse class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                        @foreach($group['items'] as $perm)
                        <label class="group flex items-center gap-3 px-4 py-3 rounded-xl border border-slate-100 cursor-pointer transition-all duration-200 hover:border-slate-300 hover:bg-slate-50"
                               :class="checked.includes('{{ $perm['id'] }}') && 'border-slate-900 bg-slate-50 ring-1 ring-slate-900/5'">
                            <div class="relative flex items-center">
                                <input type="checkbox" value="{{ $perm['id'] }}"
                                    x-model="checked"
                                    class="peer h-5 w-5 cursor-pointer appearance-none rounded-md border border-slate-300 bg-white transition-all duration-200 checked:border-slate-900 checked:bg-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-400 focus:ring-offset-2" />
                                <span class="pointer-events-none absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 scale-50 text-white opacity-0 transition-all duration-200 peer-checked:scale-100 peer-checked:opacity-100">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" class="h-3.5 w-3.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                </span>
                            </div>
                            <span class="text-sm font-satoshi-medium text-slate-600 group-hover:text-slate-900 transition-colors"
                                  :class="checked.includes('{{ $perm['id'] }}') && '!text-slate-900'">{{ $perm['name'] }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endforeach

                <!-- Submit / Cancel -->
                <div class="pt-6 border-t border-slate-100 flex items-center justify-end gap-3">
                    <x-ui.button type="button" font="medium" size="sm" style="secondary" onclick="window.location.href='{{ url('role') }}'">
                        Cancel
                    </x-ui.button>
                    <x-ui.button type="submit" font="bold" size="sm">
                        Save
                    </x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('permissionTree', () => {
        // Kumpulkan semua ID permission
        const allIds = {!! json_encode(
            collect($permissionData)->pluck('id')
                ->merge(collect($groupData)->flatMap(fn($g) => collect($g['items'])->pluck('id')))
                ->values()
        ) !!};

        // Kumpulkan ID yang sudah terpilih
        const initialChecked = {!! json_encode(
            collect($permissionData)->where('checked', true)->pluck('id')
                ->merge(collect($groupData)->flatMap(fn($g) => collect($g['items'])->where('checked', true)->pluck('id')))
                ->values()
        ) !!};

        return {
            checked: initialChecked.map(String),
            allIds: allIds.map(String),

            get totalCount() { return this.allIds.length; },
            get checkedCount() { return this.checked.length; },
            get isAllSelected() { return this.allIds.length > 0 && this.allIds.every(id => this.checked.includes(id)); },

            toggleAll() {
                if (this.isAllSelected) {
                    this.checked = [];
                } else {
                    this.checked = [...this.allIds];
                }
            },

            isGroupSelected(ids) {
                return ids.every(id => this.checked.includes(String(id)));
            },

            toggleGroup(ids) {
                const strIds = ids.map(String);
                if (this.isGroupSelected(strIds)) {
                    this.checked = this.checked.filter(id => !strIds.includes(id));
                } else {
                    const toAdd = strIds.filter(id => !this.checked.includes(id));
                    this.checked = [...this.checked, ...toAdd];
                }
            },

            submitForm() {
                document.getElementById('checkedPermissions').value = this.checked.join(',');
                document.getElementById('formPermission').submit();
            }
        };
    });
});
</script>
@endpush