<?php

namespace App\Http\Controllers;

use App\Models\SkillRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProposalController extends Controller
{
    public function index()
    {
        return response()->json([
            'data' => SkillRequest::query()
                ->with('requesterUser:id,name,avatar')
                ->where('status', 'pending')
                ->latest()
                ->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'city' => ['required', 'string', 'max:100'],
            'skill_id' => ['required', 'integer'],
            'skill_description' => ['required', 'string', 'max:5000'],
            'proposal_file' => ['required', 'file', 'mimes:pdf', 'max:5120'],
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
        ]);

        return response()->json(['data' => $proposal], 201);
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
