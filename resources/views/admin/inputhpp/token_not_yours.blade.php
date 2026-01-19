<x-app-layout>
    <div class="flex items-center justify-center min-h-[60vh]">
        <div class="bg-white border border-slate-200 rounded-xl shadow-md p-6 max-w-md text-center">
            <div class="text-amber-500 text-4xl mb-3">
                <i class="fas fa-lock"></i>
            </div>

            <h2 class="text-lg font-semibold text-slate-800 mb-2">
                Akses Ditolak
            </h2>

            <p class="text-sm text-slate-600 mb-4">
                {{ $message ?? 'Token ini bukan untuk akun Anda.' }}
            </p>

            <div class="text-xs text-slate-400 mb-5">
                Silakan login menggunakan akun approver yang sesuai
                atau minta admin menerbitkan ulang token.
            </div>

            <a href="{{ route('admin.inputhpp.index') }}"
               class="inline-flex items-center px-4 py-2 rounded-md
                      bg-indigo-600 text-white text-sm hover:bg-indigo-700">
                ← Kembali ke Dashboard
            </a>
        </div>
    </div>
</x-app-layout>
