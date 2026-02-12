<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{

    public function index(Request $request)
    {
        return view('user.profile.index', [
            'user' => $request->user()
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name'    => 'required|max:255',
            'email'   => 'required|email|unique:users,email,' . $user->id,
            'phone'   => 'nullable|max:15',
            'address' => 'nullable|max:500',
        ]);

        $user->update($validated);

        return back()->with('success', 'Profil berhasil diperbarui');
    }

    public function updateImage(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'profile_image' => 'required|image|mimes:jpeg,jpg,png|max:2048'
        ]);

        // Hapus foto lama jika ada
        if ($user->profile_image && Storage::disk('public')->exists($user->profile_image)) {
            Storage::disk('public')->delete($user->profile_image);
        }

        // Simpan foto baru
        $path = $request->file('profile_image')
                        ->store('profiles', 'public');

        $user->update([
            'profile_image' => $path
        ]);

        return back()->with('success', 'Foto profil berhasil diupload');
    }

    public function deleteImage(Request $request)
    {
        $user = $request->user();

        if ($user->profile_image && Storage::disk('public')->exists($user->profile_image)) {
            Storage::disk('public')->delete($user->profile_image);
        }

        $user->update([
            'profile_image' => null
        ]);

        return back()->with('success', 'Foto profil berhasil dihapus');
    }

    public function updatePassword(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'current_password' => 'required',
            'new_password'     => 'required|min:8|confirmed',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->with('error', 'Password lama salah');
        }

        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return back()->with('success', 'Password berhasil diubah');
    }
}
