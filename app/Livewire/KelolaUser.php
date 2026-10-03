<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;
use Livewire\WithPagination;

class KelolaUser extends Component
{
    use WithPagination;

    public string $formMode = '';

    public ?int $editingId = null;

    public string $name = '';

    public string $email = '';

    public string $role = User::ROLE_KARYAWAN;

    public string $password = '';

    public string $password_confirmation = '';

    public string $search = '';

    public string $flash = '';

    public function render()
    {
        $users = User::query()
            ->when($this->search !== '', fn ($q) => $q->where(fn ($sub) => $sub
                ->where('name', 'like', '%'.$this->search.'%')
                ->orWhere('email', 'like', '%'.$this->search.'%')))
            ->orderBy('id')
            ->paginate(20);

        return view('livewire.kelola-user', [
            'users' => $users,
            'totalAdmin' => User::where('role', User::ROLE_ADMIN)->count(),
            'totalKaryawan' => User::where('role', User::ROLE_KARYAWAN)->count(),
        ]);
    }

    public function openTambah(): void
    {
        $this->resetForm();
        $this->formMode = 'tambah';
    }

    public function openEdit(int $id): void
    {
        $this->flash = '';
        $user = User::findOrFail($id);

        $this->editingId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->role;
        $this->password = '';
        $this->password_confirmation = '';
        $this->formMode = 'edit';
    }

    public function closeForm(): void
    {
        $this->resetForm();
    }

    public function simpan()
    {
        $isEdit = $this->editingId !== null;

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'.($isEdit ? ','.$this->editingId : '')],
            'role' => ['required', 'in:admin,karyawan'],
            'password' => [$isEdit ? 'nullable' : 'required', 'confirmed', Password::min(8)],
        ];

        $this->validate($rules);

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
        ];

        if ($this->password !== '') {
            $data['password'] = Hash::make($this->password);
        }

        if ($isEdit) {
            $user = User::findOrFail($this->editingId);

            // Jangan biarkan admin terakhir kehilangan akses.
            if ($user->isAdmin() && $this->role !== User::ROLE_ADMIN && User::where('role', User::ROLE_ADMIN)->count() <= 1) {
                $this->addError('role', 'Minimal harus ada satu admin.');
                return;
            }

            $user->update($data);
            $message = 'Akun '.$user->name.' diperbarui.';
        } else {
            User::create($data);
            $message = 'Akun baru ditambahkan.';
        }

        $this->resetForm();
        $this->flash = $message;
    }

    public function hapus(int $id): void
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            $this->flash = 'Tidak bisa menghapus akun sendiri.';
            return;
        }

        if ($user->isAdmin() && User::where('role', User::ROLE_ADMIN)->count() <= 1) {
            $this->flash = 'Minimal harus ada satu admin.';
            return;
        }

        $nama = $user->name;
        $user->delete();
        $this->flash = 'Akun '.$nama.' dihapus.';
    }

    private function resetForm(): void
    {
        $this->flash = '';
        $this->formMode = '';
        $this->editingId = null;
        $this->name = '';
        $this->email = '';
        $this->role = User::ROLE_KARYAWAN;
        $this->password = '';
        $this->password_confirmation = '';
        $this->resetValidation();
    }
}
