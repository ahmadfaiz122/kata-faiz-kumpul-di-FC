<?php

namespace App\Http\Controllers;

use App\Models\SkillRequest;
use App\Models\Transaction;
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
                'barterRequest.skill',
                'providerUser:id,name,avatar',
                'requesterUser:id,name,avatar',
                'requesterSkill',
                'review',
            ])
            ->where(fn ($query) => $query->where('provider', $userId)->orWhere('requester', $userId))
            ->latest()
            ->get()
            ->map(fn (Transaction $transaction) => $this->present($transaction, $userId));

        return response()->json(['data' => $transactions->values()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'proposal_id' => ['required', 'integer', 'exists:requests,id'],
            'mode' => ['required', Rule::in(['credit', 'skill'])],
            'requester_skill_id' => ['nullable', 'integer'],
        ]);

        $proposal = SkillRequest::query()->with('skill')->findOrFail($validated['proposal_id']);
        $user = $request->user();

        if ((int) $proposal->requester === (int) $user->id) {
            return response()->json(['message' => 'Kamu tidak dapat mengajukan transaksi pada proposal sendiri.'], 422);
        }

        if ($proposal->created_at?->lt(now()->subDay()) || $proposal->status !== 'pending') {
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
            'mode' => $validated['mode'],
            'hour' => 1,
            'credits' => $validated['mode'] === 'credit' ? 1 : 0,
            'status' => 'pending',
            'starts_at' => $proposal->available_at ?? now(),
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
            if ($transaction->barterRequest()->where('created_at', '<', now()->subDay())->exists()) {
                $transaction->update(['status' => 'expired']);
                abort(response()->json(['message' => 'Proposal ini sudah kedaluwarsa.'], 422));
            }

            $requesterProfile = $transaction->requesterUser()->firstOrFail()->profile()->firstOrCreate([]);
            $providerProfile = $transaction->providerUser()->firstOrFail()->profile()->firstOrCreate([]);

            if ($transaction->mode === 'credit') {
                if ((int) $requesterProfile->credits < (int) $transaction->credits) {
                    abort(response()->json(['message' => 'Credit requester tidak mencukupi.'], 422));
                }
                $requesterProfile->decrement('credits', $transaction->credits);
                $providerProfile->increment('credits', $transaction->credits);
            }

            $startsAt = $transaction->starts_at ?? now();
            $transaction->forceFill([
                'status' => 'active',
                'approved_at' => now(),
                'starts_at' => $startsAt,
                'ends_at' => $startsAt->copy()->addHour(),
            ])->save();

            $transaction->barterRequest()->update(['status' => 'approved']);
            return $transaction;
        });

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

    public function material(Request $request, Transaction $transaction, int $skill)
    {
        if (! $this->isParticipant($transaction, $request->user()->id) || $transaction->status !== 'active' || $transaction->starts_at?->isFuture()) {
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
        $canAccess = $transaction->status === 'active' && $transaction->starts_at?->lte(now());
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
            'review' => $transaction->review,
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
            : Transaction::query()->where('status', 'active');

        $query->whereNotNull('ends_at')->where('ends_at', '<=', now())->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }
}
