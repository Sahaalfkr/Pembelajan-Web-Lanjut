<?= $this->extend('layout') ?>
<?= $this->section('content') ?>

<div class="row">
    <div class="col-lg-8">
        <!-- Profile Card -->
        <div class="card">
            <div class="card-body pt-5">
                <!-- Profil Header -->
                <div class="d-flex flex-column align-items-center text-center">
                    <div class="profile-avatar mb-4">
                        <img src="<?= base_url() ?>NiceAdmin/assets/img/profile-img.jpg" alt="Profile" class="rounded-circle" width="120" height="120" style="object-fit: cover;">
                    </div>
                    <h2><?= htmlspecialchars($username) ?></h2>
                    <p class="text-muted mb-3">
                        <span class="badge bg-primary"><?= ucfirst(htmlspecialchars($role)) ?></span>
                    </p>
                </div>

                <hr class="my-4">

                <!-- Informasi Profil -->
                <div class="profile-info">
                    <h5 class="card-title mb-4">Informasi Pengguna</h5>

                    <div class="row mb-3">
                        <div class="col-sm-3">
                            <h6 class="mb-0">
                                <i class="bi bi-person"></i> Username
                            </h6>
                        </div>
                        <div class="col-sm-9">
                            <p class="text-muted"><?= htmlspecialchars($username) ?></p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-3">
                            <h6 class="mb-0">
                                <i class="bi bi-envelope"></i> Email
                            </h6>
                        </div>
                        <div class="col-sm-9">
                            <p class="text-muted"><?= htmlspecialchars($email) ?></p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-3">
                            <h6 class="mb-0">
                                <i class="bi bi-shield-check"></i> Role
                            </h6>
                        </div>
                        <div class="col-sm-9">
                            <p class="text-muted"><?= ucfirst(htmlspecialchars($role)) ?></p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-3">
                            <h6 class="mb-0">
                                <i class="bi bi-clock-history"></i> Waktu Login
                            </h6>
                        </div>
                        <div class="col-sm-9">
                            <p class="text-muted"><?= date('d-m-Y H:i:s', strtotime($login_time)) ?></p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-3">
                            <h6 class="mb-0">
                                <i class="bi bi-check-circle"></i> Status Login
                            </h6>
                        </div>
                        <div class="col-sm-9">
                            <?php if ($is_logged_in): ?>
                                <span class="badge bg-success">
                                    <i class="bi bi-check-circle"></i> Aktif
                                </span>
                            <?php else: ?>
                                <span class="badge bg-danger">
                                    <i class="bi bi-x-circle"></i> Tidak Aktif
                                </span>
                            <?php endif; ?>
                        </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar Info -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body pt-4">
                <h5 class="card-title mb-4">Aksi</h5>
                <a href="<?= base_url('logout') ?>" class="btn btn-danger w-100 mb-2">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
                <a href="<?= base_url('/') ?>" class="btn btn-secondary w-100">
                    <i class="bi bi-house"></i> Kembali ke Home
                </a>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-body pt-4">
                <h5 class="card-title">Tips Keamanan</h5>
                <ul class="small">
                    <li>Jangan bagikan akun Anda kepada siapapun</li>
                    <li>Gunakan password yang kuat dan unik</li>
                    <li>Logout setelah selesai menggunakan aplikasi</li>
                    <li>Hindari mengakses dari perangkat publik</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
