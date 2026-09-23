<?php

namespace App\Http\Controllers;

use App\Models\SkillRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProposalController extends Controller
{
    public function index(Request $request)
    {
        $query = SkillRequest::query()
            ->with('requesterUser:id,name,avatar','requesterUser.profile.skillRecords','requesterUser.profile.achievementRecords')
            ->where('status', 'pending')
            ->whereHas('skill', fn ($skill) => $skill->where('status', 'published'))
            ->where(function ($query) {
                $query->whereNull('created_at')->orWhere('created_at', '>=', now()->subDay());
            });

        if ($request->filled('search')) {
            $query->where('skill_name', 'like', '%' . $request->string('search') . '%');
        }
        if ($request->filled('category')) {
            $query->where('skill_category', $request->string('category'));
        }
        if ($request->filled('credit_min')) {
            $query->where('hour', '>=', (int) $request->input('credit_min'));
        }
        if ($request->filled('credit_max')) {
            $query->where('hour', '<=', (int) $request->input('credit_max'));
        }

        $query->whereHas('requesterUser.profile', function ($profile) use ($request) {
            if ($request->filled('rating_min')) $profile->where('rating', '>=', (float) $request->input('rating_min'));
            if ($request->filled('rating_max')) $profile->where('rating', '<=', (float) $request->input('rating_max'));
            if ($request->filled('reputasi_min')) $profile->where('reputation', '>=', (int) $request->input('reputasi_min'));
            if ($request->filled('reputasi_max')) $profile->where('reputation', '<=', (int) $request->input('reputasi_max'));
        });

        return response()->json([
            'data' => $query->latest()->get(),
        ]);
    }

    public function categories()
    {
        return response()->json([
            'data' => SkillRequest::query()
                ->where('status', 'pending')
                ->where(function ($query) {
                    $query->whereNull('available_at')->orWhere('available_at', '>', now());
                })
                ->latest()
                ->get(),
        ]);
    }

    public function show($id)
    {
        $proposal = SkillRequest::query()
            ->with('requesterUser:id,name,avatar','requesterUser.profile.skillRecords','requesterUser.profile.achievementRecords')
            ->findOrFail($id);

        return response()->json(['data' => $proposal]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'regex:/\A08[0-9]{8,12}\z/'],
            'city' => ['required', 'string', 'max:100'],
            'skill_id' => ['required', 'integer'],
            'available_at' => ['required', 'date', 'after_or_equal:now'],
            'skill_description' => ['required', 'string', 'max:5000'],
            'proposal_file' => ['required', 'file', 'mimes:pdf', 'max:5120'],
        ], [
            'phone.regex' => 'Nomor WhatsApp harus diawali 08 dan berisi 10 sampai 14 angka.',
        ]);

        $skill = $request->user()->profile?->skillRecords()->find($validated['skill_id']);

        if (! $skill) {
            return response()->json([
                'message' => 'Skill yang dipilih tidak terdaftar di profil kamu.',
                'errors' => ['skill_id' => ['Pilih skill yang kamu miliki.']],
            ], 422);
        }

        $proposalPath = $request->file('proposal_file')->store('proposals', 'public');

        $proposal = SkillRequest::create([
            'requester' => $request->user()->id,
            'full_name' => $validated['full_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'city' => $validated['city'],
            'skill_id' => $skill->id,
            'skill_name' => $skill->name,
            'skill_category' => $skill->category_skills,
            'skill_description' => $validated['skill_description'],
            'proposal_path' => $request->getSchemeAndHttpHost() . Storage::url($proposalPath),
            'status' => 'pending',
            'hour' => 1,
            'available_at' => $validated['available_at'],
        ]);

        return response()->json(['data' => $proposal], 201);
    }

    public function mine(Request $request)
    {
        return response()->json([
            'data' => SkillRequest::query()
                ->where('requester', $request->user()->id)
                ->latest()
                ->get(),
        ]);
    }

    public function update(Request $request, int $id)
    {
        $proposal = SkillRequest::query()
            ->where('requester', $request->user()->id)
            ->find($id);

        if (! $proposal) {
            return response()->json(['message' => 'Proposal tidak ditemukan.'], 404);
        }

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'city' => ['required', 'string', 'max:100'],
            'skill_id' => ['sometimes', 'nullable', 'integer'],
            'skill_name' => ['required', 'string', 'max:100'],
            'skill_category' => ['required', 'string', 'max:100'],
            'skill_description' => ['required', 'string', 'max:5000'],
            'proposal_file' => ['sometimes', 'file', 'mimes:pdf', 'max:5120'],
        ]);

        $skill = ! empty($validated['skill_id'])
            ? $request->user()->profile?->skillRecords()->find($validated['skill_id'])
            : null;

        if (! empty($validated['skill_id']) && ! $skill) {
            return response()->json(['message' => 'Skill yang dipilih tidak terdaftar di profil kamu.'], 422);
        }

        $payload = collect($validated)->except('proposal_file')->toArray();
        $payload['skill_id'] = $skill?->id;
        $payload['skill_name'] = $skill?->name ?? $validated['skill_name'];
        $payload['skill_category'] = $skill?->category_skills ?? $validated['skill_category'];

        if ($request->hasFile('proposal_file')) {
            $oldPath = parse_url($proposal->proposal_path, PHP_URL_PATH);
            if ($oldPath) {
                Storage::disk('public')->delete(ltrim(str_replace('/storage/', '', $oldPath), '/'));
            }
            $payload['proposal_path'] = $request->getSchemeAndHttpHost() . Storage::url(
                $request->file('proposal_file')->store('proposals', 'public')
            );
        }

        $proposal->update($payload);

        return response()->json(['data' => $proposal->fresh()]);
    }

    public function destroy(Request $request, int $id)
    {
        $proposal = SkillRequest::query()
            ->where('requester', $request->user()->id)
            ->find($id);

        if (! $proposal) {
            return response()->json(['message' => 'Proposal tidak ditemukan.'], 404);
        }

        $path = parse_url($proposal->proposal_path, PHP_URL_PATH);
        if ($path) {
            Storage::disk('public')->delete(ltrim(str_replace('/storage/', '', $path), '/'));
        }

        $proposal->delete();

        return response()->json(['message' => 'Proposal berhasil dihapus.']);
    }

    public function file(SkillRequest $proposal)
    {
        if (! $proposal->proposal_path) {
            abort(404);
        }

        $path = parse_url($proposal->proposal_path, PHP_URL_PATH);
        $relativePath = str_starts_with($path, '/storage/')
            ? substr($path, strlen('/storage/'))
            : $path;

        abort_unless(Storage::disk('public')->exists($relativePath), 404);

        return response()->download(
            Storage::disk('public')->path($relativePath),
            'proposal-' . $proposal->id . '.pdf',
            ['Content-Type' => 'application/pdf']
        );
    }
}
