<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow overflow-hidden sm:rounded-lg p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">
                    Dokumen yang Diunggah
                </h3>
                <div class="mt-4">
                    <a href="{{ url('storage/gambarteknik/' . $gambarTeknik->dokumen_path) }}" target="_blank" class="text-blue-500">
                        Lihat Dokumen
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
