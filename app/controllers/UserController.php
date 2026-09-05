<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use App\Models\Role;
use App\Services\AuditService;

class UserController extends Controller {
    public function index(): void {
        $this->requireAuth();
        $this->requireRole(['super_admin', 'developer']);

        $userModel = new User();
        $roleModel = new Role();

        $users = $userModel->allWithRole();
        $roles = $roleModel->all('id ASC');

        $this->render('users/index', [
            'title' => 'Manajemen Pengguna & Hak Akses (RBAC)',
            'users' => $users,
            'roles' => $roles
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
}
