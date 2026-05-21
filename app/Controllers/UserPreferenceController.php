<?php

namespace App\Controllers;

use App\Models\UserPreference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserPreferenceController extends Controller
{
    public function updateTheme(Request $request)
    {
        $request->validate([
            'theme' => 'required|in:light,dark,system'
        ]);

        $preference = Auth::user()->preference;
        
        if ($preference) {
            $preference->update(['theme' => $request->theme]);
        } else {
            UserPreference::create([
                'user_id' => Auth::id(),
                'theme' => $request->theme,
                'brightness' => 100
            ]);
        }

        // Store in session for non-logged-in users or immediate response
        session(['user_theme' => $request->theme]);

        return response()->json(['success' => true, 'theme' => $request->theme]);
    }

    public function updateBrightness(Request $request)
    {
        $request->validate([
            'brightness' => 'required|integer|min:30|max:150'
        ]);

        $preference = Auth::user()->preference;
        
        if ($preference) {
            $preference->update(['brightness' => $request->brightness]);
        } else {
            UserPreference::create([
                'user_id' => Auth::id(),
                'theme' => 'light',
                'brightness' => $request->brightness
            ]);
        }

        // Store in session for non-logged-in users
        session(['user_brightness' => $request->brightness]);

        return response()->json(['success' => true, 'brightness' => $request->brightness]);
    }

    public function getUserPreferences()
    {
        $user = Auth::user();
        $preferences = [
            'theme' => $user->preference?->theme ?? session('user_theme', 'light'),
            'brightness' => $user->preference?->brightness ?? session('user_brightness', 100)
        ];

        return response()->json($preferences);
    }
}