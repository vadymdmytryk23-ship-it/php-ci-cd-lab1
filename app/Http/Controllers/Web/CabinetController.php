<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Services\CatalogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CabinetController extends Controller
{
    public function login(LoginRequest $request): RedirectResponse
    {
        $data = $request->validated();
        if (! Auth::attempt($data + ['status' => 'active'], $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Перевірте електронну пошту та пароль.'])->onlyInput('email');
        }
        $request->session()->regenerate();

        return redirect()->intended(route('web.utility-accounts.index'));
    }

    public function register(RegisterRequest $request): RedirectResponse
    {
        $data = $request->validated();
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

    public function statistics(CatalogService $catalog): View
    {
        $this->authorize('viewAny', new User);
        $data = $catalog->statistics();

        return view('catalog.statistics', $data + ['bills' => $data['unpaid_bills']]);
    }
}
