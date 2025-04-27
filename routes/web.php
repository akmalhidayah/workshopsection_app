<?php

use App\Http\Middleware\Admin;
use App\Http\Middleware\ApprovalMiddleware;
use App\Http\Middleware\PkmMiddleware;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\DashboardUserController;
use App\Http\Controllers\Abnormal\AbnormalController;
use App\Http\Controllers\Abnormalitas\AbnormalitasController;
use App\Http\Controllers\ScopeOfWork\ScopeOfWorkController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\GambarTeknikController;
use App\Http\Controllers\Approval\ApprovalController;
use App\Http\Controllers\Admin\InputHPPController;
use App\Http\Controllers\Admin\InputHPPController2;
use App\Http\Controllers\Admin\InputHPPController3;
use App\Http\Controllers\Admin\SPKController;
use App\Http\Controllers\Admin\PurchaseOrderController;
use App\Http\Controllers\LHPPController;
use App\Http\Controllers\ItemsController;
use App\Http\Controllers\PKMDashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Approval\SPKApprovalController;
use App\Http\Controllers\Approval\HPPApprovalController;
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
Route::get('/notifikasi', [NotificationController::class, 'index'])->name('notifications.index');
Route::post('/notifikasi', [NotificationController::class, 'store'])->name('notifications.store');
Route::get('/notifikasi/{notification_number}/edit', [NotificationController::class, 'edit'])->name('notifications.edit');
Route::patch('/notifikasi/{notification_number}', [NotificationController::class, 'update'])->name('notifications.update');
Route::delete('/notifikasi/{notification_number}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
Route::patch('/notifikasi/{notification_number}/priority', [NotificationController::class, 'updatePriority'])->name('notifications.updatePriority');
Route::patch('/admin/verifikasianggaran/update-status-anggaran/{notification_number}', [NotificationController::class, 'updateStatusAnggaran'])->name('notifications.updateStatusAnggaran');
Route::get('/notifikasi/{notification_number}', [NotificationController::class, 'show'])->name('notifications.show');

// Untuk Index di AbnormalitasController
Route::get('/abnormalitas', [App\Http\Controllers\Abnormalitas\AbnormalitasController::class, 'index'])->name('abnormalitas.index');

// Abnormal Routes
Route::get('/abnormal/create/{notificationNumber}', [App\Http\Controllers\Abnormal\AbnormalController::class, 'create'])->name('abnormal.create');
Route::post('/abnormal/store', [App\Http\Controllers\Abnormal\AbnormalController::class, 'store'])->name('abnormal.store');
Route::get('/abnormal/{notificationNumber}/edit', [App\Http\Controllers\Abnormal\AbnormalController::class, 'edit'])->name('abnormal.edit');
Route::patch('/abnormal/{notificationNumber}', [AbnormalController::class, 'update'])->name('abnormal.update');
Route::get('/abnormal/{notificationNumber}/view', [AbnormalController::class, 'show'])->name('abnormal.view');
Route::get('/abnormal/{notificationNumber}/download-pdf', [AbnormalController::class, 'downloadPDF'])->name('abnormal.download_pdf');


// Scope of Work routes
Route::get('/scopeofwork', [ScopeOfWorkController::class, 'index'])->name('scopeofwork.index');
Route::get('/scopeofwork/create/{notificationNumber}', [ScopeOfWorkController::class, 'create'])->name('scopeofwork.create');
Route::post('/scopeofwork/store', [ScopeOfWorkController::class, 'store'])->name('scopeofwork.store');
Route::get('/scopeofwork/{notificationNumber}/edit', [ScopeOfWorkController::class, 'edit'])->name('scopeofwork.edit');
Route::patch('/scopeofwork/{notificationNumber}', [ScopeOfWorkController::class, 'update'])->name('scopeofwork.update');
Route::post('/save-signature', [ScopeOfWorkController::class, 'saveSignature'])->name('save.signature');
Route::get('/scopeofwork/{notificationNumber}/view', [ScopeOfWorkController::class, 'show'])->name('scopeofwork.view');


// Rute untuk upload dokumen
Route::post('/upload-dokumen', [GambarTeknikController::class, 'uploadDokumen'])->name('upload-dokumen');
// Rute untuk melihat dokumen
Route::get('/gambarteknik/{notificationNumber}/view', [GambarTeknikController::class, 'viewDokumen'])->name('view-dokumen');
// Rute untuk menghapus dokumen
Route::delete('/hapus-dokumen/{notificationNumber}', [GambarTeknikController::class, 'hapusDokumen'])->name('hapus-dokumen');


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
    Route::get('/admin/abnormal/{notificationNumber}/download-pdf', [AbnormalController::class, 'downloadPDF'])
    ->name('admin.abnormal.download_pdf');
    Route::get('/admin/verifikasianggaran', [HomeController::class, 'verifikasiAnggaran'])->name('admin.verifikasianggaran.index');
    Route::get('/admin/purchaserequest', [HomeController::class, 'purchaseRequest'])->name('admin.purchaserequest');
    Route::get('/admin/purchaseorder', [PurchaseOrderController::class, 'index'])->name('admin.purchaseorder');
    Route::post('admin/purchaseorder/{notification_number}', [PurchaseOrderController::class, 'update'])->name('admin.purchaseorder.update');
    Route::get('/admin/updateoa', [HomeController::class, 'updateOA'])->name('admin.updateoa');
    Route::post('/admin/updateoa', [HomeController::class, 'storeOA'])->name('admin.storeOA');
    Route::post('/admin/lpj/{notification_number}', [HomeController::class, 'updateLpj'])->name('lpj.update');
    
});

// Route untuk SPK
Route::prefix('admin')->middleware(['auth'])->group(function () {
    // Route untuk halaman pembuatan SPK, sekarang menerima nomor notifikasi (notification_number) sebagai parameter
    Route::get('/spk/create/{notificationNumber}', [SPKController::class, 'create'])->name('spk.create');
    
    // Route untuk menyimpan data SPK
    Route::post('/spk/store', [SPKController::class, 'store'])->name('spk.store');
    
    // Route untuk melihat detail SPK berdasarkan nomor notifikasi
    Route::get('/spk/{notification_number}', [SPKController::class, 'show'])->name('spk.show');
});

Route::prefix('admin')->middleware(['auth', 'admin'])->name('admin.')->group(function() {
    Route::get('inputhpp', [InputHPPController::class, 'index'])->name('inputhpp.index');
    Route::get('inputhpp/create-hpp1', [InputHPPController::class, 'createHpp1'])->name('inputhpp.create_hpp1');
    Route::post('inputhpp/store', [InputHPPController::class, 'store'])->name('inputhpp.store'); 
    Route::get('inputhpp/create-hpp2', [InputHPPController2::class, 'createHpp2'])->name('inputhpp.create_hpp2');
    Route::get('inputhpp/create-hpp3', [InputHPPController3::class, 'createHpp3'])->name('inputhpp.create_hpp3');
    Route::get('inputhpp/view-hpp1/{notification_number}', [InputHPPController::class, 'viewHpp1'])->name('inputhpp.view_hpp1');
    Route::get('inputhpp/view-hpp2/{notification_number}', [InputHPPController2::class, 'viewHpp2'])->name('inputhpp.view_hpp2');
    Route::get('inputhpp/view-hpp3/{notification_number}', [InputHPPController3::class, 'viewHpp3'])->name('inputhpp.view_hpp3');
    Route::delete('inputhpp/{notification_number}', [InputHPPController::class, 'destroy'])->name('inputhpp.destroy');
    Route::get('inputhpp/edit/{notification_number}', [InputHPPController::class, 'edit'])->name('inputhpp.edit');
    Route::put('inputhpp/update/{notification_number}', [InputHPPController::class, 'update'])->name('inputhpp.update');  
    Route::get('inputhpp/{notification_number}/download-hpp1', [InputHPPController::class, 'downloadPDFHpp1'])->name('inputhpp.download_hpp1');
    Route::get('inputhpp/{notification_number}/download-hpp2', [InputHPPController2::class, 'downloadPDFHpp2'])->name('inputhpp.download_hpp2');
    Route::get('inputhpp/{notification_number}/download-hpp3', [InputHPPController3::class, 'downloadPDFHpp3'])->name('inputhpp.download_hpp3');
});

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/lpj', [HomeController::class, 'lpj'])->name('admin.lpj');
    Route::post('/admin/lpj/{notification_number}', [HomeController::class, 'updateLpj'])->name('lpj.update');
});

//Route LHPP ADMIN
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/lhpp', [App\Http\Controllers\Admin\LHPPAdminController::class, 'index'])->name('admin.lhpp.index');
    Route::get('/admin/lhpp/{notification_number}', [App\Http\Controllers\Admin\LHPPAdminController::class, 'show'])->name('admin.lhpp.show');
    Route::post('/admin/lhpp/{notification_number}/approve', [App\Http\Controllers\Admin\LHPPAdminController::class, 'approve'])->name('admin.lhpp.approve');
    Route::post('/admin/lhpp/{notification_number}/reject', [App\Http\Controllers\Admin\LHPPAdminController::class, 'reject'])->name('admin.lhpp.reject');
    Route::get('/admin/lhpp/{notification_number}/download-pdf', [App\Http\Controllers\Admin\LHPPAdminController::class, 'downloadPDF'])
    ->name('admin.lhpp.download_pdf');

});
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('unit_work', App\Http\Controllers\Admin\UnitWorkController::class);
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

        // Route untuk HPP di PKM (Download PDF)
        Route::get('inputhpp/{notification_number}/download-hpp1', [InputHPPController::class, 'downloadPDFHpp1'])->name('inputhpp.download_hpp1');
        Route::get('inputhpp/{notification_number}/download-hpp2', [InputHPPController2::class, 'downloadPDFHpp2'])->name('inputhpp.download_hpp2');
        Route::get('inputhpp/{notification_number}/download-hpp3', [InputHPPController3::class, 'downloadPDFHpp3'])->name('inputhpp.download_hpp3');

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
        Route::get('lhpp/get-abnormal-description/{notificationNumber}', [LHPPController::class, 'getAbnormalDescription'])->name('lhpp.get-abnormal-description');

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
    Route::get('/get-item-data/{notification_number}', [ItemsController::class, 'getItemData'])->name('getItemData');

// Routes for Abnormal Approval
Route::middleware(['auth'])->group(function () {
    Route::get('/approval', [ApprovalController::class, 'index'])->name('approval.index');
    Route::post('/approval/sign/{signType}/{notificationNumber}', [ApprovalController::class, 'saveSignature'])->name('approval.saveSignature');
    Route::get('/approval/get-old-signature/{signType}/{notificationNumber}', [ApprovalController::class, 'getOldSignature']);
    Route::get('/approval/abnormal/{notificationNumber}/download-pdf', [AbnormalController::class, 'downloadPDF'])->name('abnormal.download_pdf');    
});
// Routes for SPK Approval
Route::middleware(['auth'])->group(function () {
    Route::get('/approval/spk', [SPKApprovalController::class, 'index'])->name('approval.spk.index'); // Mengarahkan ke rute yang benar
    Route::post('/approval/spk/sign/{signType}/{nomorSpk}', [SPKApprovalController::class, 'saveSignature'])->name('approval.spk.saveSignature');
});
// Routes for HPP Approval
Route::middleware(['auth'])->group(function () {
    Route::get('/approval/hpp', [App\Http\Controllers\Approval\HPPApprovalController::class, 'index'])->name('approval.hpp.index');
    Route::post('/approval/hpp/sign/{signType}/{notificationNumber}', [App\Http\Controllers\Approval\HPPApprovalController::class, 'saveSignature'])->name('approval.hpp.saveSignature');
    Route::post('/approval/hpp/reject/{signType}/{notificationNumber}', [App\Http\Controllers\Approval\HPPApprovalController::class, 'reject'])->name('approval.hpp.reject');
    Route::post('/approval/hpp/notes/{notification_number}/{type}', [App\Http\Controllers\Approval\HPPApprovalController::class, 'saveNotes'])->name('approval.hpp.saveNotes');
    Route::get('/approval/hpp/get-old-signature/{signType}/{notificationNumber}', [HPPApprovalController::class, 'getOldSignature'])
    ->name('approval.hpp.getOldSignature');
});
Route::middleware(['auth'])->prefix('approval/hpp')->name('approval.hpp.')->group(function () {
    Route::get('{notification_number}/download-hpp1', [InputHPPController::class, 'downloadPDFHpp1'])->name('download_hpp1');
    Route::get('{notification_number}/download-hpp2', [InputHPPController2::class, 'downloadPDFHpp2'])->name('download_hpp2');
    Route::get('{notification_number}/download-hpp3', [InputHPPController3::class, 'downloadPDFHpp3'])->name('download_hpp3');
});


// Routes for LHPP Approval
Route::middleware(['auth'])->prefix('approval/lhpp')->name('approval.lhpp.')->group(function () {
    Route::get('/', [App\Http\Controllers\Approval\LHPPApprovalController::class, 'index'])->name('index');
    Route::post('/sign/{signType}/{notificationNumber}', [App\Http\Controllers\Approval\LHPPApprovalController::class, 'saveSignature'])->name('saveSignature');
    Route::post('/notes/{notification_number}/{type}', [App\Http\Controllers\Approval\LHPPApprovalController::class, 'saveNotes'])->name('saveNotes');
    Route::put('/status/{notification_number}', [App\Http\Controllers\Approval\LHPPApprovalController::class, 'updateStatus'])->name('updateStatus');
    Route::post('/reject/{signType}/{notificationNumber}', [App\Http\Controllers\Approval\LHPPApprovalController::class, 'reject'])->name('reject');

    // ✅ Route untuk download PDF menggunakan LHPPController
    Route::get('/{notification_number}/download-pdf', [App\Http\Controllers\LHPPController::class, 'downloadPDF'])->name('download_pdf');
});


// Route::get('/send-wa', function() {
//     $response = Http::withHeaders([
//         'Authorization' => '3GBUnGXz7gPbP5AJKA4a'
//     ])->post('https://api.fonnte.com/send', [
//         'target' => '083150898767',
//         'message' => 'Ini Pesan Laravel'
//     ]);

//     dd(json_decode($response, true));
// });





