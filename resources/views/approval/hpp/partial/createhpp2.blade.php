<!-- Logika untuk source_form 2 -->
@if (is_null($hpp->manager_signature) && auth()->user()->jabatan === 'Manager' && 
    (auth()->user()->unit_work === $hpp->controlling_unit || 
    auth()->user()->unit_work === $hpp->requesting_unit || 
    in_array($hpp->controlling_unit, auth()->user()->related_units ?? []) || 
    in_array($hpp->requesting_unit, auth()->user()->related_units ?? [])))
    <button class="bg-blue-500 text-white px-2 py-1 rounded text-xs" 
            onclick="openSignPad('{{ $hpp->notification_number }}', 'manager')">
        Tanda Tangan Manager
    </button>
    <button type="button" class="bg-red-500 text-white px-2 py-1 rounded text-xs" 
            onclick="rejectDocument('{{ $hpp->notification_number }}', 'manager')">
        Reject
    </button>
@elseif (is_null($hpp->senior_manager_signature) && !is_null($hpp->manager_signature) && auth()->user()->jabatan === 'Senior Manager' && 
    (auth()->user()->unit_work === $hpp->controlling_unit || 
    auth()->user()->unit_work === $hpp->requesting_unit || 
    in_array($hpp->controlling_unit, auth()->user()->related_units ?? []) || 
    in_array($hpp->requesting_unit, auth()->user()->related_units ?? [])))
    <button class="bg-green-500 text-white px-2 py-1 rounded text-xs" 
            onclick="openSignPad('{{ $hpp->notification_number }}', 'senior_manager')">
        Tanda Tangan Senior Manager
    </button>
    <button type="button" class="bg-red-500 text-white px-2 py-1 rounded text-xs" 
            onclick="rejectDocument('{{ $hpp->notification_number }}', 'senior_manager')">
        Reject
    </button>
    @elseif (is_null($hpp->manager_signature_requesting_unit) && !is_null($hpp->senior_manager_signature) && auth()->user()->jabatan === 'Manager' && 
        (auth()->user()->unit_work === $hpp->requesting_unit || 
        in_array($hpp->requesting_unit, auth()->user()->related_units ?? [])))
        <button class="bg-blue-500 text-white px-2 py-1 rounded text-xs" 
                onclick="openSignPad('{{ $hpp->notification_number }}', 'manager_requesting')">
            Tanda Tangan Manager Requesting
        </button>
        <button type="button" class="bg-red-500 text-white px-2 py-1 rounded text-xs" 
                onclick="rejectDocument('{{ $hpp->notification_number }}', 'manager_requesting')">
            Reject
        </button>
    @elseif (is_null($hpp->senior_manager_signature_requesting_unit) && !is_null($hpp->manager_signature_requesting_unit) && auth()->user()->jabatan === 'Senior Manager' && 
        (auth()->user()->unit_work === $hpp->requesting_unit || 
        in_array($hpp->requesting_unit, auth()->user()->related_units ?? [])))
        <button class="bg-green-500 text-white px-2 py-1 rounded text-xs" 
                onclick="openSignPad('{{ $hpp->notification_number }}', 'senior_manager_requesting')">
            Tanda Tangan Senior Manager Requesting
        </button>
        <button type="button" class="bg-red-500 text-white px-2 py-1 rounded text-xs" 
                onclick="rejectDocument('{{ $hpp->notification_number }}', 'senior_manager_requesting')">
            Reject
        </button>
    @elseif (is_null($hpp->general_manager_signature) && !is_null($hpp->senior_manager_signature_requesting_unit) && auth()->user()->jabatan === 'General Manager' && 
        (auth()->user()->unit_work === $hpp->controlling_unit || 
        auth()->user()->unit_work === $hpp->requesting_unit || 
        in_array($hpp->controlling_unit, auth()->user()->related_units ?? []) || 
        in_array($hpp->requesting_unit, auth()->user()->related_units ?? [])))
        <button class="bg-orange-500 text-white px-2 py-1 rounded text-xs" 
                onclick="openSignPad('{{ $hpp->notification_number }}', 'general_manager')">
            Tanda Tangan General Manager
        </button>
        <button type="button" class="bg-red-500 text-white px-2 py-1 rounded text-xs" 
                onclick="rejectDocument('{{ $hpp->notification_number }}', 'general_manager')">
            Reject
        </button>
    @elseif (is_null($hpp->general_manager_signature_requesting_unit) && !is_null($hpp->general_manager_signature) && auth()->user()->jabatan === 'General Manager' && 
        (auth()->user()->unit_work === $hpp->requesting_unit || 
        in_array($hpp->requesting_unit, auth()->user()->related_units ?? [])))
        <button class="bg-orange-500 text-white px-2 py-1 rounded text-xs" 
                onclick="openSignPad('{{ $hpp->notification_number }}', 'general_manager_requesting')">
            Tanda Tangan General Manager Requesting
        </button>
        <button type="button" class="bg-red-500 text-white px-2 py-1 rounded text-xs" 
                onclick="rejectDocument('{{ $hpp->notification_number }}', 'general_manager_requesting')">
            Reject
        </button>
@else
    <!-- Jika sudah ditandatangani, tampilkan status -->
    <span class="text-green-500 text-xs">Sudah Ditandatangani</span>
@endif
