<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Notifications\NewMessageNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class MakerController extends Controller
{
    /**
     * Show the dashboard view.
     */
    public function index()
    {
        $user = auth()->user();
        $notifications = $user ? $user->notifications()->orderBy('created_at', 'desc')->take(50)->get() : collect();
        $notif_data = $notifications->map(function($n){
            $d = $n->data ?? [];
            return [
                'id' => $n->id,
                'icon' => $d['icon'] ?? 'bi-bell',
                'cls' => $d['cls'] ?? 'ni-gold',
                'text' => $d['message'] ?? ($d['text'] ?? ''),
                'time' => $d['time'] ?? ($n->created_at ? $n->created_at->diffForHumans() : ''),
                'ts' => $n->created_at ? $n->created_at->toIso8601String() : null,
                'unread' => $n->read_at ? false : true,
                'data' => $d,
            ];
        })->toArray();

        return view('maker.maker.maker', compact('notif_data'));
    }

    // Maker profile page
    public function profile()
    {
        $user = auth()->user();
        return view('maker.profile.profile', compact('user'));
    }

    // Update maker profile
    public function updateProfile(Request $request)
    {
        $user = auth()->user();
        if (! $user) return redirect()->route('login');

        $validated = $request->validate([
            'first_name' => ['required','string','max:191'],
            'last_name'  => ['required','string','max:191'],
            'middle_name'=> ['nullable','string','max:191'],
            'username'   => ['nullable','string','max:100'],
            'phone_number' => ['nullable','string','max:50'],
            'address'    => ['nullable','string','max:1000'],
            'profile_picture' => ['nullable','image','mimes:jpg,jpeg,png,gif','max:4096'],
        ]);

        if ($request->hasFile('profile_picture')) {
            $path = $request->file('profile_picture')->store('profiles', 'uploads');
            if (!empty($user->profile_picture)) {
                Storage::disk('uploads')->delete($user->profile_picture);
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

        return redirect()->route('maker.profile')->with('success', 'Profile updated.');
    }

    // Update maker password
    public function updatePassword(Request $request)
    {
        $user = auth()->user();
        if (! $user) return redirect()->route('login');

        $validated = $request->validate([
            'current_password' => ['required','string'],
            'password' => ['required','string','min:8','confirmed'],
        ]);

        if (! Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $user->password = Hash::make($validated['password']);
        $user->save();

        return redirect()->route('maker.profile')->with('success', 'Password updated.');
    }

    // Show payment creation form (reuses payments.create view)
    public function createPayment()
    {
        $user = auth()->user();
        $notifications = $user ? $user->notifications()->orderBy('created_at', 'desc')->take(50)->get() : collect();
        $notif_data = $notifications->map(function($n){
            $d = $n->data ?? [];
            return [
                'id' => $n->id,
                'icon' => $d['icon'] ?? 'bi-bell',
                'cls' => $d['cls'] ?? 'ni-gold',
                'text' => $d['message'] ?? ($d['text'] ?? ''),
                'time' => $d['time'] ?? ($n->created_at ? $n->created_at->diffForHumans() : ''),
                'ts' => $n->created_at ? $n->created_at->toIso8601String() : null,
                'unread' => $n->read_at ? false : true,
                'data' => $d,
            ];
        })->toArray();

        return view('maker.maker.maker', compact('notif_data'));
    }

    // Show payment creation form for Reviewer inside reviewer layout (route guarded by reviewer middleware)
    public function createForReviewer(Request $request)
    {
        $user = auth()->user();
        $notifications = $user ? $user->notifications()->orderBy('created_at', 'desc')->take(50)->get() : collect();
        $notif_data = $notifications->map(function($n){
            $d = $n->data ?? [];
            return [
                'id' => $n->id,
                'icon' => $d['icon'] ?? 'bi-bell',
                'cls' => $d['cls'] ?? 'ni-gold',
                'text' => $d['message'] ?? ($d['text'] ?? ''),
                'time' => $d['time'] ?? ($n->created_at ? $n->created_at->diffForHumans() : ''),
                'ts' => $n->created_at ? $n->created_at->toIso8601String() : null,
                'unread' => $n->read_at ? false : true,
                'data' => $d,
            ];
        })->toArray();

        return view('maker.maker.maker', compact('notif_data'));
    }

    // List saved payments
    public function listPayments()
    {
        $status = request()->query('status', '');
        $fund   = request()->query('fund', '');
        $q      = request()->query('search', '');

        $query = Payment::orderBy('created_at', 'desc');

        if ($status) {
            $s = strtolower($status);
            if ($s === 'waiting') {
                $query->whereIn('status', ['submitted','under_review','approver_rejected','waiting']);
            } elseif ($s === 'rejected') {
                $query->whereIn('status', ['rejected','approver_rejected']);
            } else {
                $query->where('status', $s);
            }
        }

        if ($fund) {
            $query->where('fund_type', $fund);
        }

        if ($q) {
            $query->where(function($qr) use ($q) {
                $qr->where('name', 'like', '%' . $q . '%')
                   ->orWhere('op_number', 'like', '%' . $q . '%')
                   ->orWhere('transaction_type', 'like', '%' . $q . '%');
            });
        }

        $totalCount = (clone $query)->count();
        $totalSum   = (clone $query)->sum('amount');
        $awaiting   = (clone $query)->whereIn('status', ['submitted','under_review','approver_rejected','waiting'])->count();
        $approved   = (clone $query)->where('status', 'approved')->count();

        $payments = (clone $query)->paginate(25)->withQueryString();

        return view('maker.payments.payments', compact('payments','totalCount','totalSum','awaiting','approved'));
    }

    /**
     * Return recent payments as JSON for AJAX consumption.
     */
    public function paymentsJson()
    {
        $payments = Payment::orderBy('created_at', 'desc')->take(200)->get();

        $data = $payments->map(function ($p) {
            $raw = $p->status ?? 'waiting';
            if (in_array($raw, ['approved'])) {
                $status = 'approved';
            } elseif (in_array($raw, ['rejected', 'approver_rejected'])) {
                $status = 'rejected';
            } else {
                $status = 'waiting';
            }

            return [
                'id' => $p->id,
                'name' => $p->name,
                'email' => $p->email,
                'contact' => $p->contact,
                'amount' => number_format($p->amount, 2),
                'amountRaw' => (float) $p->amount,
                'transaction_type' => $p->transaction_type,
                'fund_type' => $p->fund_type,
                'op_number' => $p->op_number,
                'status' => $status,
                'created_at' => $p->created_at ? $p->created_at->toDateTimeString() : null,
            ];
        });

        return response()->json($data);
    }

    /**
     * Handle dashboard form submissions (payment processing stub).
     */
    public function store(Request $request)
    {
        // Debug CSRF/session data to diagnose 419 errors
        Log::info('CSRF debug - incoming', [
            'route' => $request->route()?->getName(),
            'session_id' => $request->session()->getId(),
            'session_token' => $request->session()->token(),
            'form__token' => $request->input('_token'),
            'header_x_csrf' => $request->header('X-CSRF-TOKEN'),
            'cookie_header' => $request->headers->get('cookie'),
        ]);

        $rules = [
            'transaction_type' => 'sometimes|string|nullable',
            'amount' => 'required|numeric|min:0',
            'name' => 'required|string|max:191',
            'contact' => 'required|string|max:50',
            'address' => 'required|string|max:255',
            'email' => 'required|email|max:191',
        ];

        // transaction-specific required fields
        $txn = $request->input('transaction_type');
        if ($txn === 'bidding_documents') {
            $rules['bid_details'] = 'required|string';
        }
        if ($txn === 'cash_bond') {
            $rules['area_hectares'] = 'required|numeric|min:0';
            $rules['zonal_value'] = 'required|numeric|min:0';
            $rules['property_location'] = 'required|string';
            $rules['assessment_form'] = 'required|string';
        }
        if ($txn === 'certification_copy_fee') {
            $rules['letter_request'] = 'required|string';
            $rules['cert_type'] = 'required|array|min:1';
            $rules['cert_type.*'] = 'string';
            $rules['copy_count'] = 'nullable|integer|min:1';
        }
        if ($txn === 'consignment') {
            $rules['consignment_assessment_form'] = 'required|string';
            $rules['consignment_case_no'] = 'required|string';
        }
        if ($txn === 'execution_judgment') {
            $rules['exec_assessment_form'] = 'required|string';
            $rules['exec_txn_type_paid'] = 'required|string';
        }
        if ($txn === 'filing_fee') {
            $rules['filing_assessment_form'] = 'required|string';
        }
        if ($txn === 'income_unserviceable') {
            $rules['rdc_resolution_no'] = 'required|string';
        }
        if ($txn === 'performance_bond') {
            $rules['pb_area_hectares'] = 'required|numeric|min:0';
            $rules['pb_zonal_value'] = 'required|numeric|min:0';
            $rules['pb_property_location'] = 'required|string';
            $rules['pb_assessment_form'] = 'required|string';
        }
        if ($txn === 'refund_cash_advances') {
            $rules['check_lddap_ada'] = 'required|string';
            $rules['cash_advance_date'] = 'required|date';
            $rules['division_section'] = 'required|string';
        }
        if ($txn === 'refund_overpayment') {
            $rules['refund_division_section'] = 'required|string';
        }
        if ($txn === 'settlement_disallowances') {
            $rules['disallowance_no'] = 'required|string';
        }
        if ($txn === 'unwithheld_remittances') {
            $rules['remit_type'] = 'required|array|min:1';
            $rules['remit_type.*'] = 'string';
        }

        // cheque requirement when payment_mode == cheque
        if ($request->input('payment_mode') === 'cheque') {
            $rules['cheque_number'] = 'required|string';
        }

        $data = $request->validate($rules);
        $meta = $request->except(['_token', 'transaction_type', 'fund_type', 'amount', 'name', 'contact', 'address', 'email', 'payment_mode', 'agree_terms']);

        // Log incoming submission for debugging
        Log::info('Maker store called', ['route' => $request->route()?->getName(), 'data' => $request->except(['_token'])]);

        try {
            $payment = Payment::create([
                'transaction_type' => $data['transaction_type'] ?? null,
                'fund_type' => $request->input('fund_type'),
                'amount' => $data['amount'],
                'name' => $data['name'],
                'contact' => $data['contact'],
                'address' => $data['address'],
                'email' => $data['email'],
                'payment_mode' => $request->input('payment_mode'),
                'meta' => $meta,
                'status' => 'submitted',
            ]);
            Log::info('Payment created', ['id' => $payment->id]);
            // Notify reviewer(s) about the new payment
            try {
                $reviewerRoleId = DB::table('roles')->where('name', 'reviewer')->value('id');
                if ($reviewerRoleId) {
                    $reviewers = User::where('role_id', $reviewerRoleId)->get();
                    foreach ($reviewers as $r) {
                        $r->notify(new NewMessageNotification($payment, auth()->user()));
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('Failed to notify reviewers: ' . $e->getMessage());
            }
        } catch (\Exception $e) {
            Log::error('Payment create failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()->withInput()->with('error', 'Failed to save payment: ' . $e->getMessage());
        }

        $routeName = $request->route() ? $request->route()->getName() : null;

        // If submitted from the Maker dashboard, return there.
        if ($routeName === 'dashboard.store') {
            return redirect()->route('dashboard')->with('success', 'Payment saved.');
        }

        // If submitted from a reviewer-scoped route, redirect back to reviewer area.
        if ($routeName && str_starts_with($routeName, 'reviewer.')) {
            return redirect()->route('reviewer')->with('success', 'Payment saved.');
        }

        // Default: go to payments listing (Maker view).
        return redirect()->route('payments.index')->with('success', 'Payment saved.');
    }
    
}
