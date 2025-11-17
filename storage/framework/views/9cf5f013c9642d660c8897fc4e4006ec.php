<!-- Modal Profile dengan Fitur Edit Lengkap -->
<div id="profileModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 15px; border: none;">
            
            <!-- Header -->
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 15px 15px 0 0;">
                <h5 class="modal-title">
                    <i class="fas fa-user-circle"></i> 
                    <span id="modalTitle">Profil Pengguna</span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <!-- Avatar -->
            <div class="text-center" style="margin-top: -50px;">
                <div id="profileAvatar" style="width: 100px; height: 100px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); margin: 0 auto; display: flex; align-items: center; justify-content: center; font-size: 40px; font-weight: bold; color: white; border: 5px solid white; box-shadow: 0 5px 15px rgba(0,0,0,0.2);">
                    <i class="fas fa-user"></i>
                </div>
            </div>

            <!-- Body -->
            <div class="modal-body" id="profileBody" style="padding: 30px;">
                <!-- Content akan dimuat di sini -->
            </div>

            <!-- Footer -->
            <div class="modal-footer" id="profileFooter" style="border-top: 1px solid #dee2e6; padding: 15px 30px;">
                <!-- Button akan dimuat di sini -->
            </div>

        </div>
    </div>
</div>

<style>
/* Profile Item Styles */
.profile-item {
    padding: 15px;
    margin-bottom: 10px;
    background: #f8f9fa;
    border-radius: 10px;
    transition: all 0.3s ease;
    display: flex;
    align-items: flex-start;
}

.profile-item:hover {
    background: #e9ecef;
    transform: translateX(5px);
}

.profile-icon {
    width: 40px;
    height: 40px;
    min-width: 40px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 18px;
    margin-right: 15px;
}

.profile-info {
    flex: 1;
}

.profile-label {
    font-size: 12px;
    color: #6c757d;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 5px;
}

.profile-value {
    font-size: 16px;
    color: #212529;
    font-weight: 500;
    word-break: break-word;
}

.badge-custom {
    display: inline-block;
    padding: 5px 12px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 600;
}

/* Form Edit Styles */
.form-group {
    margin-bottom: 20px;
}

.form-group label {
    font-weight: 600;
    color: #495057;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.form-group label i {
    color: #667eea;
}

.form-control {
    border: 2px solid #e9ecef;
    border-radius: 8px;
    padding: 10px 15px;
    transition: all 0.3s ease;
}

.form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    outline: none;
}

.btn-gradient {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    color: white;
    padding: 10px 25px;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-gradient:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
    color: white;
}

.btn-secondary {
    padding: 10px 25px;
    border-radius: 8px;
    font-weight: 600;
}

/* Loading Spinner */
.loading-spinner {
    text-align: center;
    padding: 40px;
}

.spinner-border {
    width: 3rem;
    height: 3rem;
    border-width: 4px;
    border-color: #667eea;
    border-right-color: transparent;
}

/* Alert Styles */
.alert {
    border-radius: 8px;
    border: none;
    margin-top: 15px;
}

.alert-danger {
    background-color: #f8d7da;
    color: #721c24;
}

.alert-success {
    background-color: #d4edda;
    color: #155724;
}

.alert ul {
    margin: 0;
    padding-left: 20px;
}

/* Scrollbar */
.modal-body {
    max-height: 60vh;
    overflow-y: auto;
}

.modal-body::-webkit-scrollbar {
    width: 8px;
}

.modal-body::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.modal-body::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 10px;
}
</style>

<script>
let isEditMode = false;
let currentUserData = null;

// Fungsi untuk membuka modal dan load data
function loadProfileData() {
    $('#profileModal').modal('show');
    isEditMode = false;
    
    // Reset content
    $('#profileBody').html(`
        <div class="loading-spinner">
            <div class="spinner-border" role="status">
                <span class="sr-only">Loading...</span>
            </div>
            <p class="mt-3 text-muted">Memuat data profil...</p>
        </div>
    `);
    
    $('#profileFooter').html('');
    $('#modalTitle').text('Profil Pengguna');
    
    // Fetch data dari server
    fetch('<?php echo e(route("profile.data")); ?>', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            currentUserData = data.user;
            displayProfileView(data.user);
        } else {
            showError(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError('Terjadi kesalahan saat memuat data');
    });
}

// Tampilkan profil (mode view)
function displayProfileView(user) {
    // Update avatar
    const initial = user.name ? user.name.charAt(0).toUpperCase() : 'U';
    $('#profileAvatar').html(initial);
    
    const html = `
        <div class="profile-item">
            <div class="profile-icon"><i class="fas fa-user"></i></div>
            <div class="profile-info">
                <div class="profile-label">Nama Lengkap</div>
                <div class="profile-value">${user.name || '-'}</div>
            </div>
        </div>

        <div class="profile-item">
            <div class="profile-icon"><i class="fas fa-user-circle"></i></div>
            <div class="profile-info">
                <div class="profile-label">Username</div>
                <div class="profile-value">${user.username || '-'}</div>
            </div>
        </div>

        <div class="profile-item">
            <div class="profile-icon"><i class="fas fa-envelope"></i></div>
            <div class="profile-info">
                <div class="profile-label">Email</div>
                <div class="profile-value">${user.email || '-'}</div>
            </div>
        </div>

        <div class="profile-item">
            <div class="profile-icon"><i class="fas fa-id-card"></i></div>
            <div class="profile-info">
                <div class="profile-label">NIP</div>
                <div class="profile-value">${user.nip || '-'}</div>
            </div>
        </div>

        <div class="profile-item">
            <div class="profile-icon"><i class="fas fa-briefcase"></i></div>
            <div class="profile-info">
                <div class="profile-label">Jabatan</div>
                <div class="profile-value">${user.jabatan || '-'}</div>
            </div>
        </div>

        <div class="profile-item">
            <div class="profile-icon"><i class="fas fa-venus-mars"></i></div>
            <div class="profile-info">
                <div class="profile-label">Jenis Kelamin</div>
                <div class="profile-value">${user.jenis_kelamin === 'P' ? 'Perempuan' : user.jenis_kelamin === 'L' ? 'Laki-laki' : '-'}</div>
            </div>
        </div>

        <div class="profile-item">
            <div class="profile-icon"><i class="fas fa-calendar"></i></div>
            <div class="profile-info">
                <div class="profile-label">Tanggal Lahir</div>
                <div class="profile-value">${user.tanggal_lahir || '-'}</div>
            </div>
        </div>

        <div class="profile-item">
            <div class="profile-icon"><i class="fas fa-map-marker-alt"></i></div>
            <div class="profile-info">
                <div class="profile-label">Alamat</div>
                <div class="profile-value">${user.alamat || '-'}</div>
            </div>
        </div>

        <div class="profile-item">
            <div class="profile-icon"><i class="fas fa-user-tag"></i></div>
            <div class="profile-info">
                <div class="profile-label">Role</div>
                <div class="profile-value"><span class="badge-custom">${user.role ? user.role.toUpperCase() : '-'}</span></div>
            </div>
        </div>

        <div class="profile-item">
            <div class="profile-icon"><i class="fas fa-clock"></i></div>
            <div class="profile-info">
                <div class="profile-label">Akun Dibuat</div>
                <div class="profile-value">${user.created_at || '-'}</div>
            </div>
        </div>

        <div class="profile-item">
            <div class="profile-icon"><i class="fas fa-sync"></i></div>
            <div class="profile-info">
                <div class="profile-label">Terakhir Diupdate</div>
                <div class="profile-value">${user.updated_at || '-'}</div>
            </div>
        </div>
    `;
    
    $('#profileBody').html(html);
    
    // Tambahkan tombol edit
    $('#profileFooter').html(`
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
        <button type="button" class="btn btn-gradient" onclick="switchToEditMode()">
            <i class="fas fa-edit"></i> Edit Profil
        </button>
    `);
}

// Switch ke mode edit
function switchToEditMode() {
    if (!currentUserData) return;
    
    isEditMode = true;
    $('#modalTitle').text('Edit Profil');
    
    // Format tanggal untuk input date (dari dd-mm-yyyy ke yyyy-mm-dd)
    let birthDate = '';
    if (currentUserData.tanggal_lahir) {
        const parts = currentUserData.tanggal_lahir.split('-');
        if (parts.length === 3) {
            birthDate = `${parts[2]}-${parts[1]}-${parts[0]}`;
        }
    }
    
    const html = `
        <form id="editProfileForm">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> Nama Lengkap *</label>
                        <input type="text" class="form-control" name="name" value="${currentUserData.name || ''}" required>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label><i class="fas fa-user-circle"></i> Username *</label>
                        <input type="text" class="form-control" name="username" value="${currentUserData.username || ''}" required>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label><i class="fas fa-envelope"></i> Email *</label>
                <input type="email" class="form-control" name="email" value="${currentUserData.email || ''}" required>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label><i class="fas fa-id-card"></i> NIP</label>
                        <input type="text" class="form-control" name="nip" value="${currentUserData.nip || ''}">
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label><i class="fas fa-briefcase"></i> Jabatan</label>
                        <input type="text" class="form-control" name="jabatan" value="${currentUserData.jabatan || ''}">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label><i class="fas fa-venus-mars"></i> Jenis Kelamin</label>
                        <select class="form-control" name="jenis_kelamin">
                            <option value="">Pilih</option>
                            <option value="L" ${currentUserData.jenis_kelamin === 'L' ? 'selected' : ''}>Laki-laki</option>
                            <option value="P" ${currentUserData.jenis_kelamin === 'P' ? 'selected' : ''}>Perempuan</option>
                        </select>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label><i class="fas fa-calendar"></i> Tanggal Lahir</label>
                        <input type="date" class="form-control" name="tanggal_lahir" value="${birthDate}">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label><i class="fas fa-map-marker-alt"></i> Alamat</label>
                <textarea class="form-control" name="alamat" rows="3">${currentUserData.alamat || ''}</textarea>
            </div>

            <hr>
            <h6 class="mb-3"><i class="fas fa-lock"></i> Ganti Password (Opsional)</h6>
            
            <div class="form-group">
                <label><i class="fas fa-key"></i> Password Baru</label>
                <input type="password" class="form-control" name="password" placeholder="Kosongkan jika tidak ingin mengganti">
                <small class="form-text text-muted">Minimal 6 karakter</small>
            </div>

            <div class="form-group">
                <label><i class="fas fa-key"></i> Konfirmasi Password</label>
                <input type="password" class="form-control" name="password_confirmation" placeholder="Ulangi password baru">
            </div>

            <div id="formErrors"></div>
        </form>
    `;
    
    $('#profileBody').html(html);
    
    // Ubah footer buttons
    $('#profileFooter').html(`
        <button type="button" class="btn btn-secondary" onclick="cancelEdit()">
            <i class="fas fa-times"></i> Batal
        </button>
        <button type="button" class="btn btn-gradient" onclick="saveProfile()">
            <i class="fas fa-save"></i> Simpan Perubahan
        </button>
    `);
}

// Batalkan edit, kembali ke view
function cancelEdit() {
    displayProfileView(currentUserData);
}

// Simpan perubahan profil
function saveProfile() {
    const form = document.getElementById('editProfileForm');
    const formData = new FormData(form);
    
    // Convert FormData ke JSON
    const data = {};
    formData.forEach((value, key) => {
        data[key] = value;
    });
    
    // Tampilkan loading
    $('#profileFooter').html(`
        <button type="button" class="btn btn-gradient" disabled>
            <span class="spinner-border spinner-border-sm mr-2" role="status"></span>
            Menyimpan...
        </button>
    `);
    
    // Clear previous errors
    $('#formErrors').html('');
    
    // Send update request
    fetch('<?php echo e(route("profile.update")); ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update current user data
            currentUserData = data.user;
            
            // Tampilkan success message
            $('#formErrors').html(`
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> ${data.message}
                </div>
            `);
            
            // Kembali ke view mode setelah 1.5 detik
            setTimeout(() => {
                displayProfileView(currentUserData);
            }, 1500);
            
        } else {
            // Tampilkan error
            let errorHtml = '<div class="alert alert-danger"><ul class="mb-0">';
            
            if (data.errors) {
                // Validation errors
                Object.values(data.errors).forEach(errors => {
                    errors.forEach(error => {
                        errorHtml += `<li>${error}</li>`;
                    });
                });
            } else {
                errorHtml += `<li>${data.message}</li>`;
            }
            
            errorHtml += '</ul></div>';
            $('#formErrors').html(errorHtml);
            
            // Restore buttons
            $('#profileFooter').html(`
                <button type="button" class="btn btn-secondary" onclick="cancelEdit()">
                    <i class="fas fa-times"></i> Batal
                </button>
                <button type="button" class="btn btn-gradient" onclick="saveProfile()">
                    <i class="fas fa-save"></i> Simpan Perubahan
                </button>
            `);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        $('#formErrors').html(`
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> Terjadi kesalahan saat menyimpan data
            </div>
        `);
        
        // Restore buttons
        $('#profileFooter').html(`
            <button type="button" class="btn btn-secondary" onclick="cancelEdit()">
                <i class="fas fa-times"></i> Batal
            </button>
            <button type="button" class="btn btn-gradient" onclick="saveProfile()">
                <i class="fas fa-save"></i> Simpan Perubahan
            </button>
        `);
    });
}

// Show error message
function showError(message) {
    $('#profileBody').html(`
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> ${message}
        </div>
    `);
    
    $('#profileFooter').html(`
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
    `);
}
</script><?php /**PATH C:\xampp\htdocs\project_magang\resources\views/partials/profile-modal.blade.php ENDPATH**/ ?>