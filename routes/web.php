<?php

use App\Http\Middleware\Admin;
use App\Http\Middleware\ApprovalMiddleware;
use App\Http\Middleware\PkmMiddleware;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\DashboardUserController;
use App\Http\Controllers\DokumenOrderController;
use App\Http\Controllers\Abnormal\AbnormalController;
use App\Http\Controllers\Abnormalitas\AbnormalitasController;
use App\Http\Controllers\ScopeOfWork\ScopeOfWorkController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\KawatLasController;
use App\Http\Controllers\Admin\AdminNotificationController;
use App\Http\Controllers\Admin\JenisKawatLasController;
use App\Http\Controllers\Admin\OrderBengkelController;
use App\Http\Controllers\GambarTeknikController;
use App\Http\Controllers\Approval\ApprovalController;
use App\Http\Controllers\Admin\BaseHppController;
use App\Http\Controllers\Admin\Hpp1Controller;
use App\Http\Controllers\Admin\Hpp2Controller;
use App\Http\Controllers\Admin\Hpp3Controller;
use App\Http\Controllers\Admin\Hpp4Controller;
use App\Http\Controllers\Admin\Hpp5Controller;
use App\Http\Controllers\Admin\Hpp6Controller;
use App\Http\Controllers\Admin\HppApprovalController;
use App\Http\Controllers\SignatureController;
use App\Http\Controllers\Approval\HPPApprovalMagicController;
use App\Http\Controllers\Admin\SPKController;
use App\Http\Controllers\Admin\PurchaseOrderController;
use App\Http\Controllers\Admin\AdminAccessController;
use App\Http\Controllers\LHPPController;
use App\Http\Controllers\Admin\LHPPAdminController;
use App\Http\Controllers\LHPPApprovalController as TokenLHPPApprovalController;
use App\Http\Controllers\ItemsController;
use App\Http\Controllers\PKMDashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\SPKApprovalController;
use App\Http\Controllers\Admin\UploadInfoController;
use App\Http\Controllers\WelcomeController;
use Illuminate\Support\Facades\Route;


Route::get('/', [WelcomeController::class, 'index'])->name('welcome');

// Redirect to login if accessing root
Route::get('/login', function () {
    return redirect('login');
});

// Group routes that require authentication and email verification
Route::middleware(['auth', 'verified'])->group(function () {

// Dashboard route
Route::get('/dashboard', [DashboardUserController::class, 'index'])->name('dashboard');
// LHPP detail (accessible by any authenticated user)
Route::get('lhpp/{notification_number}', [App\Http\Controllers\LHPPController::class, 'show'])->name('lhpp.show');

// Notifikasi routes
Route::middleware('admin.menu')->group(function () {
    Route::get('/notifikasi', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifikasi', [NotificationController::class, 'store'])->name('notifications.store');
    Route::get('/notifikasi/{notification_number}/edit', [NotificationController::class, 'edit'])->name('notifications.edit');
    Route::patch('/notifikasi/{notification_number}', [NotificationController::class, 'update'])->name('notifications.update');
    Route::delete('/notifikasi/{notification_number}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::patch('/notifikasi/{notification_number}/priority', [NotificationController::class, 'updatePriority'])->name('notifications.updatePriority');
    Route::get('/notifikasi/{notification_number}', [NotificationController::class, 'show'])->name('notifications.show');
});


// ===================================
// Dokumen Orders + Scope of Work Routes
// ===================================
Route::prefix('dokumen-orders')->name('dokumen_orders.')->group(function () {

    // Index Dokumen Orders
    Route::get('/', [DokumenOrderController::class, 'index'])->name('index');

    // Upload File (abnormalitas / gambar teknik)
    Route::post('/upload', [DokumenOrderController::class, 'upload'])->name('upload');

    // ==============================
    // Scope of Work Nested Routes (PENTING! taruh di atas dynamic route)
    // ==============================
    Route::prefix('scopeofwork')->name('scope.')->group(function () {

        // Partial/modal endpoints (AJAX)
        Route::get('/modal-create/{notificationNumber}', [ScopeOfWorkController::class, 'modalCreate'])->name('modal_create');
        Route::get('/modal-edit/{notificationNumber}',   [ScopeOfWorkController::class, 'modalEdit'])->name('modal_edit');

        // Existing route (page-based) — biarkan seperti biasa
        Route::get('/create/{notificationNumber}', [ScopeOfWorkController::class, 'create'])->name('create');
        Route::post('/store', [ScopeOfWorkController::class, 'store'])->name('store');
        Route::get('/{notificationNumber}/edit', [ScopeOfWorkController::class, 'edit'])->name('edit');
        Route::patch('/{notificationNumber}', [ScopeOfWorkController::class, 'update'])->name('update');
        Route::post('/save-signature', [ScopeOfWorkController::class, 'saveSignature'])->name('saveSignature');
        Route::get('/{notificationNumber}/view', [ScopeOfWorkController::class, 'show'])->name('view');
        Route::get('/{notificationNumber}/download-pdf', [ScopeOfWorkController::class, 'downloadPDF'])->name('download_pdf');

    });


    // View dokumen (abnormalitas, gambar teknik)
    Route::get('/{notificationNumber}/{jenis}/view', [DokumenOrderController::class, 'view'])->name('view');

    // Hapus dokumen
    Route::delete('/{notificationNumber}/{jenis}/delete', [DokumenOrderController::class, 'destroy'])->name('delete');
});

Route::get('/kawatlas', [KawatLasController::class, 'index'])->name('kawatlas.index');
Route::post('/kawatlas', [KawatLasController::class, 'store'])->name('kawatlas.store');
Route::get('/kawatlas/{id}/edit', [KawatLasController::class, 'edit'])->name('kawatlas.edit');
Route::patch('/kawatlas/{id}', [KawatLasController::class, 'update'])->name('kawatlas.update');
Route::delete('/kawatlas/{id}', [KawatLasController::class, 'destroy'])->name('kawatlas.destroy');
Route::put('/admin/kawatlas/{id}/jumlah', [KawatLasController::class, 'updateJumlah'])
     ->name('admin.kawatlas.updateJumlah');
     Route::put('/admin/kawatlas/{id}/status', [KawatLasController::class, 'updateStatus'])
     ->name('admin.kawatlas.updateStatus');


// Admin routes order bengkel
Route::prefix('admin')->name('admin.')->middleware(['auth','admin'])->group(function () {

    Route::get('order-bengkel', [OrderBengkelController::class, 'index'])->name('orderbengkel.index');
    Route::get('order-bengkel/create', [OrderBengkelController::class, 'create'])->name('orderbengkel.create');
    Route::get('order-bengkel/{notification_number}/edit', [OrderBengkelController::class, 'edit'])->name('orderbengkel.edit');

    // ✅ Tambahan: update inline status (AJAX atau form submit)
    // Menangani update per-notification (status anggaran/material/progress & catatan)
    Route::patch('order-bengkel/{notification_number}', [OrderBengkelController::class, 'update'])
         ->name('orderbengkel.update');

});

// Profile routes
Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Authentication routes
require __DIR__.'/auth.php';

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/users', [UserController::class, 'index'])->name('admin.users.index');
    Route::get('/admin/users/{id}/edit', [UserController::class, 'edit'])->name('admin.users.edit');
    Route::put('/admin/users/{id}', [UserController::class, 'update'])->name('admin.users.update');
    Route::delete('/admin/users/{id}', [UserController::class, 'destroy'])->name('admin.users.destroy');

});
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth','verified'])
    ->group(function () {
        // Resource utama Jenis Kawat Las
        Route::resource('jenis-kawat-las', JenisKawatLasController::class)
             ->parameters(['jenis-kawat-las' => 'jenis_kawat_las']);

        // ✅ Tambahan route untuk cost element global
        Route::get('cost-element/edit', [JenisKawatLasController::class, 'editCostElement'])
            ->name('cost-element.edit');
        Route::put('cost-element/update', [JenisKawatLasController::class, 'updateCostElement'])
            ->name('cost-element.update');
    });


Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/uploadinfo', [UploadInfoController::class, 'index'])->name('admin.uploadinfo');
    Route::post('/admin/uploadinfo', [UploadInfoController::class, 'upload'])->name('admin.uploadinfo.upload');
    Route::delete('/admin/uploadinfo/{filename}', [UploadInfoController::class, 'delete'])->name('admin.uploadinfo.delete');
    Route::post('/admin/uploadinfo/toggle-visibility', [UploadInfoController::class, 'toggleVisibility'])->name('admin.uploadinfo.toggle');
});

    // Admin dashboard route
    Route::middleware(['auth', 'admin'])->group(function () {
        Route::get('/admin/dashboard', [HomeController::class, 'index'])->name('admin.dashboard');
        Route::get('/admin/get-years', [HomeController::class, 'getYears']);
        Route::get('/admin/realisasi-biaya', [HomeController::class, 'realisasiBiaya']);
        Route::get('/admin/get-months', [HomeController::class, 'getMonths']);
        Route::get('/admin/notifikasi', [HomeController::class, 'notifikasi'])->name('notifikasi.index');
        // ✅ Halaman Verifikasi Anggaran (tampil tabel)
        Route::get('/admin/verifikasianggaran', [HomeController::class, 'verifikasiAnggaran'])
            ->name('admin.verifikasianggaran.index');

        // ✅ Route baru untuk update Verifikasi Anggaran
        Route::patch('/admin/verifikasianggaran/{notification_number}', [HomeController::class, 'updateVerifikasiAnggaran'])
            ->name('admin.verifikasianggaran.update');
        Route::get('/admin/purchaserequest', [HomeController::class, 'purchaseRequest'])->name('admin.purchaserequest');
        Route::get('/admin/purchaseorder', [PurchaseOrderController::class, 'index'])->name('admin.purchaseorder');
        Route::post('admin/purchaseorder/{notification_number}', [PurchaseOrderController::class, 'update'])->name('admin.purchaseorder.update');
        Route::get('/admin/updateoa', [HomeController::class, 'updateOA'])->name('admin.updateoa');
        Route::post('/admin/updateoa', [HomeController::class, 'storeOA'])->name('admin.storeOA');
        Route::get('/admin/access-control', [AdminAccessController::class, 'index'])->name('admin.access-control.index');
        Route::post('/admin/access-control', [AdminAccessController::class, 'update'])->name('admin.access-control.update');
        
    });
    Route::middleware(['auth', 'admin'])
        ->prefix('admin')
        ->name('admin.notifications.')
        ->group(function () {

            Route::get('/notifications/dropdown', [AdminNotificationController::class, 'dropdown'])
                ->name('dropdown');

            Route::get('/notifications/read/{id}', [AdminNotificationController::class, 'markAsRead'])
                ->name('read');
        });

// User update E-KORIN (hanya nomor + status)
Route::middleware(['auth'])->patch('/verifikasianggaran/{notification_number}/ekorin', 
    [App\Http\Controllers\NotificationController::class, 'updateEkorin']
)->name('verifikasianggaran.ekorin.update');


// Route untuk SPK
Route::prefix('admin')->middleware(['auth'])->group(function () {
    // Route untuk halaman pembuatan SPK, sekarang menerima nomor notifikasi (notification_number) sebagai parameter
    Route::get('/spk/create/{notificationNumber}', [SPKController::class, 'create'])->name('spk.create');
    
    // Route untuk menyimpan data SPK
    Route::post('/spk/store', [SPKController::class, 'store'])->name('spk.store');
    
    // Route untuk melihat detail SPK berdasarkan nomor notifikasi
    Route::get('/spk/{notification_number}', [SPKController::class, 'show'])->name('spk.show');
});

/* =========================
   APPROVAL SPK (TOKEN LINK)
   ========================= */
Route::middleware(['auth'])
    ->prefix('approval/spk')
    ->name('approval.spk.')
    ->group(function () {

        // (opsional) list SPK approval untuk user login
        Route::get('/', [SPKApprovalController::class, 'index'])
            ->name('index');

        // halaman approve via TOKEN
        Route::get('{token}', [SPKApprovalController::class, 'show'])
            ->name('sign');

        // submit tanda tangan
        Route::post('{token}', [SPKApprovalController::class, 'sign'])
            ->name('do');
    });

/* =========================
   ADMIN / HPP
   ========================= */
Route::prefix('admin')->middleware(['auth','admin'])->name('admin.')->group(function () {

    Route::prefix('inputhpp')->name('inputhpp.')->group(function () {

        // Index (pakai BaseHppController via Hpp1Controller@index)
        Route::get('/', [Hpp1Controller::class, 'index'])->name('index');
// Re-issue token (POST) — berada di: admin.inputhpp.reissue_token
Route::post('{notification_number}/reissue-token', [HppApprovalController::class, 'reissueToken'])
     ->name('reissue_token');

        // HPP 1 — > 250jt
        Route::controller(Hpp1Controller::class)->group(function () {
            Route::get('create-hpp1', 'create')->name('create_hpp1');
            Route::post('store-hpp1',  'store')->name('store_hpp1');
            Route::get('edit-hpp1/{notification_number}',   'edit')->name('edit_hpp1');
            Route::put('update-hpp1/{notification_number}', 'update')->name('update_hpp1');
            Route::delete('delete-hpp1/{notification_number}', 'destroy')->name('destroy_hpp1');
            Route::get('download-hpp1/{notification_number}',  'downloadPDF')->name('download_hpp1');
            // Form upload
            Route::get('director-upload/{notification_number}', 'showDirectorUpload')
                ->name('director_upload');

            // Simpan upload
            Route::post('director-upload/{notification_number}', 'storeDirectorUpload')
                ->name('director_upload.store');

            // Download file direktur (opsional)
            Route::get('director-download/{notification_number}', 'downloadDirector')
                ->name('download_director');
            
        });

        // HPP 2 — < 250jt
        Route::controller(Hpp2Controller::class)->group(function () {
            Route::get('create-hpp2', 'create')->name('create_hpp2');
            Route::post('store-hpp2',  'store')->name('store_hpp2');
            Route::get('edit-hpp2/{notification_number}',   'edit')->name('edit_hpp2');
            Route::put('update-hpp2/{notification_number}', 'update')->name('update_hpp2');
            Route::delete('delete-hpp2/{notification_number}', 'destroy')->name('destroy_hpp2');
            Route::get('download-hpp2/{notification_number}',  'downloadPDF')->name('download_hpp2');
        });

        // HPP 3 — Bengkel >250JT
        Route::controller(Hpp3Controller::class)->group(function () {
            Route::get('create-hpp3', 'create')->name('create_hpp3');
            Route::post('store-hpp3',  'store')->name('store_hpp3');
            Route::get('edit-hpp3/{notification_number}',   'edit')->name('edit_hpp3');
            Route::put('update-hpp3/{notification_number}', 'update')->name('update_hpp3');
            Route::delete('delete-hpp3/{notification_number}', 'destroy')->name('destroy_hpp3');
            Route::get('download-hpp3/{notification_number}',  'downloadPDF')->name('download_hpp3');
        });

        // HPP 4 – Bengkel <250JT
        Route::controller(Hpp4Controller::class)->group(function () {
            Route::get('create-hpp4', 'create')->name('create_hpp4');
            Route::post('store-hpp4',  'store')->name('store_hpp4');
            Route::get('edit-hpp4/{notification_number}',   'edit')->name('edit_hpp4');
            Route::put('update-hpp4/{notification_number}', 'update')->name('update_hpp4');
            Route::delete('delete-hpp4/{notification_number}', 'destroy')->name('destroy_hpp4');
            Route::get('download-hpp4/{notification_number}',  'downloadPDF')->name('download_hpp4');
        });

        // HPP 5 – placeholder
        Route::controller(Hpp5Controller::class)->group(function () {
            Route::get('create-hpp5', 'create')->name('create_hpp5');
            Route::post('store-hpp5',  'store')->name('store_hpp5');
            Route::get('edit-hpp5/{notification_number}',   'edit')->name('edit_hpp5');
            Route::put('update-hpp5/{notification_number}', 'update')->name('update_hpp5');
            Route::delete('delete-hpp5/{notification_number}', 'destroy')->name('destroy_hpp5');
            Route::get('download-hpp5/{notification_number}',  'downloadPDF')->name('download_hpp5');
        });

        // HPP 6 – placeholder
        Route::controller(Hpp6Controller::class)->group(function () {
            Route::get('create-hpp6', 'create')->name('create_hpp6');
            Route::post('store-hpp6',  'store')->name('store_hpp6');
            Route::get('edit-hpp6/{notification_number}',   'edit')->name('edit_hpp6');
            Route::put('update-hpp6/{notification_number}', 'update')->name('update_hpp6');
            Route::delete('delete-hpp6/{notification_number}', 'destroy')->name('destroy_hpp6');
            Route::get('download-hpp6/{notification_number}',  'downloadPDF')->name('download_hpp6');
        });
    });
});

/* =========================
   APPROVAL HPP (TOKEN LINK)
   ========================= */
Route::prefix('approval/hpp')->middleware(['auth'])->name('approval.hpp.')->group(function () {
    // halaman approve via token
    Route::get('{token}',  [HppApprovalController::class, 'show'])->name('sign'); // GET
    Route::post('{token}', [HppApprovalController::class, 'sign'])->name('do');   // POST

    // download PDF (opsional dari halaman approval)
    Route::get('{notification_number}/download-hpp1', [Hpp1Controller::class, 'downloadPDF'])->name('download_hpp1');
    Route::get('{notification_number}/download-hpp2', [Hpp2Controller::class, 'downloadPDF'])->name('download_hpp2');
    Route::get('{notification_number}/download-hpp3', [Hpp3Controller::class, 'downloadPDF'])->name('download_hpp3');
    Route::get('{notification_number}/download-hpp4', [Hpp4Controller::class, 'downloadPDF'])->name('download_hpp4');
    Route::get('{notification_number}/download-hpp5', [Hpp5Controller::class, 'downloadPDF'])->name('download_hpp5');
    Route::get('{notification_number}/download-hpp6', [Hpp6Controller::class, 'downloadPDF'])->name('download_hpp6');
});


/* Stream tanda tangan */
Route::middleware(['auth'])->get('/signatures/stream', [SignatureController::class, 'stream'])
    ->name('signatures.stream');

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/lpj', [HomeController::class, 'lpj'])->name('admin.lpj');
    Route::post('/admin/lpj/{notification_number}', [HomeController::class, 'updateLpj'])->name('lpj.update');
    
});

//Route LHPP ADMIN
Route::prefix('admin')->middleware(['auth', 'admin'])->name('admin.')->group(function () {

    // LHPP List + Detail
    Route::get('lhpp', [LHPPAdminController::class, 'index'])->name('lhpp.index');
    Route::get('lhpp/{notification_number}', [LHPPAdminController::class, 'show'])->name('lhpp.show');

    // Store garansi
    Route::post('lhpp/{notification_number}/garansi', [LHPPAdminController::class, 'storeGaransi'])
        ->name('lhpp.storeGaransi');

    // Approve / Reject LHPP
    Route::post('lhpp/{notification_number}/approve', [LHPPAdminController::class, 'approve'])
        ->name('lhpp.approve');
    Route::post('lhpp/{notification_number}/reject', [LHPPAdminController::class, 'reject'])
        ->name('lhpp.reject');

    // Download PDF
    Route::get('lhpp/{notification_number}/download-pdf', [LHPPAdminController::class, 'downloadPDF'])
        ->name('lhpp.download_pdf');

});
/* =========================
   APPROVAL LHPP (TOKEN LINK)
   ========================= */
Route::middleware(['auth'])
    ->prefix('approval/lhpp')
    ->name('approval.lhpp.')
    ->group(function () {

        // (opsional) list LHPP – tetap aman karena index di-controller cek isPkmManager()
        Route::get('/', [TokenLHPPApprovalController::class, 'index'])
            ->name('index');

        // halaman approve via TOKEN
        Route::get('{token}', [TokenLHPPApprovalController::class, 'show'])
            ->name('sign');

        // submit tanda tangan
        Route::post('{token}', [TokenLHPPApprovalController::class, 'sign'])
            ->name('do');

        // download PDF dari halaman approval
        Route::get('{notification_number}/download-pdf', [LHPPController::class, 'downloadPDF'])
            ->name('download_pdf');
    });


Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('unit_work', App\Http\Controllers\Admin\UnitWorkController::class);
});

// Route Garansi
Route::middleware(['auth','admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/garansi', [App\Http\Controllers\Admin\GaransiController::class, 'index'])->name('garansi.index');
});


//Route PKM
Route::middleware(['auth', PkmMiddleware::class])
    ->prefix('pkm')
    ->name('pkm.')
    ->group(function () {
        // Dashboard PKM
        Route::get('/dashboard', [PKMDashboardController::class, 'index'])->name('dashboard');

        // Job Waiting List
        Route::get('/jobwaiting', [PKMDashboardController::class, 'jobWaiting'])->name('jobwaiting');
        Route::post('/jobwaiting/update-progress/{notification_number}', [PKMDashboardController::class, 'updateProgress'])->name('jobwaiting.updateProgress');

        // Laporan
        Route::get('/laporan', [PKMDashboardController::class, 'laporan'])->name('laporan');

        // Detail Notifikasi
        Route::get('/notification/{notification_number}', [PKMDashboardController::class, 'notificationDetail'])->name('notification.detail');

        // Route untuk SPK
        Route::get('/spk/{notificationNumber}', [SPKController::class, 'view'])->name('spk.view');

        // Route untuk Gambar Teknik
        Route::get('/gambar-teknik/{notificationNumber}', [GambarTeknikController::class, 'view'])->name('gambar_teknik.view');

        // Route untuk LHPP di PKM
        Route::get('lhpp', [LHPPController::class, 'index'])->name('lhpp.index');
        Route::get('lhpp/create', [LHPPController::class, 'create'])->name('lhpp.create');
        Route::post('lhpp', [LHPPController::class, 'store'])->name('lhpp.store');
        Route::get('lhpp/{notification_number}', [LHPPController::class, 'show'])->name('lhpp.show');
        Route::get('lhpp/{notification_number}/edit', [LHPPController::class, 'edit'])->name('lhpp.edit');
        Route::put('lhpp/{notification_number}', [LHPPController::class, 'update'])->name('lhpp.update');
        Route::delete('lhpp/{notification_number}', [LHPPController::class, 'destroy'])->name('lhpp.destroy');
        Route::get('lhpp/{notification_number}/download-pdf', [LHPPController::class, 'downloadPDF'])->name('lhpp.download_pdf');

        // Route tambahan untuk data terkait LHPP
        Route::get('lhpp/get-nomor-order/{notificationNumber}', [LHPPController::class, 'getNomorOrder'])->name('lhpp.get-nomor-order');
        Route::get('lhpp/get-purchase-order/{notificationNumber}', [LHPPController::class, 'getPurchaseOrder'])->name('lhpp.get-purchase-order');
       Route::get('lhpp/get-jobname/{notificationNumber}', [LHPPController::class, 'getJobName']);


        // Route untuk kalkulasi durasi kerja dalam LHPP
        Route::get('calculate-work-duration/{notificationNumber}/{tanggalSelesai}', [LHPPController::class, 'calculateWorkDuration'])->name('lhpp.calculate-work-duration');

        // Route untuk Item Kebutuhan
        Route::prefix('items')->name('items.')->group(function () {
            Route::get('/', [ItemsController::class, 'index'])->name('index'); // ✅ Menampilkan daftar item
            Route::get('/create', [ItemsController::class, 'create'])->name('create'); // ✅ Form tambah item
            Route::post('/store', [ItemsController::class, 'store'])->name('store'); // ✅ Simpan item ke DB
            Route::get('/{notification_number}', [ItemsController::class, 'show'])->name('show'); // ✅ Detail item
            Route::get('/{notification_number}/edit', [ItemsController::class, 'edit'])->name('edit'); // ✅ Form edit item
            Route::put('/{notification_number}', [ItemsController::class, 'update'])->name('update'); // ✅ Simpan perubahan
            Route::post('/{notification_number}/update-approval', [ItemsController::class, 'updateApproval'])->name('update-approval');
            Route::delete('/{notification_number}', [ItemsController::class, 'destroy'])->name('destroy'); // ✅ Hapus item
        }); 
    });
            Route::get('lhpp/{notification_number}/download-pdf', [LHPPController::class, 'downloadPDF'])->name('lhpp.download_pdf');
    Route::get('/get-item-data/{notification_number}', [ItemsController::class, 'getItemData'])->name('getItemData');

Route::get('/pkm/hpp/director/{notification}', 
    [PKMDashboardController::class, 'downloadDirectorHpp']
)->name('pkm.hpp.download_director');




// Route::post('/webhook/whatsapp', [\App\Http\Controllers\WhatsAppWebhookController::class, 'receive']);
// Route::get('/webhook/whatsapp', [\App\Http\Controllers\WhatsAppWebhookController::class, 'verify']); // challenge verify (optional)

