<?php

namespace App\Http\Controllers\API;

use App\Models\UserTeamProfile;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserTeamProfileController extends Controller
{
    public function create(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'logo' => 'nullable|image|max:2048',
            'country' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('logos', 'public');
        }

        $profile = UserTeamProfile::create($validated);

        return response()->json($profile, 201);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'logo' => 'nullable|image|max:2048',
            'country' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        $profile = UserTeamProfile::findOrFail($id);

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('logos', 'public');
        }

        $profile->update($validated);

        return response()->json($profile);
    }

    public function delete($id)
    {
        $profile = UserTeamProfile::findOrFail($id);
        $profile->delete();

        return response()->json(['message' => 'Profile deleted successfully']);
    }

    public function getOne($id)
    {
        $profile = UserTeamProfile::findOrFail($id);
        return response()->json($profile);
    }

    public function getAll()
    {
        $profiles = UserTeamProfile::all();
        return response()->json($profiles);
    }
}
