<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class ProfileController extends Controller
{
    public function index()
    {
        return view('user.profile.index', [
            'user' => Auth::user()
        ]);
    }

    public function update(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|unique:users,email,' . $user->id . '|max:255',
            'phone'   => 'nullable|string|max:15',
            'address' => 'nullable|string|max:500',
        ]);

        $user->update($validated);
        return back()->with('success', 'Profil berhasil diperbarui');
    }

    public function updateImage(Request $request)
    {
        $request->validate([
            'profile_image' => 'required|image|mimes:jpeg,jpg,png,gif|max:2048'
        ], [
            'profile_image.required' => 'Pilih foto terlebih dahulu',
            'profile_image.image' => 'File harus berupa gambar',
            'profile_image.mimes' => 'Format harus jpeg, jpg, png, atau gif',
            'profile_image.max' => 'Ukuran maksimal 2MB'
        ]);

        /** @var User $user */
        $user = Auth::user();

        try {
            // Path ke folder public/uploads/profiles
            $uploadPath = public_path('uploads/profiles');
            
            // Buat folder jika belum ada
            if (!File::exists($uploadPath)) {
                File::makeDirectory($uploadPath, 0775, true);
            }

            // Hapus foto lama jika ada
            if ($user->profile_image) {
                $oldImagePath = public_path($user->profile_image);
                if (File::exists($oldImagePath)) {
                    File::delete($oldImagePath);
                }
            }

            // Generate nama file unik
            $file = $request->file('profile_image');
            $extension = $file->getClientOriginalExtension();
            $fileName = 'profile_' . $user->id . '_' . time() . '.' . $extension;
            
            // Pindahkan file ke public/uploads/profiles
            $file->move($uploadPath, $fileName);
            
            // Path relatif untuk disimpan di database (tanpa 'public/')
            $relativePath = 'uploads/profiles/' . $fileName;

            // Update database
            $user->update(['profile_image' => $relativePath]);

            return back()->with('success', 'Foto profil berhasil diperbarui!');
            
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal upload foto: ' . $e->getMessage());
        }
    }

    public function deleteImage()
    {
        /** @var User $user */
        $user = Auth::user();
        
        if ($user->profile_image) {
            $imagePath = public_path($user->profile_image);
            
            if (File::exists($imagePath)) {
                File::delete($imagePath);
            }
            
            $user->update(['profile_image' => null]);
            
            return back()->with('success', 'Foto profil berhasil dihapus');
        }
        
        return back()->with('info', 'Tidak ada foto profil untuk dihapus');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);

        /** @var User $user */
        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Password lama salah']);
        }

        $user->update(['password' => Hash::make($request->new_password)]);

        return back()->with('success', 'Password berhasil diubah');
    }
}