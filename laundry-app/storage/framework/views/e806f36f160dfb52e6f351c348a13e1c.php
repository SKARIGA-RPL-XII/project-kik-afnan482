<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>Profil - LaundryKu</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-gray-100">
<div class="max-w-4xl mx-auto p-6 space-y-6">

    <!-- HEADER -->
    <div class="bg-blue-600 text-white p-6 rounded-lg">
        <h1 class="text-2xl font-bold">Profil Saya</h1>
        <a href="<?php echo e(route('user.dashboard')); ?>" class="text-sm hover:underline">
            <i class="fas fa-arrow-left mr-1"></i>Kembali
        </a>
    </div>

    <!-- FOTO PROFIL -->
    <div class="bg-white p-6 rounded-lg shadow">
        <h2 class="font-semibold mb-4">Foto Profil</h2>

        <div class="flex flex-col md:flex-row items-center gap-6">

            <div>
                <?php if($user->profile_image): ?>
                    <img src="<?php echo e(asset('storage/'.$user->profile_image)); ?>"
                         class="w-32 h-32 rounded-full object-cover border-4 border-blue-200">
                <?php else: ?>
                    <div class="w-32 h-32 rounded-full bg-blue-600 text-white flex items-center justify-center text-4xl font-bold">
                        <?php echo e(strtoupper(substr($user->name,0,1))); ?>

                    </div>
                <?php endif; ?>
            </div>

            <div>
                <!-- Upload -->
                <form action="<?php echo e(route('user.profile.image.update')); ?>"
                      method="POST"
                      enctype="multipart/form-data"
                      id="uploadForm">
                    <?php echo csrf_field(); ?>

                    <input type="file"
                           name="profile_image"
                           id="imageInput"
                           class="hidden"
                           accept="image/png,image/jpeg">

                    <button type="button"
                            onclick="document.getElementById('imageInput').click()"
                            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                        Upload Foto
                    </button>
                </form>

                <!-- Delete -->
                <?php if($user->profile_image): ?>
                <form action="<?php echo e(route('user.profile.image.delete')); ?>"
                      method="POST"
                      id="deleteForm"
                      class="mt-2">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('DELETE'); ?>

                    <button type="button"
                            onclick="confirmDelete()"
                            class="text-red-600 text-sm hover:underline">
                        Hapus Foto
                    </button>
                </form>
                <?php endif; ?>

                <p class="text-xs text-gray-500 mt-2">
                    JPG/PNG Maksimal 2MB
                </p>
            </div>
        </div>
    </div>

    <!-- INFORMASI PROFIL -->
    <div class="bg-white p-6 rounded-lg shadow">
        <div class="flex justify-between mb-4">
            <h2 class="font-semibold">Informasi Pribadi</h2>

            <button type="button"
                    onclick="toggleEdit()"
                    id="editBtn"
                    class="text-blue-600 hover:underline text-sm">
                Edit
            </button>
        </div>

        <form action="<?php echo e(route('user.profile.update')); ?>"
              method="POST"
              id="profileForm">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>

            <div class="space-y-3">
                <input type="text"
                       name="name"
                       value="<?php echo e($user->name); ?>"
                       class="w-full border rounded p-2"
                       disabled required>

                <input type="email"
                       name="email"
                       value="<?php echo e($user->email); ?>"
                       class="w-full border rounded p-2"
                       disabled required>

                <input type="text"
                       name="phone"
                       value="<?php echo e($user->phone); ?>"
                       class="w-full border rounded p-2"
                       disabled>

                <textarea name="address"
                          rows="3"
                          class="w-full border rounded p-2"
                          disabled><?php echo e($user->address); ?></textarea>
            </div>

            <div id="actionBtn" class="hidden mt-4 flex gap-2">
                <button type="submit"
                        class="bg-blue-600 text-white px-4 py-2 rounded">
                    Simpan
                </button>

                <button type="button"
                        onclick="toggleEdit()"
                        class="bg-gray-200 px-4 py-2 rounded">
                    Batal
                </button>
            </div>
        </form>
    </div>

    <!-- UBAH PASSWORD -->
    <div class="bg-white p-6 rounded-lg shadow">
        <h2 class="font-semibold mb-4">Ubah Password</h2>

        <form action="<?php echo e(route('user.profile.password.update')); ?>"
              method="POST">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>

            <div class="space-y-3">
                <input type="password"
                       name="current_password"
                       placeholder="Password Lama"
                       class="w-full border rounded p-2"
                       required>

                <input type="password"
                       name="new_password"
                       placeholder="Password Baru (min 8 karakter)"
                       class="w-full border rounded p-2"
                       required>

                <input type="password"
                       name="new_password_confirmation"
                       placeholder="Konfirmasi Password"
                       class="w-full border rounded p-2"
                       required>
            </div>

            <button type="submit"
                    class="mt-4 bg-blue-600 text-white px-4 py-2 rounded">
                Ubah Password
            </button>
        </form>
    </div>

</div>

<script>
function toggleEdit() {
    const inputs = document.querySelectorAll(
        '#profileForm input:not([type=hidden]), #profileForm textarea'
    );

    const action = document.getElementById('actionBtn');
    const editBtn = document.getElementById('editBtn');

    inputs.forEach(input => {
        input.disabled = !input.disabled;
    });

    action.classList.toggle('hidden');
    editBtn.classList.toggle('hidden');
}
document.getElementById('imageInput').addEventListener('change', function () {
    const file = this.files[0];
    if (!file) return;

    if (file.size > 2048 * 1024) {
        Swal.fire('Error','Maksimal 2MB','error');
        this.value='';
        return;
    }

    Swal.fire({
        title:'Upload foto?',
        showCancelButton:true,
        confirmButtonText:'Upload',
        cancelButtonText:'Batal'
    }).then(result => {
        if(result.isConfirmed){
            document.getElementById('uploadForm').submit();
        }
    });
});

function confirmDelete(){
    Swal.fire({
        title:'Hapus foto?',
        text:'Foto profil akan dihapus',
        icon:'warning',
        showCancelButton:true,
        confirmButtonColor:'#dc2626',
        confirmButtonText:'Ya, Hapus',
        cancelButtonText:'Batal'
    }).then(result => {
        if(result.isConfirmed){
            document.getElementById('deleteForm').submit();
        }
    });
}
</script>

</body>
</html><?php /**PATH C:\laragon\www\laundry-app\resources\views/user/profile/index.blade.php ENDPATH**/ ?>