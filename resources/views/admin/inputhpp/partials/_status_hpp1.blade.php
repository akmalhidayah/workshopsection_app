{{-- Partial: status HPP1 + upload direktur --}}
@if (is_null($data->manager_signature))
    <span class="text-red-500">Menunggu tanda tangan Manager Bengkel Mesin</span>

@elseif (is_null($data->senior_manager_signature))
    <span class="text-red-500">Menunggu tanda tangan Senior Manager Workshop</span>

@elseif (is_null($data->manager_signature_requesting_unit))
    <span class="text-red-500">Menunggu tanda tangan Manager Peminta</span>

@elseif (is_null($data->senior_manager_signature_requesting_unit))
    <span class="text-red-500">Menunggu tanda tangan Senior Manager Peminta</span>

@elseif (is_null($data->general_manager_signature_requesting_unit))
    <span class="text-red-500">Menunggu tanda tangan General Manager Peminta</span>

@elseif (is_null($data->general_manager_signature))
    <span class="text-red-500">Menunggu tanda tangan General Manager</span>

{{-- ===== DIREKTUR / UPLOAD FILE ===== --}}
@elseif (empty($data->director_uploaded_file))
    {{-- Direktur belum upload file --}}
    <div class="space-y-1">
        <span class="text-red-500">Menunggu tanda tangan Direktur Operasional</span>

        @if ($data->source_form === 'createhpp1')
            <form action="{{ route('admin.inputhpp.director_upload.store', $data->notification_number) }}"
                  method="POST"
                  enctype="multipart/form-data"
                  class="inline-block mt-1 director-upload-form">
                @csrf
                <input type="file" name="hpp_file" accept="application/pdf" class="director-input hidden" />

                <button type="button"
                        class="director-upload-btn inline-flex items-center gap-2 text-[11px] px-3 py-1 rounded-md
                               bg-emerald-600 text-white hover:bg-emerald-700">
                    <i class="fas fa-upload text-[10px]"></i> Upload HPP Dirops
                </button>
            </form>

            <div class="text-xs text-slate-400 mt-1">
                Upload manual oleh Direksi setelah GM menyetujui. (PDF maks 10MB)
            </div>
        @endif
    </div>

@else
    {{-- Semua sudah selesai + file direktur sudah ada --}}
    <div class="space-y-1">
        <span class="text-green-600 font-semibold">Telah Ditandatangani Semua</span>

        <div class="mt-1">
            <span class="inline-flex items-center gap-2 px-2 py-0.5 rounded text-[10px]
                         bg-slate-100 text-slate-700 ring-1 ring-slate-200">
                <i class="fas fa-file-upload text-[9px]"></i> File Direktur Terunggah
            </span>

            <a href="{{ route('admin.inputhpp.download_director', $data->notification_number) }}"
               class="inline-flex items-center ml-2 text-[10px] px-2 py-0.5 rounded
                      bg-indigo-100 text-indigo-800 ring-1 ring-indigo-200 hover:bg-indigo-200">
                <i class="fas fa-file-pdf text-[9px]"></i> Download
            </a>

            <span class="text-xs text-slate-500 ml-2">
                {{ optional($data->director_uploaded_at)->format('d/m/Y H:i') }}
            </span>
        </div>
    </div>
@endif
