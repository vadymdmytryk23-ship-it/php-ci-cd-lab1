<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CabinetController extends Controller
{
    public function login(Request $request): RedirectResponse
    {
        $data = $request->validate(['email' => 'required|email', 'password' => 'required|string']);
        if (! Auth::attempt($data + ['status' => 'active'], $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Перевірте електронну пошту та пароль.'])->onlyInput('email');
        }
        $request->session()->regenerate();

        return redirect()->intended(route('web.utility-accounts.index'));
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|string|min:8|max:72|confirmed',
        ]);
        Auth::login(User::create($data)->refresh());
        $request->session()->regenerate();

        return redirect()->route('web.profile')->with('success', 'Профіль створено.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    public function profile(Request $request): View
    {
        return view('auth.profile', ['user' => $request->user()]);
    }

    public function statistics(Request $request): View
    {
        abort_unless($request->user()->role === 'administrator', 403);

        return view('catalog.statistics', [
            'users' => User::count(),
            'bills' => Bill::where('status', 'unpaid')->count(),
            'paid' => Payment::where('status', 'paid')->sum('amount'),
        ]);
    }
}
