<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConversationController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;
        Transaction::query()
            ->whereIn('status', ['scheduled', 'active', 'completed'])
            ->where(fn ($query) => $query->where('provider', $userId)->orWhere('requester', $userId))
            ->where(function ($query) {
                $query->where(function ($scheduled) {
                    $scheduled->where('status', 'scheduled')->whereNotNull('starts_at')->where('starts_at', '<=', now());
                })->orWhere(function ($active) {
                    $active->where('status', 'active')->whereNotNull('ends_at')->where('ends_at', '<=', now());
                });
            })
            ->get()
            ->each(function (Transaction $transaction) {
                if ($transaction->status === 'scheduled') {
                    $this->activateIfDue($transaction);
                } else {
                    $transaction->update(['status' => 'completed', 'completed_at' => now()]);
                }
            });

        Transaction::query()
            ->whereIn('status', ['scheduled', 'active', 'completed'])
            ->where(fn ($query) => $query->where('provider', $userId)->orWhere('requester', $userId))
            ->pluck('id')
            ->each(fn (int $transactionId) => Conversation::firstOrCreate(['transaction_id' => $transactionId]));

        $conversations = Conversation::query()
            ->with(['transaction.barterRequest.skill', 'transaction.providerUser:id,name,avatar', 'transaction.requesterUser:id,name,avatar'])
            ->whereHas('transaction', fn ($query) => $query->where('provider', $userId)->orWhere('requester', $userId))
            ->latest('updated_at')
            ->get()
            ->map(fn (Conversation $conversation) => $this->presentConversation($conversation, $userId));

        return response()->json(['data' => $conversations->values()]);
    }

    public function show(Request $request, Conversation $conversation)
    {
        $transaction = $conversation->transaction;
        if ($transaction->status === 'active' && $transaction->ends_at?->lte(now())) {
            $transaction->update(['status' => 'completed', 'completed_at' => now()]);
            $transaction->refresh();
        }
        $this->authorizeParticipant($transaction, $request->user()->id);
        $this->activateIfDue($transaction);

        $after = $request->integer('after_id', 0);
        $messages = $conversation->messages()
            ->with('sender:id,name,avatar')
            ->when($after > 0, fn ($query) => $query->where('id', '>', $after))
            ->oldest('id')
            ->get()
            ->map(fn (Message $message) => $this->presentMessage($message, $request->user()->id));

        $conversation->messages()
            ->where('sender_id', '<>', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'data' => $messages->values(),
            'meta' => $this->chatMeta($transaction, $request->user()->id),
        ]);
    }

    public function store(Request $request, Conversation $conversation)
    {
        $transaction = $conversation->transaction;
        $this->authorizeParticipant($transaction, $request->user()->id);
        $this->activateIfDue($transaction);
        $transaction->refresh();

        if ($transaction->status !== 'active' || ! $transaction->starts_at?->lte(now()) || ! $transaction->ends_at?->isFuture()) {
            return response()->json(['message' => 'Chat hanya tersedia selama sesi transaksi berlangsung.'], 422);
        }

        $validated = $request->validate([
            'body' => ['required', 'string', 'min:1', 'max:2000'],
        ]);

        $message = $conversation->messages()->create([
            'sender_id' => $request->user()->id,
            'body' => trim($validated['body']),
        ])->load('sender:id,name,avatar');
        $conversation->touch();

        return response()->json(['data' => $this->presentMessage($message, $request->user()->id)], 201);
    }

    private function activateIfDue(Transaction $transaction): void
    {
        if ($transaction->status !== 'scheduled' || ! $transaction->starts_at?->lte(now())) {
            return;
        }

        DB::transaction(function () use ($transaction) {
            $locked = Transaction::query()->lockForUpdate()->findOrFail($transaction->id);
            if ($locked->status !== 'scheduled' || ! $locked->starts_at?->lte(now())) {
                return;
            }

            if ($locked->mode === 'credit') {
                $requester = $locked->requesterUser()->firstOrFail();
                $provider = $locked->providerUser()->firstOrFail();
                $requesterProfile = $requester->profile()->lockForUpdate()->firstOrCreate([]);
                $providerProfile = $provider->profile()->lockForUpdate()->firstOrCreate([]);
                if ((int) $requesterProfile->credits < (int) $locked->credits) {
                    $locked->update(['status' => 'issue_reported']);
                    return;
                }
                $requesterBalance = (int) $requesterProfile->credits;
                $providerBalance = (int) $providerProfile->credits;
                $requesterProfile->decrement('credits', $locked->credits);
                $providerProfile->increment('credits', $locked->credits);
                $requester->creditLedger()->create(['transaction_id' => $locked->id, 'amount' => -$locked->credits, 'balance_after' => $requesterBalance - $locked->credits, 'type' => 'spent', 'description' => 'Credit digunakan saat sesi dimulai.']);
                $provider->creditLedger()->create(['transaction_id' => $locked->id, 'amount' => $locked->credits, 'balance_after' => $providerBalance + $locked->credits, 'type' => 'earned', 'description' => 'Credit diperoleh saat sesi dimulai.']);
            }

            $locked->update(['status' => 'active', 'ends_at' => $locked->starts_at->copy()->addHour()]);
        });
    }

    private function authorizeParticipant(Transaction $transaction, int $userId): void
    {
        abort_unless((int) $transaction->provider === $userId || (int) $transaction->requester === $userId, 403);
    }

    private function chatMeta(Transaction $transaction, int $userId): array
    {
        $now = now();
        return [
            'transaction_id' => $transaction->id,
            'status' => $transaction->status,
            'can_send' => $transaction->status === 'active' && $transaction->starts_at?->lte($now) && $transaction->ends_at?->gt($now),
            'read_only' => $transaction->status === 'completed',
            'is_archived' => $transaction->status === 'completed',
            'can_review' => $transaction->status === 'completed' && ! $transaction->reviews()->where('reviewer', $userId)->exists(),
            'reviewed_user_id' => (int) $transaction->provider === $userId ? $transaction->requester : $transaction->provider,
            'starts_at' => $transaction->starts_at,
            'ends_at' => $transaction->ends_at,
        ];
    }

    private function presentConversation(Conversation $conversation, int $userId): array
    {
        $transaction = $conversation->transaction;
        $other = (int) $transaction->provider === $userId ? $transaction->requesterUser : $transaction->providerUser;
        return [
            'id' => $conversation->id,
            'transaction_id' => $transaction->id,
            'skill' => $transaction->barterRequest?->skill?->name,
            'status' => $transaction->status,
            'other_user' => $other,
            'unread_count' => $conversation->messages()->where('sender_id', '<>', $userId)->whereNull('read_at')->count(),
            'meta' => $this->chatMeta($transaction, $userId),
        ];
    }

    private function presentMessage(Message $message, int $userId): array
    {
        return [
            'id' => $message->id,
            'body' => $message->body,
            'sender' => $message->sender,
            'is_mine' => (int) $message->sender_id === $userId,
            'read_at' => $message->read_at,
            'created_at' => $message->created_at,
        ];
    }
}
