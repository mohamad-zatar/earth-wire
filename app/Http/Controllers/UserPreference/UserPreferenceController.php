<?php

namespace App\Http\Controllers\UserPreference;

use App\Http\Controllers\Controller;
use App\Models\UserPreference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * @group User Preferences
 * APIs for managing user preferences.
 *
 * @authenticated
 * These endpoints require a valid Sanctum Bearer token.
 */
class UserPreferenceController extends Controller
{
    /**
     * Set user preferences
     *
     * This endpoint allows an authenticated user to save or update their preferences, such as preferred sources, categories, and authors.
     *
     * @bodyParam sources array Optional. An array of preferred sources. Example: ["New York Times", "The Guardian"]
     * @bodyParam sources.* string Each source in the array. Example: "New York Times"
     * @bodyParam categories array Optional. An array of preferred categories. Example: ["Technology", "Health"]
     * @bodyParam categories.* string Each category in the array. Example: "Technology"
     * @bodyParam authors array Optional. An array of preferred authors. Example: ["John Doe", "Jane Smith"]
     * @bodyParam authors.* string Each author in the array. Example: "John Doe"
     *
     * @response 200 {
     *   "message": "Preferences saved successfully.",
     *   "data": {
     *     "id": 1,
     *     "user_id": 1,
     *     "sources": ["New York Times", "The Guardian"],
     *     "categories": ["Technology", "Health"],
     *     "authors": ["John Doe", "Jane Smith"],
     *     "created_at": "2024-12-27T15:36:51.000000Z",
     *     "updated_at": "2024-12-27T15:37:41.000000Z"
     *   }
     * }
     *
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "sources.0": ["The sources.0 must be a string."],
     *     "categories.0": ["The categories.0 must be a string."],
     *     "authors.0": ["The authors.0 must be a string."]
     *   }
     * }
     */
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

        $cacheKey = "personalized_feed_user_{$request->user()->id}";
        Cache::forget($cacheKey);

        return response()->json(['message' => 'Preferences saved successfully.', 'data' => $preference]);
    }

    /**
     * Get user preferences
     *
     * This endpoint allows an authenticated user to retrieve their saved preferences, such as preferred sources, categories, and authors.
     *
     * @response 200 {
     *   "id": 1,
     *   "user_id": 1,
     *   "sources": ["New York Times", "The Guardian"],
     *   "categories": ["Technology", "Health"],
     *   "authors": ["John Doe", "Jane Smith"],
     *   "created_at": "2024-12-27T15:36:51.000000Z",
     *   "updated_at": "2024-12-27T15:37:41.000000Z"
     * }
     *
     * @response 200 []
     * If no preferences are found, the response is an empty array.
     */

    public function getPreferences(Request $request)
    {
        $preference = UserPreference::where('user_id', $request->user()->id)->first();

        return response()->json($preference ?? []);
    }
}
