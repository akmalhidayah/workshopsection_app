{{-- Partial: status HPP2 --}}
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

@else
    <span class="text-green-600 font-semibold">Telah Ditandatangani Semua</span>
@endif
