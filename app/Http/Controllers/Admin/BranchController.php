<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BranchController extends Controller
{
    public function index(): View
    {
        return view('admin.branches.index', [
            'branches' => Branch::query()->orderByDesc('is_active')->orderBy('name')->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('admin.branches.form', ['branch' => new Branch()]);
    }

    public function store(Request $request): RedirectResponse
    {
        Branch::query()->create($this->validated($request));

        return redirect()->route('admin.branches.index')->with('success', 'Заведение добавлено.');
    }

    public function edit(Branch $branch): View
    {
        return view('admin.branches.form', compact('branch'));
    }

    public function update(Request $request, Branch $branch): RedirectResponse
    {
        $branch->update($this->validated($request));

        return redirect()->route('admin.branches.index')->with('success', 'Заведение обновлено.');
    }

    public function destroy(Branch $branch): RedirectResponse
    {
        $branch->delete();

        return redirect()->route('admin.branches.index')->with('success', 'Заведение удалено. История расчётов сохранена.');
    }

    private function validated(Request $request): array
    {
        return [
            ...$request->validate([
                'name' => ['required', 'string', 'max:120'],
                'address' => ['required', 'string', 'max:500'],
                'latitude' => ['nullable', 'numeric', 'between:-90,90'],
                'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            ]),
            'is_active' => $request->boolean('is_active'),
        ];
    }
}
