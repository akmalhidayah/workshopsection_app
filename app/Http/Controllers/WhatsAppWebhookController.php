<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Hpp1;

class WhatsAppWebhookController extends Controller
{
    // GET verification (when register webhook) : ?hub.mode=subscribe&hub.challenge=xxx&hub.verify_token=yyy
    public function verify(Request $req)
    {
        $token = env('WA_WEBHOOK_VERIFY_TOKEN');
        if ($req->input('hub_verify_token') === $token || $req->input('hub.verify_token') === $token) {
            return response($req->input('hub.challenge') ?? $req->input('hub.challenge'));
        }
        return response('Invalid verify token', 403);
    }

    public function receive(Request $req)
    {
        // optional: verify signature X-Hub-Signature-256
        Log::info('[WA Webhook] payload', ['payload'=>$req->all()]);

        // proses message_status updates
        $entry = $req->input('entry.0.changes.0.value', []);
        $statuses = data_get($entry, 'statuses', []);
        if (!empty($statuses)) {
            foreach ($statuses as $s) {
                $msgId = $s['id'] ?? null;
                $status = $s['status'] ?? null; // e.g. delivered, read, failed
                $recipient = $s['recipient_id'] ?? null;
                // map message id -> Hpp1 (jika Anda menyimpan wa_message_id)
                if ($msgId) {
                    try {
                        Hpp1::where('wa_message_id', $msgId)
                            ->update([
                                'wa_status' => $status,
                                'wa_status_updated_at' => now(),
                            ]);
                    } catch (\Throwable $e) {
                        Log::error('[WA Webhook] gagal update Hpp1 status: '.$e->getMessage());
                    }
                }
            }
        }

        return response('ok', 200);
    }
}
