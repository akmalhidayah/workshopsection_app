{{-- =========================================================
   PARTIAL: STATUS HPP1 (STATUS ONLY – TANPA AKSI)
========================================================= --}}

@if (is_null($data->manager_signature))
    <span class="text-red-500">
        Menunggu tanda tangan Manager Bengkel Mesin
    </span>

@elseif (is_null($data->senior_manager_signature))
    <span class="text-red-500">
        Menunggu tanda tangan Senior Manager Workshop
    </span>

@elseif (is_null($data->manager_signature_requesting_unit))
    <span class="text-red-500">
        Menunggu tanda tangan Manager Peminta
    </span>

@elseif (is_null($data->senior_manager_signature_requesting_unit))
    <span class="text-red-500">
        Menunggu tanda tangan Senior Manager Peminta
    </span>

@elseif (is_null($data->general_manager_signature_requesting_unit))
    <span class="text-red-500">
        Menunggu tanda tangan General Manager Peminta
    </span>

@elseif (is_null($data->general_manager_signature))
    <span class="text-red-500">
        Menunggu tanda tangan General Manager
    </span>

{{-- ===== DIREKTUR (STATUS SAJA) ===== --}}
@elseif (empty($data->director_uploaded_file))
    <span class="text-red-500">
        Menunggu upload dokumen Direktur Operasional
    </span>

@else
    <div class="space-y-1">
        <span class="text-green-600 font-semibold">
            Telah Disetujui Semua
        </span>

        <div class="mt-1 inline-flex items-center gap-2 px-2 py-0.5 rounded
                    text-[10px] bg-slate-100 text-slate-700 ring-1 ring-slate-200">
            <i class="fas fa-file-upload text-[9px]"></i>
            Dokumen Direktur Terunggah
        </div>
    </div>
@endif
