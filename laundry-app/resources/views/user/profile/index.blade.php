<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil - LaundryKu</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-100">
<div class="max-w-5xl mx-auto p-6 space-y-8">

    <!-- HEADER -->
    <div class="bg-blue-600 text-white p-8 rounded-2xl shadow-xl">
        <h1 class="text-3xl font-bold mb-2 tracking-wide">Profil Saya</h1>
        <a href="{{ route('user.dashboard') }}" 
           class="text-sm hover:underline inline-flex items-center opacity-90 hover:opacity-100 transition">
            <i class="fas fa-arrow-left mr-2"></i>Kembali ke Dashboard
        </a>
    </div>

    <!-- PROFILE CARD -->
    <div class="bg-white p-8 rounded-2xl shadow-lg border border-gray-100">
        <div class="flex flex-col md:flex-row items-center md:items-start gap-8">
            
            <!-- FOTO -->
            <div class="relative group">
                @php
                    $imageUrl = null;
                    if($user->profile_image) {
                        $imagePath = public_path($user->profile_image);
                        if(file_exists($imagePath)) {
                            $imageUrl = asset($user->profile_image) . '?v=' . time();
                        }
                    }
                @endphp

                @if($imageUrl)
                    <img src="{{ $imageUrl }}" 
                         alt="Profile {{ $user->name }}"
                         class="w-36 h-36 rounded-full object-cover border-4 border-blue-200 shadow-xl transition duration-300 group-hover:scale-105">
                @else
                    <div class="w-36 h-36 rounded-full bg-blue-600 text-white flex items-center justify-center text-5xl font-bold shadow-xl transition duration-300 group-hover:scale-105">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                @endif
            </div>

            <!-- ACTION -->
            <div class="flex-1 space-y-3">
                <form action="{{ route('user.profile.image.update') }}" 
                      method="POST" enctype="multipart/form-data" id="imageForm">
                    @csrf
                    <input type="file" name="profile_image" id="imageInput" 
                           class="hidden" accept="image/*" 
                           onchange="this.form.submit()">

                    <button type="button" 
                        onclick="document.getElementById('imageInput').click()" 
                        class="bg-blue-600 text-white px-6 py-2.5 rounded-xl shadow-md hover:shadow-lg hover:scale-105 transition duration-300">
                        <i class="fas fa-camera mr-2"></i> Ganti Foto
                    </button>
                </form>

                @if($user->profile_image)
                <form action="{{ route('user.profile.image.delete') }}" 
                      method="POST">
                    @csrf @method('DELETE')
                    <button type="submit" 
                        class="text-red-500 text-sm hover:underline"
                        onclick="return confirm('Hapus foto profil?')">
                        Hapus Foto
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>

    <!-- INFORMASI PRIBADI -->
    <div class="bg-white p-8 rounded-2xl shadow-lg border border-gray-100">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold flex items-center">
                <i class="fas fa-user mr-3 text-blue-600"></i>Informasi Pribadi
            </h2>
            <button onclick="toggleEdit()" id="editBtn" 
                class="text-blue-600 hover:underline font-medium">
                Edit Profil
            </button>
        </div>

        <form action="{{ route('user.profile.update') }}" 
              method="POST" id="profileForm">
            @csrf @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-600">Nama Lengkap</label>
                    <input type="text" name="name" value="{{ $user->name }}" 
                        class="w-full border rounded-xl p-3 mt-1 profile-input focus:ring-2 focus:ring-blue-400 outline-none transition"
                        disabled>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-600">Email</label>
                    <input type="email" name="email" value="{{ $user->email }}" 
                        class="w-full border rounded-xl p-3 mt-1 profile-input focus:ring-2 focus:ring-blue-400 outline-none transition"
                        disabled>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-600">Nomor Telepon</label>
                    <input type="text" name="phone" value="{{ $user->phone }}" 
                        class="w-full border rounded-xl p-3 mt-1 profile-input focus:ring-2 focus:ring-blue-400 outline-none transition"
                        disabled>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-600">Alamat</label>
                    <textarea name="address"
                        class="w-full border rounded-xl p-3 mt-1 profile-input focus:ring-2 focus:ring-blue-400 outline-none transition"
                        disabled>{{ $user->address }}</textarea>
                </div>
            </div>

            <div id="saveBtnContainer" class="hidden mt-6">
                <button type="submit" 
                    class="bg-green-600 text-white px-6 py-2.5 rounded-xl shadow-md hover:shadow-lg hover:scale-105 transition duration-300">
                    Simpan Perubahan
                </button>
                <button type="button" onclick="toggleEdit()" 
                    class="ml-4 text-gray-500 hover:underline">
                    Batal
                </button>
            </div>
        </form>
    </div>

    <!-- PASSWORD -->
    <div class="bg-white p-8 rounded-2xl shadow-lg border border-gray-100">
        <h2 class="text-xl font-semibold mb-6 flex items-center">
            <i class="fas fa-lock mr-3 text-blue-600"></i>Ubah Password
        </h2>

        <form action="{{ route('user.profile.password.update') }}" method="POST">
            @csrf @method('PUT')

            <div class="space-y-4">
                <input type="password" name="current_password" 
                    placeholder="Password Lama"
                    class="w-full border rounded-xl p-3 focus:ring-2 focus:ring-blue-400 outline-none transition"
                    required>

                <input type="password" name="new_password" 
                    placeholder="Password Baru"
                    class="w-full border rounded-xl p-3 focus:ring-2 focus:ring-blue-400 outline-none transition"
                    required>

                <input type="password" name="new_password_confirmation" 
                    placeholder="Konfirmasi Password Baru"
                    class="w-full border rounded-xl p-3 focus:ring-2 focus:ring-blue-400 outline-none transition"
                    required>

                <button type="submit" 
                    class="bg-gray-800 text-white px-6 py-2.5 rounded-xl shadow-md hover:shadow-lg hover:scale-105 transition duration-300">
                    Update Password
                </button>
            </div>
        </form>
    </div>

</div>


<script>
    function toggleEdit() {
        const inputs = document.querySelectorAll('.profile-input');
        const saveBtn = document.getElementById('saveBtnContainer');
        const editBtn = document.getElementById('editBtn');
        
        inputs.forEach(input => input.disabled = !input.disabled);
        saveBtn.classList.toggle('hidden');
        editBtn.classList.toggle('hidden');
    }

    @if(session('success'))
        Swal.fire('Berhasil!', "{{ session('success') }}", 'success');
    @endif
    @if($errors->any())
        Swal.fire('Error!', "{{ $errors->first() }}", 'error');
    @endif
</script>
</body>
</html>