<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Tampilkan daftar seluruh pengguna dan manajemen akun.
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $selectedRole = $request->query('role');

        // Ringkasan metrik statistik akun
        $metrics = [
            'total' => User::count(),
            'super_admin' => User::role('super-admin')->count(),
            'admin' => User::role('admin')->count(),
            'verifikator' => User::role('verifikator')->count(),
            'petugas_lapangan' => User::role('petugas-lapangan')->count(),
            'viewer' => User::role('viewer')->count(),
        ];

        // Query pengguna dengan eager loading relasi peran
        $users = User::with('roles')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($selectedRole, function ($query, $role) {
                $query->role($role);
            })
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        // Daftar peran resmi sistem
        $roles = Role::whereIn('name', [
            'super-admin',
            'admin',
            'verifikator',
            'petugas-lapangan',
            'viewer',
        ])->get();

        // Definisi label & atribut visual peran
        $roleDefinitions = [
            'super-admin' => [
                'name' => 'Super Admin',
                'description' => 'Akses penuh ke semua fitur dan konfigurasi sistem',
                'badge_class' => 'bg-purple-50 text-purple-700 border-purple-200',
                'badge_dot' => 'bg-purple-500',
                'icon' => '👑',
            ],
            'admin' => [
                'name' => 'Administrator Data',
                'description' => 'Kelola Master Data, Statistik, Laporan, & GIS',
                'badge_class' => 'bg-blue-50 text-blue-700 border-blue-200',
                'badge_dot' => 'bg-blue-500',
                'icon' => '🛡️',
            ],
            'verifikator' => [
                'name' => 'Verifikator',
                'description' => 'Validasi dan verifikasi data pendaratan/tangkapan',
                'badge_class' => 'bg-amber-50 text-amber-700 border-amber-200',
                'badge_dot' => 'bg-amber-500',
                'icon' => '🔍',
            ],
            'petugas-lapangan' => [
                'name' => 'Petugas Lapangan',
                'description' => 'Input data Trip, Logbook, Tangkapan, dan Nelayan',
                'badge_class' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                'badge_dot' => 'bg-emerald-500',
                'icon' => '📋',
            ],
            'viewer' => [
                'name' => 'Viewer',
                'description' => 'Melihat ringkasan dashboard, peta GIS, dan laporan',
                'badge_class' => 'bg-slate-100 text-slate-700 border-slate-200',
                'badge_dot' => 'bg-slate-400',
                'icon' => '👁️',
            ],
        ];

        return view('admin.users.index', compact('users', 'metrics', 'roles', 'roleDefinitions'));
    }

    /**
     * Tambahkan akun pengguna baru beserta penetapan peran.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'string', 'exists:roles,name'],
        ], [
            'name.required' => 'Nama pengguna wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
            'email.unique' => 'Alamat email sudah digunakan oleh pengguna lain.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'role.required' => 'Peran (role) pengguna wajib dipilih.',
            'role.exists' => 'Peran yang dipilih tidak terdaftar di sistem.',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'email_verified_at' => now(),
        ]);

        $user->assignRole($validated['role']);

        return redirect()->route('admin.users.index')
            ->with('success', "Akun pengguna '{$user->name}' berhasil dibuat dengan peran '{$validated['role']}'.");
    }

    /**
     * Perbarui data akun dan peran pengguna.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'string', 'exists:roles,name'],
        ], [
            'name.required' => 'Nama pengguna wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
            'email.unique' => 'Alamat email sudah digunakan oleh pengguna lain.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'role.required' => 'Peran (role) pengguna wajib dipilih.',
            'role.exists' => 'Peran yang dipilih tidak terdaftar di sistem.',
        ]);

        // Cek jika akun ini adalah satu-satunya Super Admin dan mencoba diturunkan rolenya
        if ($user->hasRole('super-admin') && $validated['role'] !== 'super-admin') {
            $superAdminCount = User::role('super-admin')->count();
            if ($superAdminCount <= 1) {
                return back()->withInput()->with('error', 'Tidak dapat mengubah peran satu-satunya Super Admin yang tersisa.');
            }
        }

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
        ];

        if (! empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);
        $user->syncRoles([$validated['role']]);

        return redirect()->route('admin.users.index')
            ->with('success', "Data akun pengguna '{$user->name}' berhasil diperbarui.");
    }

    /**
     * Hapus akun pengguna dari sistem.
     */
    public function destroy(User $user): RedirectResponse
    {
        // Pengecekan 1: Mencegah menghapus akun sendiri yang sedang login
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri yang sedang aktif digunakan.');
        }

        // Pengecekan 2: Mencegah menghapus satu-satunya Super Admin
        if ($user->hasRole('super-admin')) {
            $superAdminCount = User::role('super-admin')->count();
            if ($superAdminCount <= 1) {
                return back()->with('error', 'Tidak dapat menghapus satu-satunya akun Super Admin yang tersisa di sistem.');
            }
        }

        $userName = $user->name;
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', "Akun pengguna '{$userName}' berhasil dihapus dari sistem.");
    }
}
