<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        return view('admin.users.index', [
            'users' => User::query()->latest()->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('admin.users.form', ['user' => new User(['role' => 'courier'])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', 'in:admin,courier'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
        $validated['password'] = Hash::make($validated['password']);

        User::query()->create($validated);

        return redirect()->route('admin.users.index')->with('success', 'Пользователь добавлен.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.form', compact('user'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'role' => ['required', 'in:admin,courier'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        if ($user->isAdmin() && $validated['role'] !== 'admin' && User::query()->where('role', 'admin')->count() <= 1) {
            return back()->withInput()->withErrors(['role' => 'В системе должен остаться хотя бы один администратор.']);
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
        if ($user->is($request->user())) {
            return back()->withErrors(['user' => 'Нельзя удалить собственную учётную запись.']);
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'Пользователь удалён. История расчётов сохранена.');
    }
}
