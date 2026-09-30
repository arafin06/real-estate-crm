<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Rules\ValidPhoneNumber;
use App\Support\PhoneNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        return response()->json($request->user());
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => [
                'sometimes', 'required', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'phone' => ['nullable', new ValidPhoneNumber],
        ]);

        // Phones are stored as bare digits everywhere else in the app.
        if (array_key_exists('phone', $data)) {
            $data['phone'] = filled($data['phone'])
                ? PhoneNumber::normalize($data['phone'])
                : null;
        }

        $user->update($data);

        return response()->json($user->fresh());
    }

    public function updatePassword(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if (! Hash::check($data['current_password'], $user->password)) {
            return response()->json([
                'message' => 'The current password is incorrect.',
                'errors' => ['current_password' => ['The current password is incorrect.']],
            ], 422);
        }

        $user->update(['password' => $data['new_password']]);

        return response()->json(['message' => 'Password updated.']);
    }

    public function updateAvatar(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,jpg,png,webp|max:2048',
        ]);

        $previous = $user->avatar;

        $path = $request->file('avatar')->store('avatars', 'public');

        $user->update(['avatar' => $path]);

        // Only sweep up the old file once the new one is safely recorded.
        if (filled($previous) && $previous !== $path) {
            Storage::disk('public')->delete($previous);
        }

        return response()->json([
            'avatar' => $path,
            'avatar_url' => asset('storage/'.$path),
            'user' => $user->fresh(),
        ]);
    }
}
