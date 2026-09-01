<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::query()->latest();

        if (! $request->user()->isSuperAdmin()) {
            $query->where('role', '!=', 'super_admin');
        }

        return view('admin.users.index', [
            'users' => $query->paginate(15),
        ]);
    }

    public function create(Request $request): View
    {
        return view('admin.users.form', [
            'user' => new User(['role' => 'courier']),
            'canManageSuperAdmins' => $request->user()->isSuperAdmin(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::in($this->allowedRoles($request))],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
        $validated['password'] = Hash::make($validated['password']);

        User::query()->create($validated);

        return redirect()->route('admin.users.index')->with('success', 'Пользователь добавлен.');
    }

    public function edit(Request $request, User $user): View
    {
        $this->ensureCanManage($request, $user);

        return view('admin.users.form', [
            'user' => $user,
            'canManageSuperAdmins' => $request->user()->isSuperAdmin(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->ensureCanManage($request, $user);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'role' => ['required', Rule::in($this->allowedRoles($request))],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        if ($user->isAdmin() && ! in_array($validated['role'], ['admin', 'super_admin'], true) && User::query()->whereIn('role', ['admin', 'super_admin'])->count() <= 1) {
            return back()->withInput()->withErrors(['role' => 'В системе должен остаться хотя бы один администратор.']);
        }

        if ($user->isSuperAdmin() && $validated['role'] !== 'super_admin' && User::query()->where('role', 'super_admin')->count() <= 1) {
            return back()->withInput()->withErrors(['role' => 'В системе должен остаться хотя бы один супер-администратор.']);
        }

        if (blank($validated['password'] ?? null)) {
            unset($validated['password']);
        } else {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('admin.users.index')->with('success', 'Пользователь обновлён.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $this->ensureCanManage($request, $user);

        if ($user->is($request->user())) {
            return back()->withErrors(['user' => 'Нельзя удалить собственную учётную запись.']);
        }

        if ($user->isSuperAdmin() && User::query()->where('role', 'super_admin')->count() <= 1) {
            return back()->withErrors(['user' => 'Нельзя удалить последнего супер-администратора.']);
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'Пользователь удалён. История расчётов сохранена.');
    }

    private function allowedRoles(Request $request): array
    {
        return $request->user()->isSuperAdmin()
            ? ['admin', 'super_admin', 'courier']
            : ['admin', 'courier'];
    }

    private function ensureCanManage(Request $request, User $user): void
    {
        abort_if($user->isSuperAdmin() && ! $request->user()->isSuperAdmin(), 404);
    }
}
