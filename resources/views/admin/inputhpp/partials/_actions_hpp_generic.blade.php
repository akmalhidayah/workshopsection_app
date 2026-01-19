@php
    use Illuminate\Support\Facades\Route;

    /* =========================================================
       BASIC CONTEXT
    ========================================================= */
    $notif  = (string) ($data->notification_number ?? '');
    $source = $data->source_form ?? '';

    /* =========================================================
       ROUTE MAP PER SOURCE FORM (TIDAK DIUBAH)
    ========================================================= */
    $downloadMap = [
        'createhpp1' => 'admin.inputhpp.download_hpp1',
        'createhpp2' => 'admin.inputhpp.download_hpp2',
        'createhpp3' => 'admin.inputhpp.download_hpp3',
        'createhpp4' => 'admin.inputhpp.download_hpp4',
        'createhpp5' => 'admin.inputhpp.download_hpp5',
        'createhpp6' => 'admin.inputhpp.download_hpp6',
    ];

    $editMap = [
        'createhpp1' => 'admin.inputhpp.edit_hpp1',
        'createhpp2' => 'admin.inputhpp.edit_hpp2',
        'createhpp3' => 'admin.inputhpp.edit_hpp3',
        'createhpp4' => 'admin.inputhpp.edit_hpp4',
        'createhpp5' => 'admin.inputhpp.edit_hpp5',
        'createhpp6' => 'admin.inputhpp.edit_hpp6',
    ];

    $deleteMap = [
        'createhpp1' => 'admin.inputhpp.destroy_hpp1',
        'createhpp2' => 'admin.inputhpp.destroy_hpp2',
        'createhpp3' => 'admin.inputhpp.destroy_hpp3',
        'createhpp4' => 'admin.inputhpp.destroy_hpp4',
        'createhpp5' => 'admin.inputhpp.destroy_hpp5',
        'createhpp6' => 'admin.inputhpp.destroy_hpp6',
    ];

    $downloadRouteName = $downloadMap[$source] ?? null;
    $editRouteName     = $editMap[$source] ?? null;
    $deleteRouteName   = $deleteMap[$source] ?? null;

    /* =========================================================
       DIREKTUR FLOW (INI INTI PERUBAHAN)
    ========================================================= */

    // file direktur sudah ada?
    $hasDirectorFile = !empty($data->director_uploaded_file ?? null);

    // source yang MEMANG boleh upload direktur
    // >>> PERUBAHAN UTAMA: createhpp1 & createhpp3
    $isDirectorFlowSource = in_array($source, ['createhpp1', 'createhpp3', 'createhpp5'], true);

    // GM sudah tanda tangan?
    // (PASTIKAN FIELD INI BENAR SESUAI DB ANDA)
    $isGmSigned = !empty($data->general_manager_signature ?? null);

    // keputusan final: boleh tampilkan upload/download direktur
    $canDirectorAction = $isDirectorFlowSource && $isGmSigned;

    // route khusus direktur (tetap)
    $routeDownloadDirector = 'admin.inputhpp.download_director';
    $routeUploadDirector   = 'admin.inputhpp.director_upload.store';
@endphp

<td class="px-3 py-2 text-center space-x-1">

    {{-- =========================================================
       FILE DIREKTUR SUDAH ADA → DOWNLOAD SAJA
    ========================================================= --}}
    @if ($hasDirectorFile && $canDirectorAction)
        @if (Route::has($routeDownloadDirector))
            <a href="{{ route($routeDownloadDirector, $notif) }}"
               target="_blank"
               rel="noopener noreferrer"
               class="action-btn bg-indigo-500 hover:bg-indigo-600"
               title="Download File Direktur">
                <i class="fas fa-file-pdf"></i>
            </a>
        @else
            <button class="action-btn bg-slate-300 text-slate-600 cursor-not-allowed" disabled>
                <i class="fas fa-file-pdf"></i>
            </button>
        @endif

    @else
        {{-- =====================================================
           DOWNLOAD PDF BIASA (SEMUA SOURCE FORM)
        ===================================================== --}}
        @if ($downloadRouteName && Route::has($downloadRouteName))
            <a href="{{ route($downloadRouteName, $notif) }}"
               target="_blank"
               rel="noopener noreferrer"
               class="action-btn bg-red-500 hover:bg-red-600"
               title="Preview / Download HPP">
                <i class="fas fa-file-pdf"></i>
            </a>
        @else
            <button class="action-btn bg-slate-300 text-slate-600 cursor-not-allowed" disabled>
                <i class="fas fa-file-pdf"></i>
            </button>
        @endif

        {{-- =====================================================
           UPLOAD DIREKTUR
           HANYA: createhpp1 & createhpp3
           HANYA: SETELAH GM SIGN
        ===================================================== --}}
        @if ($canDirectorAction)
            <form action="{{ Route::has($routeUploadDirector) ? route($routeUploadDirector, $notif) : '#' }}"
                  method="POST"
                  enctype="multipart/form-data"
                  class="inline-block ml-1 director-upload-form">
                @csrf
                <input type="file" name="hpp_file" accept="application/pdf" class="director-input hidden">

                <button type="button"
                        class="director-upload-btn inline-flex items-center gap-2 text-[11px] px-3 py-1 rounded-md
                               bg-emerald-600 text-white hover:bg-emerald-700">
                    <i class="fas fa-upload text-[10px]"></i> Upload HPP DIROPS
                </button>
            </form>
        @endif
    @endif

    {{-- =========================================================
       EDIT (TIDAK DIUBAH)
    ========================================================= --}}
    @if ($editRouteName && Route::has($editRouteName))
        <a href="{{ route($editRouteName, $notif) }}"
           class="action-btn bg-yellow-500 hover:bg-yellow-600 ml-1"
           title="Edit">
            <i class="fas fa-edit"></i>
        </a>
    @else
        <button class="action-btn bg-slate-300 text-slate-600 cursor-not-allowed ml-1" disabled>
            <i class="fas fa-edit"></i>
        </button>
    @endif

    {{-- =========================================================
       DELETE (TIDAK DIUBAH)
    ========================================================= --}}
    @if ($deleteRouteName && Route::has($deleteRouteName))
        <form action="{{ route($deleteRouteName, $notif) }}"
              method="POST"
              class="inline-block ml-1 delete-form">
            @csrf @method('DELETE')
            <button type="button"
                    class="action-btn bg-slate-500 hover:bg-slate-600 delete-button"
                    title="Hapus">
                <i class="fas fa-trash-alt"></i>
            </button>
        </form>
    @else
        <button class="action-btn bg-slate-300 text-slate-600 cursor-not-allowed ml-1" disabled>
            <i class="fas fa-trash-alt"></i>
        </button>
    @endif

</td>
