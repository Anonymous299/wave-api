<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UpdateFcmTokenController extends Controller
{
    /**
     * Update FCM Token
     *
     * Updates the Firebase Cloud Messaging (FCM) token for the authenticated user.
     *
     * @authenticated
     *
     * @bodyParam fcm_token string required The Firebase Cloud Messaging token for the user's device. Example: fMEQMOF0xEELbP7icvPD:APA91bHQOcmVEbg...
     *
     * @response 200 {
     *   "message": "FCM token updated successfully"
     * }
     *
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "fcm_token": ["The fcm token field is required."]
     *   }
     * }
     */
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'fcm_token' => 'required|string',
        ]);

        $user = auth()->user();
        $user->update(['fcm_token' => $request->fcm_token]);

        return response()->json(['message' => 'FCM token updated successfully']);
    }
}
