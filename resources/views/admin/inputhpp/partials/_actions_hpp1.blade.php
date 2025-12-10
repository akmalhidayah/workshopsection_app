@php
    use Illuminate\Support\Facades\Route;

    $notif = (string) ($data->notification_number ?? '');
    $source = $data->source_form ?? '';

    // mapping route names per source_form (ubah nama route jika beda)
    $downloadMap = [
        'createhpp1' => 'admin.inputhpp.download_hpp1',
        'createhpp2' => 'admin.inputhpp.download_hpp2',
        'createhpp3' => 'admin.inputhpp.download_hpp3',
        'createhpp4' => 'admin.inputhpp.download_hpp4',
    ];

    $editMap = [
        'createhpp1' => 'admin.inputhpp.edit_hpp1',
        'createhpp2' => 'admin.inputhpp.edit_hpp2',
        'createhpp3' => 'admin.inputhpp.edit_hpp3',
        'createhpp4' => 'admin.inputhpp.edit_hpp4',
    ];

    $deleteMap = [
        'createhpp1' => 'admin.inputhpp.destroy_hpp1',
        'createhpp2' => 'admin.inputhpp.destroy_hpp2',
        'createhpp3' => 'admin.inputhpp.destroy_hpp3',
        'createhpp4' => 'admin.inputhpp.destroy_hpp4',
    ];

    // optional: route khusus download direktur & upload direktur (hanya relevan untuk createhpp1)
    $routeDownloadDirector = 'admin.inputhpp.download_director';
    $routeUploadDirector = 'admin.inputhpp.director_upload.store';

    $downloadRouteName = $downloadMap[$source] ?? null;
    $editRouteName = $editMap[$source] ?? null;
    $deleteRouteName = $deleteMap[$source] ?? null;

    $hasDirectorFile = !empty($data->director_uploaded_file ?? null);
@endphp

<td class="px-3 py-2 text-center space-x-1">

    {{-- Jika ada file direktur: tampilkan label & tombol download file direktur --}}
    @if ($hasDirectorFile)
        @if (Route::has($routeDownloadDirector))
            <a href="{{ route($routeDownloadDirector, $notif) }}"
               target="_blank"
               rel="noopener noreferrer"
               class="action-btn bg-indigo-500 hover:bg-indigo-600"
               title="Download File Direktur">
               <i class="fas fa-file-pdf"></i>
            </a>
        @else
            <button class="action-btn bg-slate-300 text-slate-600 cursor-not-allowed" disabled title="Route download direktur tidak ditemukan">
                <i class="fas fa-file-pdf"></i>
            </button>
        @endif

    @else
        {{-- Tombol Download PDF sesuai source_form (fallback ke '#') --}}
        @if ($downloadRouteName && Route::has($downloadRouteName))
            <a href="{{ route($downloadRouteName, $notif) }}"
               target="_blank"
               rel="noopener noreferrer"
               class="action-btn bg-red-500 hover:bg-red-600"
               title="Preview / Download HPP">
               <i class="fas fa-file-pdf"></i>
            </a>
        @else
            <button class="action-btn bg-slate-300 text-slate-600 cursor-not-allowed" disabled title="Route download HPP tidak ditemukan">
                <i class="fas fa-file-pdf"></i>
            </button>
        @endif

        {{-- Upload Direktur: hanya tampilkan untuk createhpp1 --}}
        @if ($source === 'createhpp1')
            <form action="{{ Route::has($routeUploadDirector) ? route($routeUploadDirector, $notif) : '#' }}"
                  method="POST"
                  enctype="multipart/form-data"
                  class="inline-block ml-1 director-upload-form">
                @csrf
                <input type="file" name="hpp_file" accept="application/pdf" class="director-input hidden" />

                <button type="button"
                        @if(!Route::has($routeUploadDirector)) disabled title="Route upload tidak ditemukan" @endif
                        class="director-upload-btn inline-flex items-center gap-2 text-[11px] px-3 py-1 rounded-md
                               bg-emerald-600 text-white hover:bg-emerald-700">
                    <i class="fas fa-upload text-[10px]"></i> Upload HPP Dirops
                </button>
            </form>

            <div class="text-xs text-slate-400 mt-1">Upload manual oleh Direksi setelah GM menyetujui. (PDF maks 10MB)</div>
        @endif
    @endif

    {{-- Tombol Edit --}}
    @if ($editRouteName && Route::has($editRouteName))
      <a href="{{ route($editRouteName, $notif) }}" class="action-btn bg-yellow-500 hover:bg-yellow-600 ml-1" title="Edit">
        <i class="fas fa-edit"></i>
      </a>
    @else
      <button class="action-btn bg-slate-300 text-slate-600 cursor-not-allowed ml-1" disabled title="Route edit tidak ditemukan">
        <i class="fas fa-edit"></i>
      </button>
    @endif

    {{-- Tombol Hapus --}}
    @if ($deleteRouteName && Route::has($deleteRouteName))
      <form action="{{ route($deleteRouteName, $notif) }}" method="POST" class="inline-block ml-1 delete-form">
        @csrf @method('DELETE')
        <button type="button" class="action-btn bg-slate-500 hover:bg-slate-600 delete-button" title="Hapus">
          <i class="fas fa-trash-alt"></i>
        </button>
      </form>
    @else
      <button class="action-btn bg-slate-300 text-slate-600 cursor-not-allowed ml-1" disabled title="Route delete tidak ditemukan">
        <i class="fas fa-trash-alt"></i>
      </button>
    @endif

</td>
