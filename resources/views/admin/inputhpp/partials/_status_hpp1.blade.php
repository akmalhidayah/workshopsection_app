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
    <div>
        <span class="text-green-600 font-semibold">Telah Disetujui Semua</span>
        <div class="mt-1 flex items-center gap-1 text-[10px] text-slate-600">
            <i class="fas fa-file-upload text-[9px]"></i>
            Dokumen direktur terunggah
        </div>
    </div>
@endif
