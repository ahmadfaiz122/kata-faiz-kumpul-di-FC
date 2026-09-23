<?php

namespace App\Http\Controllers;

use App\Models\SkillRequest;
use App\Models\Transaction;
use App\Models\CreditLedger;
use App\Models\Conversation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $this->completeExpiredTransactions();

        $userId = $request->user()->id;
        $transactions = Transaction::query()
            ->with([
                'barterRequest:id,skill_id,requester,full_name,city,skill_name,skill_category,skill_description,phone,available_at',
                'barterRequest.skill:id,name,category_skills,material_path',
                'providerUser:id,name,avatar',
                'requesterUser:id,name,avatar',
                'requesterSkill',
                'review',
                'reviews:id,transaction_id,reviewer,reviewed,rating,reputation_emoji,reputation,comment',
                'conversation:id,transaction_id',
            ])
            ->where(fn ($query) => $query->where('provider', $userId)->orWhere('requester', $userId))
            ->whereNotExists(fn ($query) => $query->selectRaw('1')->from('transaction_user_hides')->whereColumn('transaction_user_hides.transaction_id', 'transactions.id')->where('transaction_user_hides.user_id', $userId))
            ->latest()
            ->get()
            ->map(fn (Transaction $transaction) => $this->present($transaction, $userId));

        return response()->json(['data' => $transactions->values()]);
    }

    public function ledger(Request $request)
    {
        return response()->json([
            'data' => $request->user()->creditLedger()->with('transaction:id,barter_request')->latest()->paginate(20),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'proposal_id' => ['required', 'integer', 'exists:requests,id'],
            'mode' => ['required', Rule::in(['credit', 'skill'])],
            'requester_skill_id' => ['nullable', 'integer'],
            'requester_skill_note' => ['required_if:mode,skill', 'nullable', 'string', 'max:1000'],
        ]);

        $proposal = SkillRequest::query()->with('skill')->findOrFail($validated['proposal_id']);
        $user = $request->user();

        if ((int) $proposal->requester === (int) $user->id) {
            return response()->json(['message' => 'Kamu tidak dapat mengajukan transaksi pada proposal sendiri.'], 422);
        }

        if (($proposal->available_at && $proposal->available_at->lte(now())) || $proposal->status !== 'pending') {
            return response()->json(['message' => 'Proposal ini sudah tidak tersedia.'], 422);
        }

        $requesterSkill = null;
        if ($validated['mode'] === 'skill') {
            $requesterSkill = $user->profile?->skillRecords()->find($validated['requester_skill_id'] ?? 0);
            if (! $requesterSkill) {
                return response()->json(['message' => 'Pilih skill yang ingin kamu tukarkan.'], 422);
            }
        }

        $alreadyRequested = Transaction::query()
            ->where('barter_request', $proposal->id)
            ->where('requester', $user->id)
            ->whereIn('status', ['pending', 'active'])
            ->exists();

        if ($alreadyRequested) {
            return response()->json(['message' => 'Kamu sudah mengajukan transaksi untuk proposal ini.'], 422);
        }

        $transaction = Transaction::create([
            'barter_request' => $proposal->id,
            'provider' => $proposal->requester,
            'requester' => $user->id,
            'requester_skill_id' => $requesterSkill?->id,
            'requester_skill_note' => $validated['mode'] === 'skill' ? trim((string) ($validated['requester_skill_note'] ?? '')) : null,
            'mode' => $validated['mode'],
            'hour' => 1,
            'credits' => $validated['mode'] === 'credit' ? 1 : 0,
            'status' => 'pending',
            'starts_at' => $proposal->available_at ?? now(),
            'requester_approved_at' => now(),
        ]);

        return response()->json(['data' => $this->present($transaction->load(['barterRequest.skill', 'providerUser', 'requesterUser', 'requesterSkill']), $user->id)], 201);
    }

    public function approve(Request $request, Transaction $transaction)
    {
        if ((int) $transaction->provider !== (int) $request->user()->id) {
            return response()->json(['message' => 'Hanya pemilik skill yang dapat menyetujui transaksi ini.'], 403);
        }

        $transaction = DB::transaction(function () use ($transaction) {
            $transaction = Transaction::query()->lockForUpdate()->findOrFail($transaction->id);
            if ($transaction->status !== 'pending') {
                return $transaction;
            }
            if ($transaction->barterRequest()->whereNotNull('available_at')->where('available_at', '<=', now())->exists()) {
                $transaction->update(['status' => 'expired']);
                abort(response()->json(['message' => 'Proposal ini sudah kedaluwarsa.'], 422));
            }

            if ($transaction->mode === 'credit') {
                $requesterProfile = $transaction->requesterUser()->firstOrFail()->profile()->firstOrCreate([]);
                if ((int) $requesterProfile->credits < (int) $transaction->credits) {
                    abort(response()->json(['message' => 'Credit requester tidak mencukupi untuk menjadwalkan sesi ini.'], 422));
                }
            }

            $startsAt = $transaction->starts_at ?? now();
            $transaction->forceFill([
                'status' => 'scheduled',
                'approved_at' => now(),
                'provider_approved_at' => now(),
                'starts_at' => $startsAt,
                'ends_at' => $startsAt->copy()->addHour(),
            ])->save();

            $transaction->barterRequest()->update(['status' => 'approved']);
            Conversation::firstOrCreate(['transaction_id' => $transaction->id]);
            return $transaction;
        });

        $this->activateScheduledTransaction($transaction);

        return response()->json(['data' => $this->present($transaction->fresh(['barterRequest.skill', 'providerUser', 'requesterUser', 'requesterSkill', 'review']), $request->user()->id)]);
    }

    public function show(Request $request, Transaction $transaction)
    {
        if (! $this->isParticipant($transaction, $request->user()->id)) {
            abort(403);
        }
        $this->completeExpiredTransactions($transaction);
        $transaction->load(['barterRequest.skill', 'providerUser:id,name,avatar', 'requesterUser:id,name,avatar', 'requesterSkill', 'review']);
        return response()->json(['data' => $this->present($transaction, $request->user()->id)]);
    }

    public function reject(Request $request, Transaction $transaction)
    {
        if ((int) $transaction->provider !== (int) $request->user()->id) {
            return response()->json(['message' => 'Hanya provider yang dapat menolak transaksi.'], 403);
        }
        if ($transaction->status !== 'pending') {
            return response()->json(['message' => 'Transaksi ini sudah diproses.'], 422);
        }
        $transaction->update(['status' => 'rejected']);
        return response()->json(['message' => 'Transaksi ditolak.']);
    }

    public function cancel(Request $request, Transaction $transaction)
    {
        if ((int) $transaction->requester !== (int) $request->user()->id) {
            return response()->json(['message' => 'Hanya requester yang dapat membatalkan transaksi.'], 403);
        }
        if ($transaction->status !== 'pending') {
            return response()->json(['message' => 'Transaksi aktif tidak dapat dibatalkan.'], 422);
        }
        $transaction->update(['status' => 'cancelled']);
        return response()->json(['message' => 'Transaksi dibatalkan.']);
    }

    public function hide(Request $request, Transaction $transaction)
    {
        abort_unless($this->isParticipant($transaction, $request->user()->id), 403);

        $transaction->hiddenByUsers()->syncWithoutDetaching([$request->user()->id]);

        return response()->json(['message' => 'Transaksi disembunyikan dari daftar kamu.']);
    }

    public function material(Request $request, Transaction $transaction, int $skill)
    {
        if (! $this->isParticipant($transaction, $request->user()->id) || ! in_array($transaction->status, ['active', 'completed'], true) || $transaction->starts_at?->isFuture()) {
            abort(403);
        }

        $skillModel = $transaction->barterRequest?->skill;
        if ($transaction->mode === 'skill' && (int) $transaction->requester_skill_id === $skill) {
            $skillModel = $transaction->requesterSkill;
        }
        abort_unless($skillModel && (int) $skillModel->id === $skill && $skillModel->material_path, 404);

        $path = parse_url($skillModel->material_path, PHP_URL_PATH);
        $relativePath = str_starts_with($path, '/storage/') ? substr($path, strlen('/storage/')) : $path;
        abort_unless(Storage::disk('public')->exists($relativePath), 404);

        return response()->download(Storage::disk('public')->path($relativePath), $skillModel->name . '.pdf', ['Content-Type' => 'application/pdf']);
    }

    private function present(Transaction $transaction, int $userId): array
    {
        $canAccess = in_array($transaction->status, ['active', 'completed'], true) && $transaction->starts_at?->lte(now());
        $providerPhone = $canAccess ? $transaction->barterRequest?->phone : null;
        $materials = [];
        if ($canAccess) {
            if ($transaction->barterRequest?->skill) {
                $materials[] = [
                    'side' => 'provider',
                    'name' => $transaction->barterRequest->skill->name,
                    'url' => '/api/transactions/' . $transaction->id . '/materials/' . $transaction->barterRequest->skill->id,
                ];
            }
            if ($transaction->mode === 'skill' && $transaction->requesterSkill) {
                $materials[] = [
                    'side' => 'requester',
                    'name' => $transaction->requesterSkill->name,
                    'url' => '/api/transactions/' . $transaction->id . '/materials/' . $transaction->requesterSkill->id,
                ];
            }
        }

        return [
            'id' => $transaction->id,
            'proposal_id' => $transaction->barter_request,
            'mode' => $transaction->mode,
            'status' => $transaction->status,
            'hour' => 1,
            'credits' => (int) $transaction->credits,
            'starts_at' => $transaction->starts_at,
            'ends_at' => $transaction->ends_at,
            'approved_at' => $transaction->approved_at,
            'completed_at' => $transaction->completed_at,
            'is_provider' => (int) $transaction->provider === $userId,
            'provider' => $transaction->provider_user,
            'requester' => $transaction->requester_user,
            'provider_phone' => $providerPhone,
            'whatsapp_url' => $providerPhone ? 'https://wa.me/' . preg_replace('/\D+/', '', $providerPhone) : null,
            'materials' => $materials,
            'requester_skill' => $transaction->requesterSkill,
            'requester_skill_note' => $transaction->requester_skill_note,
            'review' => $transaction->review,
            'reviewed_by_me' => $transaction->reviews->contains('reviewer', $userId),
            'conversation_id' => $transaction->conversation?->id,
            'proposal' => [
                'name' => $transaction->barterRequest?->full_name,
                'city' => $transaction->barterRequest?->city,
                'skill_name' => $transaction->barterRequest?->skill_name ?? $transaction->barterRequest?->skill?->name,
                'skill_category' => $transaction->barterRequest?->skill_category,
                'skill_description' => $transaction->barterRequest?->skill_description,
                'available_at' => $transaction->barterRequest?->available_at,
            ],
        ];
    }

    private function isParticipant(Transaction $transaction, int $userId): bool
    {
        return (int) $transaction->provider === $userId || (int) $transaction->requester === $userId;
    }

    private function completeExpiredTransactions(?Transaction $transaction = null): void
    {
        $query = $transaction
            ? Transaction::query()->whereKey($transaction->id)
            : Transaction::query()->whereIn('status', ['active', 'scheduled']);

        $this->activateScheduledTransaction($transaction);

        $query->whereNotNull('ends_at')->where('ends_at', '<=', now())->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    private function activateScheduledTransaction(?Transaction $transaction = null): void
    {
        $query = $transaction
            ? Transaction::query()->whereKey($transaction->id)
            : Transaction::query()->where('status', 'scheduled');

        $query->whereNotNull('starts_at')->where('starts_at', '<=', now())->each(function (Transaction $scheduled) {
            DB::transaction(function () use ($scheduled) {
                $locked = Transaction::query()->lockForUpdate()->findOrFail($scheduled->id);
                if ($locked->status !== 'scheduled' || ! $locked->starts_at?->lte(now())) {
                    return;
                }

                if ($locked->mode === 'credit') {
                    $requester = $locked->requesterUser()->firstOrFail();
                    $provider = $locked->providerUser()->firstOrFail();
                    $requesterProfile = $requester->profile()->lockForUpdate()->firstOrFail();
                    $providerProfile = $provider->profile()->lockForUpdate()->firstOrFail();
                    if ((int) $requesterProfile->credits < (int) $locked->credits) {
                        $locked->update(['status' => 'issue_reported']);
                        return;
                    }
                    $requesterBalance = (int) $requesterProfile->credits;
                    $providerBalance = (int) $providerProfile->credits;
                    $requesterProfile->decrement('credits', $locked->credits);
                    $providerProfile->increment('credits', $locked->credits);
                    CreditLedger::create(['user_id' => $requester->id, 'transaction_id' => $locked->id, 'amount' => -$locked->credits, 'balance_after' => $requesterBalance - $locked->credits, 'type' => 'spent', 'description' => 'Credit digunakan saat sesi dimulai.']);
                    CreditLedger::create(['user_id' => $provider->id, 'transaction_id' => $locked->id, 'amount' => $locked->credits, 'balance_after' => $providerBalance + $locked->credits, 'type' => 'earned', 'description' => 'Credit diperoleh saat sesi dimulai.']);
                }

                $locked->update(['status' => 'active']);
            });
        });
    }
}
