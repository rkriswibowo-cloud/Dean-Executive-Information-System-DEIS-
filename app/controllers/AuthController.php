<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use App\Helpers\AuthHelper;
use App\Helpers\CsrfHelper;
use App\Services\AuditService;

class AuthController extends Controller {
    public function login(): void {
        if (AuthHelper::check()) {
            $this->redirect('dashboard');
        }

        if ($this->isPost()) {
            $this->requireCsrf();
            $username = trim($this->getPost('username', ''));
            $password = $this->getPost('password', '');

            $userModel = new User();
            $user = $userModel->findByUsername($username);

            if ($user && password_verify($password, $user['password'])) {
                if ($user['status'] !== 'active') {
                    $this->render('auth/login', [
                        'error' => 'Akun Anda sedang dinonaktifkan. Hubungi Administrator.',
                        'oldUsername' => $username
                    ], 'layouts/auth');
                    return;
                }

                AuthHelper::login($user);
                AuditService::log('LOGIN', 'auth', (string)$user['id'], null, ['username' => $username, 'status' => 'success']);
                $this->redirect('dashboard', ['success' => 'Selamat datang kembali, ' . $user['name']]);
            } else {
                AuditService::log('LOGIN_FAILED', 'auth', null, null, ['username' => $username]);
                $this->render('auth/login', [
                    'error' => 'Username atau password yang Anda masukkan salah.',
                    'oldUsername' => $username
                ], 'layouts/auth');
                return;
            }
        }

        $this->render('auth/login', [], 'layouts/auth');
    }

    public function logout(): void {
        if (AuthHelper::check()) {
            $user = AuthHelper::user();
            AuditService::log('LOGOUT', 'auth', (string)AuthHelper::id(), null, ['username' => $user['username'] ?? '']);
            AuthHelper::logout();
        }
        $this->redirect('login', ['info' => 'Anda telah berhasil logout.']);
    }

    public function switchContext(): void {
        $this->requireAuth();
        $this->requireCsrf();

        $roleSlug = $this->getPost('role_slug', '');
        AuthHelper::switchContext($roleSlug);
        AuditService::log('SWITCH_CONTEXT', 'auth', (string)AuthHelper::id(), null, ['active_role' => $roleSlug]);

        $this->redirect('dashboard', ['info' => 'Konteks peran beralih ke: ' . strtoupper($roleSlug)]);
    }

    public function impersonate(): void {
        $this->requireAuth();
        $this->requireCsrf();

        if (!AuthHelper::isSuperAdmin()) {
            $this->redirect('dashboard', ['error' => 'Akses ditolak: Hanya Super Admin yang memiliki wewenang untuk impersonasi akun.']);
        }

        $targetUserId = (int)$this->getPost('user_id', 0);
        if (!$targetUserId) {
            $this->redirect('users', ['error' => 'Pengguna tujuan impersonasi tidak valid.']);
        }

        $prevUser = AuthHelper::user();
        if (AuthHelper::impersonate($targetUserId)) {
            $newUser = AuthHelper::user();
            AuditService::log('IMPERSONATE_START', 'auth', (string)$targetUserId, null, [
                'impersonator_id'   => $prevUser['id'] ?? null,
                'impersonator_name' => $prevUser['name'] ?? null,
                'target_user'       => $newUser['username'] ?? null,
                'target_role'       => $newUser['role_slug'] ?? null,
            ]);
            $this->redirect('dashboard', ['info' => 'Mode Impersonasi aktif: Anda sedang mengakses sistem sebagai ' . $newUser['name'] . ' (' . strtoupper($newUser['role_name'] ?? $newUser['role_slug']) . ').']);
        } else {
            $this->redirect('users', ['error' => 'Gagal melakukan impersonasi. Akun pengguna tidak ditemukan atau tidak aktif.']);
        }
    }

    public function leaveImpersonation(): void {
        $this->requireAuth();

        if (!AuthHelper::isImpersonating()) {
            $this->redirect('dashboard');
        }

        $impersonatedUser = AuthHelper::user();
        $impersonator = AuthHelper::getImpersonator();

        if (AuthHelper::leaveImpersonation()) {
            AuditService::log('IMPERSONATE_END', 'auth', (string)($impersonator['id'] ?? AuthHelper::id()), null, [
                'left_user'   => $impersonatedUser['username'] ?? null,
                'super_admin' => $impersonator['name'] ?? null
            ]);
            $this->redirect('users', ['success' => 'Berhasil keluar dari mode penyamaran. Anda telah kembali ke akun Super Admin.']);
        } else {
            $this->redirect('dashboard', ['error' => 'Gagal kembali ke sesi Super Admin.']);
        }
    }

    public function profile(): void {
        $this->requireAuth();
        $userModel = new User();
        $user = $userModel->find(AuthHelper::id());

        if ($this->isPost()) {
            $this->requireCsrf();
            $name = trim($this->getPost('name', ''));
            $email = trim($this->getPost('email', ''));
            $phone = trim($this->getPost('phone', ''));
            $newPassword = $this->getPost('new_password', '');

            $updateData = [
                'name'  => $name,
                'email' => $email,
                'phone' => $phone
            ];

            if (!empty($newPassword)) {
                $updateData['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
            }

            $userId = AuthHelper::id();
            $userModel->update($userId, $updateData);

            // Sync with related tables if user is Dekan, Kaprodi, or Lecturer
            $db = $userModel->getDb();
            $userRole = AuthHelper::role();
            $currentUserData = AuthHelper::user();

            // 1. Sync lecturers table if record exists for this user
            $stmtLec = $db->prepare("UPDATE lecturers SET name = :name, email = :email, phone = :phone WHERE user_id = :uid OR (nidn IS NOT NULL AND nidn = :nidn)");
            $stmtLec->execute([
                'name'  => $name,
                'email' => $email,
                'phone' => $phone,
                'uid'   => $userId,
                'nidn'  => $currentUserData['nidn'] ?? ''
            ]);

            // 2. If user is Dekan, sync faculty dean_name and app_settings
            if ($userRole === 'dekan' || $currentUserData['role_slug'] === 'dekan') {
                $db->prepare("UPDATE faculties SET dean_name = :name WHERE id = 1")->execute(['name' => $name]);
                $db->prepare("UPDATE app_settings SET setting_value = :name WHERE setting_key = 'dean_name'")->execute(['name' => $name]);
            }

            // 3. If user is Kaprodi, sync study_programs head_name
            if ($userRole === 'kaprodi' || $currentUserData['role_slug'] === 'kaprodi') {
                $db->prepare("UPDATE study_programs SET head_name = :name WHERE head_user_id = :uid")->execute(['name' => $name, 'uid' => $userId]);
            }

            AuditService::log('UPDATE', 'profile', (string)$userId, null, ['name' => $name, 'email' => $email]);

            // Refresh session user data
            unset($_SESSION['user_data']);

            $this->redirect('profile', ['success' => 'Profil berhasil diperbarui dan disinkronkan ke seluruh modul.']);
        }

        $this->render('auth/profile', [
            'user' => $user,
            'title' => 'Profil Pengguna'
        ]);
    }
}
