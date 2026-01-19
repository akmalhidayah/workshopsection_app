<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\RolePermission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminAccessController extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::user() || !Auth::user()->isSuperAdmin()) {
            abort(403);
        }

        $roles = User::where('usertype', 'admin')
            ->pluck('role')
            ->filter()
            ->unique()
            ->values()
            ->all();

        $roles = array_values(array_unique(array_merge(
            ['admin', 'admin_limited', 'super_admin'],
            $roles
        )));

        $selectedRole = $request->query('role', 'admin');

        $permissions = Permission::orderBy('label')->get();
        if ($permissions->isEmpty()) {
            $permissions = collect();
            foreach (config('admin_permissions.permissions', []) as $key => $label) {
                $permissions->push((object) ['key' => $key, 'label' => $label]);
            }
        }

        $assigned = RolePermission::where('role', $selectedRole)
            ->pluck('permission_key')
            ->all();

        return view('admin.access-control.index', [
            'roles' => $roles,
            'selectedRole' => $selectedRole,
            'permissions' => $permissions,
            'assigned' => $assigned,
        ]);
    }

    public function update(Request $request)
    {
        if (!Auth::user() || !Auth::user()->isSuperAdmin()) {
            abort(403);
        }

        $data = $request->validate([
            'role' => 'required|string|max:50',
            'permissions' => 'array',
            'permissions.*' => 'string',
        ]);

        $role = $data['role'];
        if ($role === 'super_admin') {
            return redirect()
                ->route('admin.access-control.index', ['role' => $role])
                ->with('error', 'Role super_admin tidak memerlukan permission.');
        }

        $keys = $data['permissions'] ?? [];

        RolePermission::where('role', $role)->delete();
        $rows = [];
        $now = now();
        foreach ($keys as $key) {
            $rows[] = [
                'role' => $role,
                'permission_key' => $key,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        if (!empty($rows)) {
            RolePermission::insert($rows);
        }

        return redirect()
            ->route('admin.access-control.index', ['role' => $role])
            ->with('success', 'Permission berhasil diperbarui.');
    }
}
