<?php

namespace App\Http\Controllers;

use App\Models\SkillRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProposalController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'city' => ['required', 'string', 'max:100'],
            'skill_description' => ['required', 'string', 'max:5000'],
            'proposal_file' => ['required', 'file', 'mimes:pdf', 'max:10240'],
        ]);

        $proposalPath = $request->file('proposal_file')->store('proposals', 'public');

        $proposal = SkillRequest::create([
            'requester' => $request->user()->id,
            'full_name' => $validated['full_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'city' => $validated['city'],
            'skill_description' => $validated['skill_description'],
            'proposal_path' => $request->getSchemeAndHttpHost() . Storage::url($proposalPath),
            'status' => 'pending',
            'hour' => 1,
        ]);

        return response()->json(['data' => $proposal], 201);
    }
}