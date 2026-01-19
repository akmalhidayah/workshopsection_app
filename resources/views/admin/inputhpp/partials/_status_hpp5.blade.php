{{-- Partial: status HPP5 (pakai status flow karena stage peminta bisa di-skip) --}}
@if ($data->status === 'rejected')
    <span class="text-red-600 font-semibold">Ditolak</span>
@elseif ($data->status === 'submitted')
    <span class="text-red-500">Menunggu tanda tangan Manager Bengkel Mesin</span>
@elseif ($data->status === 'approved_manager')
    <span class="text-red-500">Menunggu tanda tangan Senior Manager Workshop</span>
@elseif ($data->status === 'approved_sm')
    <span class="text-red-500">Menunggu tanda tangan Manager Peminta</span>
@elseif ($data->status === 'approved_manager_req')
    <span class="text-red-500">Menunggu tanda tangan Senior Manager Peminta</span>
@elseif ($data->status === 'approved_sm_req')
    <span class="text-red-500">Menunggu tanda tangan General Manager Peminta</span>
@elseif ($data->status === 'approved_gm_req')
    <span class="text-red-500">Menunggu tanda tangan General Manager</span>
@elseif ($data->status === 'approved_gm')
    <span class="text-green-600 font-semibold">Telah Ditandatangani Semua</span>
@else
    <span class="text-slate-500">Status tidak dikenali</span>
@endif
