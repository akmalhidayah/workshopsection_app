<x-admin-layout>
    <div class="admin-card p-5 mb-4">
        <div class="admin-header">
            <div class="flex items-center gap-3">
                <span class="inline-flex w-10 h-10 items-center justify-center rounded-xl bg-blue-600 text-white">
                    <i data-lucide="shield-check" class="w-5 h-5"></i>
                </span>
                <div>
                    <h1 class="admin-title">Access Control</h1>
                    <p class="admin-subtitle">Atur permission menu untuk role admin.</p>
                </div>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.access-control.index') }}" class="admin-filter mt-4">
            <div class="relative">
                <select name="role" class="admin-select w-56" onchange="this.form.submit()">
                    @foreach($roles as $role)
                        <option value="{{ $role }}" @selected($selectedRole === $role)>
                            {{ $role }}
                        </option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>

    @if (session('success'))
        <div class="mb-3 text-sm text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-2">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="mb-3 text-sm text-red-700 bg-red-50 border border-red-200 rounded-xl px-4 py-2">
            {{ session('error') }}
        </div>
    @endif

    <div class="admin-card p-5">
        <form method="POST" action="{{ route('admin.access-control.update') }}">
            @csrf
            <input type="hidden" name="role" value="{{ $selectedRole }}">

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach($permissions as $perm)
                    @php
                        $key = $perm->key;
                        $label = $perm->label;
                        $checked = in_array($key, $assigned, true);
                    @endphp
                    <label class="flex items-center gap-3 border border-slate-200 rounded-xl px-3 py-2 hover:bg-slate-50">
                        <input type="checkbox" name="permissions[]" value="{{ $key }}" @checked($checked)>
                        <div class="text-sm text-slate-800">
                            <div class="font-semibold">{{ $label }}</div>
                            <div class="text-[11px] text-slate-500">{{ $key }}</div>
                        </div>
                    </label>
                @endforeach
            </div>

            <div class="mt-4 flex items-center gap-2">
                <button class="admin-btn admin-btn-primary" type="submit">
                    <i data-lucide="save" class="w-4 h-4"></i> Simpan
                </button>
                <a href="{{ route('admin.access-control.index', ['role' => $selectedRole]) }}" class="admin-btn admin-btn-ghost">
                    <i data-lucide="rotate-ccw" class="w-4 h-4"></i> Reset
                </a>
            </div>
        </form>
    </div>
</x-admin-layout>
