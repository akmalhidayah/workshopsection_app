<x-admin-layout>
    <div class="py-6">
        <div class="max-w-lg mx-auto sm:px-6 lg:px-8">
            <div class="admin-header mb-4">
                <div class="flex items-center gap-3">
                    <span class="inline-flex w-10 h-10 items-center justify-center rounded-xl bg-slate-50 text-slate-600">
                        <i data-lucide="settings" class="w-5 h-5"></i>
                    </span>
                    <div>
                        <h1 class="admin-title">Atur Cost Element</h1>
                        <p class="admin-subtitle">Kelola nilai cost element global.</p>
                    </div>
                </div>
            </div>
            <div class="admin-card p-5">

                @if(session('success'))
                    <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">
                        {{ session('success') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-4 p-3 bg-red-100 text-red-800 rounded">
                        <ul class="list-disc pl-5">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('admin.cost-element.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-4">
                        <label class="block font-medium mb-1">Cost Element Global</label>
                        <input type="text" name="value" 
                               value="{{ old('value', $value) }}" 
                               class="admin-input w-full"
                               required>
                    </div>
                    <div class="flex justify-end gap-2">
                        <a href="{{ route('admin.jenis-kawat-las.index') }}" 
                           class="admin-btn admin-btn-ghost">
                            Kembali
                        </a>
                        <button type="submit" 
                                class="admin-btn admin-btn-primary">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>
