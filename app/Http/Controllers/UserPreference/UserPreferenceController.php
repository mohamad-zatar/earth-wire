<?php

namespace App\Http\Controllers\UserPreference;

use App\Http\Controllers\Controller;
use App\Models\UserPreference;
use Illuminate\Http\Request;

class UserPreferenceController extends Controller
{
    public function setPreferences(Request $request)
    {
        $validated = $request->validate([
            'sources' => 'array',
            'sources.*' => ['string', 'filled'],

            'categories' => 'array',
            'categories.*' => ['string', 'filled'],

            'authors' => 'array',
            'authors.*' => ['string', 'filled'],
        ]);

        $preference = UserPreference::updateOrCreate(
            ['user_id' => $request->user()->id],
            $validated
        );

        return response()->json(['message' => 'Preferences saved successfully.', 'data' => $preference]);
    }

    public function getPreferences(Request $request)
    {
        $preference = UserPreference::where('user_id', $request->user()->id)->first();

        return response()->json($preference ?? []);
    }
}
