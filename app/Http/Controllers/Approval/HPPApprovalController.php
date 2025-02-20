<?php

namespace App\Http\Controllers\Approval;

use Barryvdh\DomPDF\Facade\Pdf; 
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Hpp1;
use App\Models\User;
use App\Models\Lpj;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class HPPApprovalController extends Controller
{
    public function index()
    {
        $user = auth()->user();
    
        // Ambil daftar notification_number yang sudah memiliki LPJ & PPL lengkap
        $lpjCompletedNotifications = DB::table('lpjs')
            ->whereNotNull('lpj_number')
            ->whereNotNull('ppl_number')
            ->whereNotNull('lpj_document_path')
            ->whereNotNull('ppl_document_path')
            ->pluck('notification_number')
            ->toArray();

        // Menentukan kondisi unit kerja dan units terkait
        $unitWork = $user->unit_work;
        $relatedUnits = is_array($user->related_units) ? $user->related_units : json_decode($user->related_units, true) ?? [];

        // Ambil dokumen HPP untuk unit terkait
        $hppDocuments = Hpp1::with([
            'managerSignatureRequestingUser',
            'generalManagerRequestingUser',
            'seniorManagerRequestingUser',
            'managerSignatureUser',
            'seniorManagerSignatureUser',
            'generalManagerSignatureUser',
            'directorSignatureUser',
        ])
        ->whereNotIn('notification_number', $lpjCompletedNotifications) // ✅ Filter HPP yang sudah ada LPJ & PPL
        ->where(function ($query) use ($unitWork, $relatedUnits) {
            $query->where('controlling_unit', $unitWork)
                  ->orWhere('requesting_unit', $unitWork)
                  ->orWhere(function ($subQuery) use ($relatedUnits) {
                      foreach ($relatedUnits as $relatedUnit) {
                          $subQuery->orWhere('controlling_unit', $relatedUnit)
                                   ->orWhere('requesting_unit', $relatedUnit);
                      }
                  });
        })
        ->orderByRaw("CASE 
            WHEN manager_signature_user_id IS NULL THEN 0 
            WHEN senior_manager_signature_user_id IS NULL THEN 1 
            WHEN general_manager_signature_user_id IS NULL THEN 2 
            WHEN director_signature_user_id IS NULL THEN 3 
            ELSE 4 
        END")
        ->get();
    
        return view('approval.hpp.index', compact('hppDocuments'));
    }


    public function saveSignature(Request $request, $signType, $notificationNumber)
    {
        try {
            \Log::info('Received signType: ' . $signType);
            \Log::info('Notification Number: ' . $notificationNumber);
    
            $request->validate([
                'tanda_tangan' => 'required',
            ]);
    
            $hpp = Hpp1::where('notification_number', $notificationNumber)->firstOrFail();
    
            $user = auth()->user();
    
            if (!($user->unit_work === $hpp->requesting_unit || 
            $user->unit_work === $hpp->controlling_unit || 
            in_array($hpp->requesting_unit, $user->related_units) || 
            in_array($hpp->controlling_unit, $user->related_units))) {        
          return response()->json(['error' => 'Unauthorized to sign this document'], 403);
        }
      
    
            $sourceForm = $hpp->source_form;
    
            if ($sourceForm === 'createhpp1') {
                $this->handleCreateHPP1($hpp, $signType, $request->tanda_tangan);
            } elseif ($sourceForm === 'createhpp2') {
                $this->handleCreateHPP2($hpp, $signType, $request->tanda_tangan);
            } elseif ($sourceForm === 'createhpp3') {
                $this->handleCreateHPP3($hpp, $signType, $request->tanda_tangan);
            } else {
                throw new \Exception('Invalid source form');
            }
    
            return response()->json(['message' => 'Signature saved successfully!'], 200);
    
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return response()->json(['error' => 'Error saving signature', 'details' => $e->getMessage()], 500);
        }
    }
    
protected function handleCreateHPP1($hpp, $signType, $signature)
{
    if ($signType == 'manager') {
        $hpp->manager_signature = $signature;
        $hpp->manager_signature_user_id = auth()->user()->id;
        $this->notifyNextRole('Senior Manager', 'Unit Of Workshop', $hpp);
    } elseif ($signType == 'senior_manager') {
        $hpp->senior_manager_signature = $signature;
        $hpp->senior_manager_signature_user_id = auth()->user()->id;
        $this->notifyNextRole('Manager', $hpp->requesting_unit, $hpp);
    } elseif ($signType == 'manager_requesting') {
        $hpp->manager_signature_requesting_unit = $signature;
        $hpp->manager_signature_requesting_user_id = auth()->user()->id;
        $this->notifyNextRole('Senior Manager', $hpp->requesting_unit, $hpp);
    } elseif ($signType == 'senior_manager_requesting') {
        $hpp->senior_manager_signature_requesting_unit = $signature;
        $hpp->senior_manager_signature_requesting_user_id = auth()->user()->id;
        $this->notifyNextRole('General Manager', 'Unit Of Workshop', $hpp);
    } elseif ($signType == 'general_manager') {
        $hpp->general_manager_signature = $signature;
        $hpp->general_manager_signature_user_id = auth()->user()->id;
        $this->notifyNextRole('General Manager', $hpp->requesting_unit, $hpp);
    } elseif ($signType == 'general_manager_requesting') {
        $hpp->general_manager_signature_requesting_unit = $signature;
        $hpp->general_manager_signature_requesting_user_id = auth()->user()->id;
        $this->notifyNextRole('Director', null, $hpp);
    } elseif ($signType == 'director') {
        $hpp->director_signature = $signature;
        $hpp->director_signature_user_id = auth()->user()->id;
        \Log::info('All approvals completed for Notification Number: ' . $hpp->notification_number);
    }

    $hpp->save();
}
protected function handleCreateHPP2($hpp, $signType, $signature)
{
    if ($signType == 'manager') {
        $hpp->manager_signature = $signature;
        $hpp->manager_signature_user_id = auth()->user()->id;
        $this->notifyNextRole('Senior Manager', 'Unit Of Workshop', $hpp);
    } elseif ($signType == 'senior_manager') {
        $hpp->senior_manager_signature = $signature;
        $hpp->senior_manager_signature_user_id = auth()->user()->id;
        $this->notifyNextRole('Manager', $hpp->requesting_unit, $hpp);
    } elseif ($signType == 'manager_requesting') {
        $hpp->manager_signature_requesting_unit = $signature;
        $hpp->manager_signature_requesting_user_id = auth()->user()->id;
        $this->notifyNextRole('Senior Manager', $hpp->requesting_unit, $hpp);
    } elseif ($signType == 'senior_manager_requesting') {
        $hpp->senior_manager_signature_requesting_unit = $signature;
        $hpp->senior_manager_signature_requesting_user_id = auth()->user()->id;
        $this->notifyNextRole('General Manager', 'Unit Of Workshop', $hpp);
    } elseif ($signType == 'general_manager') {
        $hpp->general_manager_signature = $signature;
        $hpp->general_manager_signature_user_id = auth()->user()->id;
        $this->notifyNextRole('General Manager', $hpp->requesting_unit, $hpp);
    } elseif ($signType == 'general_manager_requesting') {
        $hpp->general_manager_signature_requesting_unit = $signature;
        $hpp->general_manager_signature_requesting_user_id = auth()->user()->id;
        \Log::info('All approvals completed for Notification Number: ' . $hpp->notification_number);
    }

    $hpp->save();
}
protected function handleCreateHPP3($hpp, $signType, $signature)
{
    if ($signType == 'manager') {
        $hpp->manager_signature = $signature;
        $hpp->manager_signature_user_id = auth()->user()->id;
        $this->notifyNextRole('Senior Manager', 'Unit Of Workshop', $hpp);
    } elseif ($signType == 'senior_manager') {
        $hpp->senior_manager_signature = $signature;
        $hpp->senior_manager_signature_user_id = auth()->user()->id;
        $this->notifyNextRole('General Manager', 'Unit Of Workshop', $hpp);
    } elseif ($signType == 'general_manager') {
        $hpp->general_manager_signature = $signature;
        $hpp->general_manager_signature_user_id = auth()->user()->id;
        \Log::info('All approvals completed for Notification Number: ' . $hpp->notification_number);
    }

    $hpp->save();
}

protected function notifyNextRole($role, $unitWork, $hpp)
{
    $query = User::where('jabatan', $role);

    if ($unitWork) {
        $query->where(function ($q) use ($unitWork) {
            $q->where('unit_work', $unitWork)
              ->orWhereJsonContains('related_units', $unitWork);
        });
    }

    $users = $query->get();

    foreach ($users as $user) {
        $message = "Permintaan Approval Pembuatan HPP :\nNotification Number: {$hpp->notification_number}\nDescription: {$hpp->description}\nPlease log in to review:\nhttps://sectionofworkshop.com/approval/hpp";

        Http::withHeaders([
            'Authorization' => 'KBTe2RszCgc6aWhYapcv'
        ])->post('https://api.fonnte.com/send', [
            'target' => $user->whatsapp_number,
            'message' => $message,
        ]);

        \Log::info("WhatsApp notification sent to {$role}: " . $user->whatsapp_number);
    }
}


    public function reject(Request $request, $signType, $notificationNumber)
    {
        // Validasi alasan penolakan
        $request->validate([
            'reason' => 'required|string',
        ]);

        $hpp = Hpp1::where('notification_number', $notificationNumber)->firstOrFail();

        // Simpan alasan penolakan dan tandai dokumen ditolak
        $hpp->rejection_reason = $request->reason;

        if ($signType === 'manager') {
            $hpp->manager_signature = 'rejected';
        } elseif ($signType === 'senior_manager') {
            $hpp->senior_manager_signature = 'rejected';
        } elseif ($signType === 'general_manager') {
            $hpp->general_manager_signature = 'rejected';
        } elseif ($signType === 'director') {
            $hpp->director_signature = 'rejected';
        } elseif ($signType === 'manager_requesting') {
            $hpp->manager_signature_requesting_unit = 'rejected';
        } elseif ($signType === 'senior_manager_requesting') {
            $hpp->senior_manager_signature_requesting_unit = 'rejected';
        } elseif ($signType === 'general_manager_requesting') {
            $hpp->general_manager_signature_requesting_unit = 'rejected';
        }

        $hpp->save();

        return response()->json(['message' => 'Document rejected successfully!'], 200);
    }
    public function saveNotes(Request $request, $notification_number, $type)
    {
        $hpp = Hpp1::where('notification_number', $notification_number)->firstOrFail();
        
        if ($type == 'controlling') {
            $existingNotes = $hpp->controlling_notes ? json_decode($hpp->controlling_notes, true) : [];
            $newNotes = $request->input('controlling_notes');
    
            // Tambahkan catatan baru dengan informasi user_id
            foreach (array_filter($newNotes) as $note) {
                $existingNotes[] = [
                    'note' => $note,
                    'user_id' => auth()->user()->id, // Simpan ID user yang menambahkan catatan
                ];
            }
    
            $hpp->controlling_notes = json_encode($existingNotes);
        } elseif ($type == 'requesting') {
            $existingNotes = $hpp->requesting_notes ? json_decode($hpp->requesting_notes, true) : [];
            $newNotes = $request->input('requesting_notes');
    
            // Tambahkan catatan baru dengan informasi user_id
            foreach (array_filter($newNotes) as $note) {
                $existingNotes[] = [
                    'note' => $note,
                    'user_id' => auth()->user()->id, // Simpan ID user yang menambahkan catatan
                ];
            }
    
            $hpp->requesting_notes = json_encode($existingNotes);
        }
        
        $hpp->save();
    
        return redirect()->back()->with('success', 'Catatan berhasil disimpan.');
    }

        public function getRelatedUnitsAttribute($value)
    {
        return is_string($value) ? json_decode($value, true) : (is_array($value) ? $value : []);
    }

    public function getOldSignature($signType, $notificationNumber)
    {
        // Logging untuk debug
        \Log::info('Sign Type: ' . $signType);
        \Log::info('Notification Number: ' . $notificationNumber);
    
        // Cari dokumen berdasarkan nomor notifikasi
        $hpp = Hpp1::where('notification_number', $notificationNumber)->first();
    
        if (!$hpp) {
            \Log::error('Dokumen tidak ditemukan: ' . $notificationNumber);
            return response()->json(['message' => 'Dokumen tidak ditemukan'], 404);
        }
    
        $signature = null;
        $userId = null;
    
        // Tentukan kolom tanda tangan dan user_id berdasarkan tipe tanda tangan
        switch ($signType) {
            case 'manager':
                $signature = $hpp->manager_signature;
                $userId = $hpp->manager_signature_user_id;
                break;
            case 'senior_manager':
                $signature = $hpp->senior_manager_signature;
                $userId = $hpp->senior_manager_signature_user_id;
                break;
            case 'general_manager':
                $signature = $hpp->general_manager_signature;
                $userId = $hpp->general_manager_signature_user_id;
                break;
            case 'director':
                $signature = $hpp->director_signature;
                $userId = $hpp->director_signature_user_id;
                break;
            case 'manager_requesting':
                $signature = $hpp->manager_signature_requesting_unit;
                $userId = $hpp->manager_signature_requesting_user_id;
                break;
            case 'senior_manager_requesting':
                $signature = $hpp->senior_manager_signature_requesting_unit;
                $userId = $hpp->senior_manager_signature_requesting_user_id;
                break;
            case 'general_manager_requesting':
                $signature = $hpp->general_manager_signature_requesting_unit;
                $userId = $hpp->general_manager_signature_requesting_user_id;
                break;
            default:
                return response()->json(['message' => 'Tipe tanda tangan tidak valid'], 400);
        }
    
        // Jika tanda tangan ditemukan, kirimkan respon
        if ($signature && $userId) {
            $user = User::find($userId); // Ambil informasi user yang bertanda tangan
            return response()->json([
                'signature' => $signature,
                'user' => $user ? $user->only(['id', 'name', 'jabatan']) : null,
            ], 200);
        } else {
            return response()->json(['message' => 'Tanda tangan lama tidak ditemukan'], 404);
        }
    }
    
    

}
