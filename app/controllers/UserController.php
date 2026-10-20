<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use App\Models\Role;
use App\Services\AuditService;
use App\Services\Obe\UserAccountRepository as ObeUserAccountRepository;

class UserController extends Controller {
    public function index(): void {
        $this->requireAuth();
        $this->requireRole(['super_admin', 'developer']);

        $userModel = new User();
        $roleModel = new Role();

        $users = $userModel->allWithRole();
        $roles = $roleModel->all('id ASC');

        $obeUsers = [];
        $obeError = null;
        try {
            $obeUsers = (new ObeUserAccountRepository())->all();
            $localIndex = ['nidn' => [], 'username' => [], 'email' => []];
            foreach ($users as $localUser) {
                foreach (array_keys($localIndex) as $field) {
                    $key = mb_strtolower(trim((string)($localUser[$field] ?? '')), 'UTF-8');
                    if ($key !== '' && $key !== '-') $localIndex[$field][$key] = $localUser;
                }
            }
            foreach ($obeUsers as &$obeUser) {
                $match = null;
                foreach (['nidn', 'username', 'email'] as $field) {
                    $key = mb_strtolower(trim((string)($obeUser[$field] ?? '')), 'UTF-8');
                    if ($key !== '' && isset($localIndex[$field][$key])) {
                        $match = $localIndex[$field][$key];
                        break;
                    }
                }
                $obeUser['deis_user_id'] = $match ? (int)$match['id'] : null;
                $obeUser['deis_user_name'] = $match['name'] ?? null;
            }
            unset($obeUser);
        } catch (\Throwable $e) {
            $obeError = $e->getMessage();
        }

        $this->render('users/index', [
            'title' => 'Manajemen Pengguna & Hak Akses (RBAC)',
            'users' => $users,
            'roles' => $roles,
            'obeUsers' => $obeUsers,
            'obeError' => $obeError,
        ]);
    }

    public function create(): void {
        $this->requireAuth();
        $this->requireRole(['super_admin', 'developer']);
        $this->requireCsrf();

        $name = trim($this->getPost('name', ''));
        $username = trim($this->getPost('username', ''));
        $email = trim($this->getPost('email', ''));
        $password = $this->getPost('password', '');
        $roleId = (int)$this->getPost('role_id', 4);
        $phone = trim($this->getPost('phone', ''));

        $userModel = new User();
        if ($userModel->findByUsername($username)) {
            $this->redirect('users', ['error' => 'Username sudah digunakan. Pilih username lain.']);
        }

        $userId = $userModel->create([
            'name'     => $name,
            'username' => $username,
            'email'    => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'role_id'  => $roleId,
            'phone'    => $phone,
            'status'   => 'active'
        ]);

        AuditService::log('CREATE_USER', 'users', (string)$userId, null, ['username' => $username, 'role_id' => $roleId]);
        $this->redirect('users', ['success' => 'Pengguna baru berhasil didaftarkan.']);
    }

    public function toggleStatus(): void {
        $this->requireAuth();
        $this->requireRole(['super_admin', 'developer']);
        $this->requireCsrf();

        $userId = (int)$this->getPost('user_id', 0);
        $status = $this->getPost('status', 'active');

        if ($userId) {
            $userModel = new User();
            $userModel->update($userId, ['status' => $status]);
            AuditService::log('UPDATE_STATUS', 'users', (string)$userId, null, ['status' => $status]);
        }

        $this->redirect('users', ['success' => 'Status akun pengguna berhasil diperbarui.']);
    }

    public function delete(): void {
        $this->requireAuth();
        $this->requireRole(['super_admin', 'developer']);
        $this->requireCsrf();

        $userId = (int)$this->getPost('user_id', 0);
        if ($userId <= 0) {
            $this->redirect('users', ['error' => 'Akun yang akan dihapus tidak valid.']);
        }
        if ($userId === (int)\App\Helpers\AuthHelper::id()) {
            $this->redirect('users', ['error' => 'Anda tidak dapat menghapus akun yang sedang digunakan.']);
        }

        $userModel = new User();
        $target = $userModel->findWithRole($userId);
        if (!$target) {
            $this->redirect('users', ['error' => 'Akun DEIS tidak ditemukan.']);
        }
        if (($target['role_slug'] ?? '') === 'super_admin' && $userModel->countByRoleSlug('super_admin') <= 1) {
            $this->redirect('users', ['error' => 'Superadmin terakhir tidak dapat dihapus.']);
        }

        try {
            $userModel->delete($userId);
            $auditTarget = $target;
            unset($auditTarget['password']);
            AuditService::log('DELETE_USER', 'users', (string)$userId, $auditTarget, null);
            $this->redirect('users', ['success' => 'Akun DEIS ' . $target['username'] . ' berhasil dihapus.']);
        } catch (\Throwable $e) {
            $this->redirect('users', ['error' => 'Akun tidak dapat dihapus karena masih digunakan oleh data lain. Nonaktifkan akun sebagai alternatif.']);
        }
    }
}
