<?php

namespace App\Http\Controllers\Abnormal;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Abnormal;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use App\Mail\AbnormalitasNotification; // Pastikan jalur ini benar
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;


class AbnormalController extends Controller
{
    public function create($notificationNumber)
{
    // Ambil data notifikasi berdasarkan notification_number
    $notification = Notification::where('notification_number', $notificationNumber)->first();

    // Pastikan bahwa notifikasi ditemukan
    if (!$notification) {
        return redirect()->route('abnormalitas.index')->with('error', 'Notifikasi tidak ditemukan.');
    }

    // Kirim data notifikasi ke view create
    return view('abnormal.create', compact('notification'));
}


public function store(Request $request)
    {
        $request->validate([
            'abnormal_title' => 'required|string|max:255',
            'notification_number' => 'required|string|max:255',
            'unit_kerja' => 'required|string|max:255',
            'abnormal_date' => 'required|date',
            'problem_description' => 'required|string',
            'root_cause' => 'required|string',
            'immediate_actions' => 'required|string',
            'summary' => 'required|string',
            'actions.*' => 'required|string',
            'by.*' => 'required|string',
            'when.*' => 'required|date',
            'risks.*' => 'nullable|string',
            'fotos.*' => 'nullable|file|mimes:jpg,png,jpeg,pdf,doc,docx',
            'keterangans.*' => 'nullable|string',
        ]);

        $actions = [];
        if ($request->has('actions')) {
            foreach ($request->actions as $key => $action) {
                $actions[] = [
                    'action' => $action,
                    'by' => $request->by[$key],
                    'when' => $request->when[$key],
                ];
            }
        }

        $risks = $request->risks ?? [];

        $files = [];
        if ($request->hasFile('fotos')) {
            foreach ($request->file('fotos') as $key => $file) {
                if ($file) {
                    $path = $file->store('abnormalitas', 'public');
                    $filename = basename($path);

                    $files[] = [
                        'file_path' => $filename,
                        'keterangan' => $request->keterangans[$key] ?? '',
                    ];
                }
            }
        }

        // Menyimpan data ke database
        $abnormal = Abnormal::create([
            'notification_number' => $request->notification_number,
            'abnormal_title' => $request->abnormal_title,
            'unit_kerja' => $request->unit_kerja,
            'abnormal_date' => $request->abnormal_date,
            'problem_description' => $request->problem_description,
            'root_cause' => $request->root_cause,
            'immediate_actions' => $request->immediate_actions,
            'summary' => $request->summary,
            'actions' => json_encode($actions),
            'risks' => json_encode($risks),
            'files' => json_encode($files),
        ]);

        // Kirim email notifikasi ke user yang bersangkutan
        $managers = User::where('unit_work', $request->unit_kerja)
            ->where('jabatan', 'Manager')
            ->get();


            foreach ($managers as $manager) {
                Http::withHeaders([
                    'Authorization' => 'KBTe2RszCgc6aWhYapcv', // API key Fonnte Anda
                ])->post('https://api.fonnte.com/send', [
                    'target' => $manager->whatsapp_number,
                    'message' => "Permintaan Approval Abnormalitas Pekerjaan :\nNomor Order: {$abnormal->notification_number}\nNama Pekerjaan: {$abnormal->abnormal_title}\nUnit Kerja: {$abnormal->unit_kerja}\nDeskripsi Masalah: {$abnormal->problem_description}\n\nSilakan login dan tanda tangani dokumen:\nhttps://sectionofworkshop.com/approval",
                ]);
            }
            
        // foreach ($managers as $manager) {
        //     Mail::to($manager->email)->send(new AbnormalitasNotification($abnormal, $manager));
        // }

        return redirect()->route('abnormalitas.index')->with('success', 'Form berhasil disimpan dan cek silahkan cek dokumen.');
        }

public function edit($notificationNumber)
{
    $abnormal = Abnormal::where('notification_number', $notificationNumber)->firstOrFail();
    $notification = Notification::where('notification_number', $abnormal->notification_number)->first();

    // Decode the JSON fields back into arrays
    $abnormal->actions = json_decode($abnormal->actions, true);
    $abnormal->risks = json_decode($abnormal->risks, true);
    $abnormal->files = json_decode($abnormal->files, true);

    if (!$notification) {
        return redirect()->route('abnormalitas.index')->with('error', 'Notifikasi terkait tidak ditemukan.');
    }

    return view('abnormal.edit', compact('abnormal', 'notification'));
}



public function update(Request $request, $notificationNumber)
{
    $abnormal = Abnormal::where('notification_number', $notificationNumber)->firstOrFail();

    $request->validate([
        'abnormal_title' => 'required|string|max:255',
        'abnormal_date' => 'required|date',
        'problem_description' => 'required|string',
        'root_cause' => 'required|string',
        'immediate_actions' => 'required|string',
        'summary' => 'required|string',
        'actions.*' => 'required|string',
        'by.*' => 'required|string',
        'when.*' => 'required|date',
        'risks.*' => 'nullable|string',
        'fotos.*' => 'nullable|file|mimes:jpg,png,jpeg,pdf,doc,docx',
        'keterangans.*' => 'nullable|string',
    ]);

    $actions = [];
    if ($request->has('actions')) {
        foreach ($request->actions as $key => $action) {
            $actions[] = [
                'action' => $action,
                'by' => $request->by[$key],
                'when' => $request->when[$key],
            ];
        }
    }

    $risks = $request->risks ?? [];

    $files = json_decode($abnormal->files, true);
    if ($request->hasFile('fotos')) {
        foreach ($request->file('fotos') as $key => $file) {
            if ($file) {
                $path = $file->store('public/abnormalitas');
                $filename = basename($path);

                $files[] = [
                    'file_path' => $filename,
                    'keterangan' => $request->keterangans[$key] ?? '',
                ];
            }
        }
    }

    $abnormal->update([
        'abnormal_title' => $request->abnormal_title,
        'abnormal_date' => $request->abnormal_date,
        'problem_description' => $request->problem_description,
        'root_cause' => $request->root_cause,
        'immediate_actions' => $request->immediate_actions,
        'summary' => $request->summary,
        'actions' => json_encode($actions),
        'risks' => json_encode($risks),
        'files' => json_encode($files),
    ]);

    return redirect()->route('abnormalitas.index')->with('success', 'Abnormalitas updated successfully.');
}

public function show($notificationNumber)
{
    // Ambil data abnormalitas dengan relasi managerUser dan seniorManagerUser
    $abnormal = Abnormal::with(['managerUser', 'seniorManagerUser'])
        ->where('notification_number', $notificationNumber)
        ->firstOrFail();

    // Ambil data notifikasi terkait berdasarkan notification_number
    $notification = Notification::where('notification_number', $notificationNumber)->firstOrFail();


    // Kirim data ke view
    return view('abnormal.view', compact('abnormal', 'notification'));
}

public function downloadPDF($notificationNumber)
{
    $abnormal = Abnormal::where('notification_number', $notificationNumber)->firstOrFail();
    $notification = Notification::where('notification_number', $notificationNumber)->firstOrFail();

        // Decode JSON fields
        $abnormal->actions = json_decode($abnormal->actions, true) ?? [];
        $abnormal->risks = json_decode($abnormal->risks, true) ?? [];
        $abnormal->files = json_decode($abnormal->files, true) ?? [];
    
    // Pastikan folder tanda tangan abnormalitas ada
    $signaturePath = public_path("storage/signatures/abnormalitas/");
    if (!file_exists($signaturePath)) {
        mkdir($signaturePath, 0777, true, true);
    }

    // Daftar tanda tangan yang akan diproses
    $signatures = [
        'manager_signature' => $abnormal->manager_signature,
        'senior_manager_signature' => $abnormal->senior_manager_signature,
    ];

    foreach ($signatures as $key => $signature) {
        if (!empty($signature) && str_starts_with($signature, 'data:image')) {
            // Ambil data Base64 tanpa header
            $imageData = substr($signature, strpos($signature, ',') + 1);
            $imagePath = "{$signaturePath}{$key}_{$notificationNumber}.png";

            // Simpan gambar Base64 ke file
            file_put_contents($imagePath, base64_decode($imageData));

            // Simpan path gambar agar bisa digunakan di Blade
            $abnormal->$key = "storage/signatures/abnormalitas/{$key}_{$notificationNumber}.png";
        } else {
            // Jika tidak dalam format base64, pastikan path yang ada benar
            $existingPath = public_path("storage/signatures/abnormalitas/{$key}_{$notificationNumber}.png");
            if (file_exists($existingPath)) {
                $abnormal->$key = "storage/signatures/abnormalitas/{$key}_{$notificationNumber}.png";
            }
        }
    }

    // Load view untuk PDF
    $pdf = Pdf::loadView('abnormal.pdf', compact('abnormal', 'notification'))
        ->setPaper('a4', 'portrait');

    return $pdf->stream("Abnormalitas_{$notificationNumber}.pdf");
}
}
