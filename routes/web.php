<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\LoginController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\MakerController;
use App\Http\Controllers\ApproverController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ReviewerController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
	return view('landingpage.landingpage');
});

Route::get('/login', [LoginController::class, 'index'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.attempt');
// Password reset (forgot password) routes
Route::get('/forgot-password', [PasswordResetController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [PasswordResetController::class, 'reset'])->name('password.update');
Route::get('/login/otp', [App\Http\Controllers\LoginController::class, 'showOtpForm'])->name('auth.otp.show');
Route::post('/login/otp', [App\Http\Controllers\LoginController::class, 'verifyOtp'])->name('auth.otp.verify');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/dashboard', function () {
	return redirect()->route('dashboard');
});





Route::middleware(['auth', \App\Http\Middleware\RequireRole::class . ':maker'])->group(function () {
	// Maker dashboard (protected)
	Route::get('/maker', [MakerController::class, 'index'])->name('dashboard');
	// Maker profile
	Route::get('/maker/profile', [MakerController::class, 'profile'])->name('maker.profile');
	// Maker profile update routes
	Route::patch('/maker/profile', [MakerController::class, 'updateProfile'])->name('maker.profile.update');
	Route::patch('/maker/profile/password', [MakerController::class, 'updatePassword'])->name('maker.profile.password');
	// Handle payment form submissions from the dashboard (now served at /maker)
	Route::post('/maker', [MakerController::class, 'store'])->name('dashboard.store')->middleware(\App\Http\Middleware\LogUserActivity::class);

// Payments flow (handled by MakerController)
Route::get('/payments', [MakerController::class, 'listPayments'])->name('payments.index');
// JSON endpoint for AJAX requests
Route::get('/payments.json', [MakerController::class, 'paymentsJson'])->name('payments.json');
Route::get('/payments/create', [MakerController::class, 'createPayment'])->name('payments.create');
Route::post('/payments', [MakerController::class, 'store'])->name('payments.store')->middleware(\App\Http\Middleware\LogUserActivity::class);

	// Maker notifications (JSON + mark read)
	Route::get('/maker/notifications', function (\Illuminate\Http\Request $request) {
		$user = auth()->user();
		if (! $user) return response()->json([], 401);
		$notes = $user->notifications()->orderBy('created_at', 'desc')->take(50)->get()->map(function ($n) {
			return [
				'id' => $n->id,
				'data' => $n->data,
				'read' => $n->read_at ? true : false,
				'created_at' => $n->created_at ? $n->created_at->toIso8601String() : null,
			];
		});
		return response()->json($notes);
	})->name('maker.notifications.list');

	Route::post('/maker/notifications/{id}/read', function ($id) {
		$user = auth()->user();
		if (! $user) return response()->json([], 401);
		$note = $user->notifications()->where('id', $id)->first();
		if ($note) {
			$note->markAsRead();
			return response()->json(['ok' => true]);
		}
		return response()->json(['ok' => false], 404);
	})->name('maker.notifications.read');

	Route::post('/maker/notifications/mark-all', function (\Illuminate\Http\Request $request) {
		$user = auth()->user();
		if (! $user) return response()->json([], 401);
		try {
			$user->unreadNotifications->markAsRead();
			return response()->json(['ok' => true]);
		} catch (\Throwable $e) {
			return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
		}
	})->name('maker.notifications.mark_all');

	// Maker: page to view all notifications
	Route::get('/maker/notifications/all', function (\Illuminate\Http\Request $request) {
		$user = auth()->user();
		if (! $user) return redirect()->route('login');
		$notes = $user->notifications()->orderBy('created_at', 'desc')->paginate(25);
		return view('maker.notifications.notification', compact('notes'));
	})->name('maker.notifications.page');

	// Maker: clear all notifications
	Route::delete('/maker/notifications/clear', function (\Illuminate\Http\Request $request) {
		$user = auth()->user();
		if (! $user) return response()->json([], 401);
		try {
			$user->notifications()->delete();
			return response()->json(['ok' => true]);
		} catch (\Throwable $e) {
			return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
		}
	})->name('maker.notifications.clear_all');
});

// Accountant routes (require authenticated approver)
Route::middleware(['auth', \App\Http\Middleware\RequireRole::class . ':approver'])->group(function () {
	
	Route::get('/accountant/approved', [ApproverController::class, 'approved'])->name('accountant.approved');
	Route::get('/accountant/approval', [ApproverController::class, 'approval'])->name('accountant.approval');
	// Accountant profile
	Route::get('/accountant/profile', [ApproverController::class, 'profile'])->name('accountant.profile');
	Route::patch('/accountant/profile', [ApproverController::class, 'updateProfile'])->name('accountant.profile.update');
	Route::patch('/accountant/profile/password', [ApproverController::class, 'updatePassword'])->name('accountant.profile.password');
	// Remove profile picture
	Route::post('/accountant/profile/picture/remove', [ApproverController::class, 'removeProfilePicture'])->name('accountant.profile.remove_picture');
	Route::post('/accountant/approval/{id}/approve', [ApproverController::class, 'approve'])->name('accountant.approve');
	Route::post('/accountant/approval/{id}/reject', [ApproverController::class, 'reject'])->name('accountant.reject');

	// Accountant notifications (JSON + mark read)
	Route::get('/accountant/notifications', function (\Illuminate\Http\Request $request) {
		$user = auth()->user();
		if (! $user) return response()->json([], 401);
		$notes = $user->notifications()->orderBy('created_at', 'desc')->take(50)->get()->map(function ($n) {
			return [
				'id' => $n->id,
				'data' => $n->data,
				'read' => $n->read_at ? true : false,
				'created_at' => $n->created_at ? $n->created_at->toIso8601String() : null,
			];
		});
		return response()->json($notes);
	})->name('accountant.notifications.list');

	Route::post('/accountant/notifications/{id}/read', function ($id) {
		$user = auth()->user();
		if (! $user) return response()->json([], 401);
		$note = $user->notifications()->where('id', $id)->first();
		if ($note) {
			$note->markAsRead();
			return response()->json(['ok' => true]);
		}
		return response()->json(['ok' => false], 404);
	})->name('accountant.notifications.read');

	Route::post('/accountant/notifications/mark-all', function (\Illuminate\Http\Request $request) {
		$user = auth()->user();
		if (! $user) return response()->json([], 401);
		try {
			$user->unreadNotifications->markAsRead();
			return response()->json(['ok' => true]);
		} catch (\Throwable $e) {
			return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
		}
	})->name('accountant.notifications.mark_all');

	// Accountant: page to view all notifications
	Route::get('/accountant/notifications/all', function (\Illuminate\Http\Request $request) {
		$user = auth()->user();
		if (! $user) return redirect()->route('login');
		$notes = $user->notifications()->orderBy('created_at', 'desc')->paginate(25);
		return view('approver.notifications.notification', compact('notes'));
	})->name('accountant.notifications.page');

	// Accountant: clear all notifications
	Route::delete('/accountant/notifications/clear', function (\Illuminate\Http\Request $request) {
		$user = auth()->user();
		if (! $user) return response()->json([], 401);
		try {
			$user->notifications()->delete();
			return response()->json(['ok' => true]);
		} catch (\Throwable $e) {
			return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
		}
	})->name('accountant.notifications.clear_all');
});


// Admin routes (require authenticated admin)
Route::middleware(['auth', \App\Http\Middleware\RequireRole::class . ':admin'])->group(function () {
	// Admin dashboard
	Route::get('/admin', [AdminController::class, 'index'])->name('admin.dashboard');

	// Admin: export users CSV
	Route::get('/admin/users/export', [AdminController::class, 'exportUsers'])->name('admin.users.export');

	// Admin: users list page
	Route::get('/admin/users', [AdminController::class, 'users'])->name('admin.users');

	// Admin: view single user
	Route::get('/admin/users/{id}', [AdminController::class, 'show'])->name('admin.users.show');

	// Admin: audit logs
	Route::get('/admin/auditlogs', [AdminController::class, 'auditlogs'])->name('admin.auditlogs');

	// Admin: transaction history (previously reports)
	Route::get('/admin/history', [AdminController::class, 'reports'])->name('admin.history');

	// Admin: user management routes
	Route::post('/admin/users', [AdminController::class, 'store'])->name('admin.users.store')->middleware(\App\Http\Middleware\LogUserActivity::class);
	// Admin: update user
	Route::patch('/admin/users/{id}', [AdminController::class, 'update'])->name('admin.users.update')->middleware(\App\Http\Middleware\LogUserActivity::class);
	Route::post('/admin/users/{id}/toggle', [AdminController::class, 'toggle'])->name('admin.users.toggle')->middleware(\App\Http\Middleware\LogUserActivity::class);
	Route::delete('/admin/users/{id}', [AdminController::class, 'destroy'])->name('admin.users.destroy')->middleware(\App\Http\Middleware\LogUserActivity::class);

	// Admin: transaction history generate
	Route::post('/admin/history/generate', [AdminController::class, 'generateReport'])->name('admin.history.generate')->middleware(\App\Http\Middleware\LogUserActivity::class);

	// Profile routes handled by AdminController
Route::get('/profile', [AdminController::class, 'profile'])->name('profile')->middleware('auth');
Route::patch('/profile', [AdminController::class, 'updateProfile'])->name('profile.update')->middleware('auth');
Route::patch('/profile/password', [AdminController::class, 'updatePassword'])->name('profile.password')->middleware('auth');
});

// Reviewer routes (require authenticated reviewer)
Route::middleware(['auth', \App\Http\Middleware\RequireRole::class . ':reviewer'])->group(function () {
	// REVIEWER VIEW SINGLE PAYMENT
Route::get('/reviewer/payment/{id}', [ReviewerController::class, 'show'])
    ->name('reviewer.show');
	Route::get('/reviewer', [ReviewerController::class, 'index'])->name('reviewer');
	Route::put('/payments/{id}', [ReviewerController::class, 'update'])->name('payments.update')->middleware(\App\Http\Middleware\LogUserActivity::class);
	// Reviewer forwards payments to Accountant for final approval
	Route::post('/payments/{id}/forward', [ReviewerController::class, 'forward'])->name('payments.forward');
	Route::get('/payments/next-op', [ReviewerController::class, 'nextOpNumber'])->name('payments.next-op');

	// Allow Reviewers to open Maker's payment create page and submit (reuses MakerController)
	Route::get('/reviewer/payments/create', [MakerController::class, 'createForReviewer'])->name('reviewer.payments.create');
	Route::post('/reviewer/payments', [MakerController::class, 'store'])->name('reviewer.payments.store')->middleware(\App\Http\Middleware\LogUserActivity::class);
	Route::get('/reviewer/payments', [MakerController::class, 'listPayments'])->name('reviewer.payments.index');

	// Notifications for reviewer (JSON)
	Route::get('/notifications', function (\Illuminate\Http\Request $request) {
		$user = auth()->user();
		if (! $user) return response()->json([], 401);
		$notes = $user->notifications()->orderBy('created_at', 'desc')->take(50)->get()->map(function ($n) {
			return [
				'id' => $n->id,
				'data' => $n->data,
				'read' => $n->read_at ? true : false,
				'created_at' => $n->created_at ? $n->created_at->toIso8601String() : null,
			];
		});
		return response()->json($notes);
	})->name('notifications.list');

	Route::post('/notifications/{id}/read', function ($id) {
		$user = auth()->user();
		if (! $user) return response()->json([], 401);
		$note = $user->notifications()->where('id', $id)->first();
		if ($note) {
			$note->markAsRead();
			return response()->json(['ok' => true]);
		}
		return response()->json(['ok' => false], 404);
	})->name('notifications.read');

	// Reviewer: mark all notifications as read
	Route::post('/notifications/mark-all', function (\Illuminate\Http\Request $request) {
		$user = auth()->user();
		if (! $user) return response()->json([], 401);
		try {
			$user->unreadNotifications->markAsRead();
			return response()->json(['ok' => true]);
		} catch (\Throwable $e) {
			return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
		}
	})->name('notifications.mark_all');

		// Reviewer: clear all notifications (delete)
		Route::delete('/notifications/clear', function (\Illuminate\Http\Request $request) {
			$user = auth()->user();
			if (! $user) return response()->json([], 401);
			try {
				$user->notifications()->delete();
				return response()->json(['ok' => true]);
			} catch (\Throwable $e) {
				return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
			}
		})->name('notifications.clear_all');

	// Page: view all notifications (reviewer)
	Route::get('/notifications/all', function (\Illuminate\Http\Request $request) {
		$user = auth()->user();
		if (! $user) return redirect()->route('login');
		$notes = $user->notifications()->orderBy('created_at', 'desc')->paginate(25);
		return view('reviewer.notifications.notification', compact('notes'));
	})->name('notifications.page');

	// Reviewer: profile page
	Route::get('/reviewer/profile', function () {
		$user = auth()->user();
		return view('reviewer.profile', compact('user'));
	})->name('reviewer.profile');

	// Reviewer: update profile
	Route::patch('/reviewer/profile', function (\Illuminate\Http\Request $request) {
		$user = auth()->user();

		$validated = $request->validate([
			'first_name' => ['required','string','max:191'],
			'last_name'  => ['required','string','max:191'],
			'middle_name'=> ['nullable','string','max:191'],
			'username'   => ['nullable','string','max:100'],
			'phone_number' => ['nullable','string','max:50'],
			'address'    => ['nullable','string','max:1000'],
			'profile_picture' => ['nullable','image','max:4096'],
		]);

		if ($request->hasFile('profile_picture')) {
			$path = $request->file('profile_picture')->store('profiles', 'public');
			// remove old picture if present
			if (!empty($user->profile_picture)) {
				\Illuminate\Support\Facades\Storage::disk('public')->delete($user->profile_picture);
			}
			$user->profile_picture = $path;
		}

		$user->first_name = $validated['first_name'];
		$user->last_name  = $validated['last_name'];
		$user->middle_name = $validated['middle_name'] ?? null;
		$user->username   = $validated['username'] ?? $user->username;
		$user->phone_number = $validated['phone_number'] ?? $user->phone_number;
		$user->address    = $validated['address'] ?? $user->address;
		$user->save();

		return redirect()->route('reviewer.profile')->with('success', 'Profile updated.');
	})->name('reviewer.profile.update');

	// Reviewer: change password
	Route::patch('/reviewer/profile/password', function (\Illuminate\Http\Request $request) {
		$user = auth()->user();
		$validated = $request->validate([
			'current_password' => ['required','string'],
			'password' => ['required','string','min:8','confirmed'],
		]);
		if (!\Illuminate\Support\Facades\Hash::check($validated['current_password'], $user->password)) {
			return back()->withErrors(['current_password' => 'Current password is incorrect.']);
		}
		$user->password = \Illuminate\Support\Facades\Hash::make($validated['password']);
		$user->save();
		return redirect()->route('reviewer.profile')->with('success', 'Password updated.');
	})->name('reviewer.profile.password');
});


