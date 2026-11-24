<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'identifier' => ['required', 'string', 'max:120'],
            'password' => ['required', 'string'],
        ]);

        $throttleKey = 'sonit-login:'.Str::lower($credentials['identifier']).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()
                ->withInput($request->only('identifier'))
                ->withErrors(['identifier' => "Too many sign-in attempts. Please wait {$seconds} seconds and try again."]);
        }

        $identifier = trim($credentials['identifier']);
        $user = User::query()
            ->where('email', $identifier)
            ->orWhere('reg_no', strtoupper($identifier))
            ->orWhere('reg_no', $identifier)
            ->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            RateLimiter::hit($throttleKey, 60);
            \App\Models\ActivityLog::record('auth', 'login_failed', "Failed sign-in attempt for identifier: {$identifier}.");

            return back()
                ->withInput($request->only('identifier'))
                ->withErrors(['identifier' => 'Invalid registration number/email or password.']);
        }

        if (! $user->is_active) {
            return back()
                ->withInput($request->only('identifier'))
                ->withErrors(['identifier' => 'Your account is currently inactive. Contact SONIT Administration.']);
        }

        RateLimiter::clear($throttleKey);
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();
        $user->syncCourseCommunities();

        \App\Models\ActivityLog::record('auth', 'login', "Signed in as {$user->name} ({$user->roleLabel()}).", $user);

        if ($user->isAdmin()) {
            return redirect()->intended(route('admin.dashboard'))
                ->with('success', "Welcome back, {$user->name}.");
        }

        return redirect()->intended(route('dashboard'))
            ->with('success', "Welcome back, {$user->name}.");
    }

    public function showRegister(): View
    {
        $courses = Course::where('is_active', true)->orderBy('level')->orderBy('code')->get();

        return view('auth.register', compact('courses'));
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'reg_no' => ['required', 'string', 'max:50', 'unique:users,reg_no', 'regex:/^NIT\/[A-Z0-9]+\/\d{4}\/\d+$/i'],
            'email' => ['required', 'email', 'max:120', 'unique:users,email'],
            'course_id' => ['required', 'exists:courses,id'],
            'year_joined' => ['required', 'integer', 'min:2018', 'max:2030'],
            'academic_level' => ['required', 'string', 'in:First Year,Second Year,Third Year,Fourth Year,Postgraduate'],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'reg_no.regex' => 'Registration number must follow NIT format, e.g. NIT/BLTM/2023/4805.',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'reg_no' => strtoupper($data['reg_no']),
            'email' => strtolower($data['email']),
            'course_id' => $data['course_id'],
            'year_joined' => $data['year_joined'],
            'academic_level' => $data['academic_level'],
            'phone' => $data['phone'] ?? null,
            'role' => User::ROLE_STUDENT,
            'password' => Hash::make($data['password']),
            'is_active' => true,
        ]);

        // Automatically enroll student into their course communities & general channels
        $user->syncCourseCommunities();

        Auth::login($user);
        $request->session()->regenerate();

        \App\Models\ActivityLog::record('auth', 'register', "Registered new student account {$user->reg_no}.", $user);

        return redirect()->route('dashboard')
            ->with('success', 'Your SONIT student account is active and you have been automatically placed in your course community.');
    }

    public function logout(Request $request): RedirectResponse
    {
        $actor = Auth::user();
        if ($actor) {
            \App\Models\ActivityLog::record('auth', 'logout', "Signed out from SONIT Portal.", $actor);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'You have been signed out securely.');
    }

    public function profile(): View
    {
        $user = Auth::user()->load('course');

        return view('portal.profile', compact('user'));
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:30'],
            'academic_level' => ['nullable', 'string', 'max:40'],
            'bio' => ['nullable', 'string', 'max:500'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_avatar' => ['nullable', 'boolean'],
        ]);

        if ($request->boolean('remove_avatar')) {
            if ($user->avatar && str_starts_with($user->avatar, 'uploads/') && file_exists(public_path($user->avatar))) {
                @unlink(public_path($user->avatar));
            }
            $data['avatar'] = null;
        } elseif ($request->hasFile('avatar')) {
            if ($user->avatar && str_starts_with($user->avatar, 'uploads/') && file_exists(public_path($user->avatar))) {
                @unlink(public_path($user->avatar));
            }
            $file = $request->file('avatar');
            $filename = 'avatar_'.$user->id.'_'.time().'.'.$file->getClientOriginalExtension();
            $file->move(public_path('uploads/avatars'), $filename);
            $data['avatar'] = 'uploads/avatars/'.$filename;
        } else {
            unset($data['avatar']);
        }

        unset($data['remove_avatar']);
        $user->update($data);

        \App\Models\ActivityLog::record('profile', 'profile_updated', "Updated personal profile / avatar.", $user);

        return back()->with('success', 'Profile updated successfully.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $request->user()->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Your password has been updated securely.');
    }
}
