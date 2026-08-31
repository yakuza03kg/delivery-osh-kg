<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\DeliveryCalculation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HistoryController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $isAdmin = $user->isAdmin();
        $query = DeliveryCalculation::query()->with(['branch', 'user'])->latest();

        if (! $isAdmin) {
            $query->where('user_id', $user->id);
        }

        if ($isAdmin && $request->filled('courier_id')) {
            $query->where('user_id', $request->integer('courier_id'));
        }

        if ($isAdmin && $request->filled('branch_id')) {
            $query->where('branch_id', $request->integer('branch_id'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        return view('history.index', [
            'calculations' => $query->paginate(20)->withQueryString(),
            'branches' => $isAdmin ? Branch::query()->orderBy('name')->get() : collect(),
            'couriers' => $isAdmin ? User::query()->orderBy('name')->get() : collect(),
            'isAdmin' => $isAdmin,
        ]);
    }
}
