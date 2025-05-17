<?php

namespace App\Http\Controllers;

use App\Models\User;
use Clickbar\Magellan\Data\Geometries\Point;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChangeUserLocationController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        /** @var User|null $user */
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user->update([
            'location' => Point::makeGeodetic(
                $request->input('latitude'),
                $request->input('longitude')
            ),
        ]);
    }
}
