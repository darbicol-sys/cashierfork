<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Accountant — For Review</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet"/>
  <link rel="icon" href="{{ asset('img/dar_logo.png') }}" />
  <style>
    :root {
      --green-deep:   #0e2a1a;
      --green-mid:    #1a4a2e;
      --green-accent: #2d7a4f;
      --green-light:  #e8f4ee;
      --gold:         #c9992a;
      --gold-light:   #e8c46a;
      --cream:        #f5f0e8;
      --border:       #e2ddd5;
      --text-dark:    #0e2a1a;
      --text-mid:     #3d5045;
      --muted:        #8a9e90;
      --bg:           #f4f1eb;
      --surface:      #ffffff;
      --red:          #a0251c;
    }
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'DM Sans', sans-serif; background: var(--bg); min-height: 100vh; color: var(--text-dark); }

    /* ── TOP STRIPE ── */
    .top-stripe { height: 4px; background: linear-gradient(90deg, var(--green-accent), var(--gold), var(--red)); }

    /* ── HEADER ── */
    .page-header {
      background: var(--green-deep);
      padding: 16px 32px;
      display: flex;
      align-items: center;
      gap: 14px;
      position: sticky;
      top: 0;
      z-index: 200;
    }

    /* ── LOGO FIX ── */
    .header-seal {
      width: 38px; height: 38px; border-radius: 50%;
      overflow: hidden; flex-shrink: 0;
      background: transparent;
      display: flex; align-items: center; justify-content: center;
    }
    .header-seal img {
      width: 38px; height: 38px;
      object-fit: cover; border-radius: 50%; display: block;
    }

    .header-text .t1 { font-size: .58rem; letter-spacing: 2.5px; text-transform: uppercase; color: rgba(245,240,232,.35); font-weight: 300; }
    .header-text .t2 { font-size: .85rem; font-weight: 600; color: var(--cream); }
    .header-sep { width: 1px; height: 30px; background: rgba(245,240,232,.15); margin: 0 4px; }
    .header-page { font-family: 'Cormorant Garamond', serif; font-size: 1.2rem; font-weight: 700; color: var(--gold-light); }

    /* ── HEADER ACTIONS ── */
    .header-actions { margin-left: auto; display: flex; align-items: center; gap: 8px; position: relative; }

    /* ── NOTIFICATION ── */
    .notif-btn {
      position: relative; display: flex; align-items: center; justify-content: center;
      width: 44px; height: 44px; border-radius: 10px;
      background: rgba(255,255,255,.07); border: none;
      color: rgba(245,240,232,.55); cursor: pointer;
      transition: background .15s, color .15s; flex-shrink: 0;
    }
    .notif-btn:hover { background: rgba(255,255,255,.14); color: var(--cream); }
    .notif-btn i { font-size: 1.25rem; }
    .notif-badge {
      position: absolute; top: 6px; right: 6px;
      min-width: 18px; height: 18px; padding: 0 6px; border-radius: 12px;
      background: var(--red); color: #fff; font-size: .72rem; font-weight: 700;
      line-height: 18px; text-align: center; display: none; box-shadow: 0 1px 0 rgba(0,0,0,.08);
    }
    .notif-badge.show { display: inline-block; }

    .notif-dropdown {
      display: none; position: absolute; top: calc(100% + 10px); right: 0;
      width: 300px; background: var(--surface); border-radius: 12px;
      border: 1px solid var(--border); box-shadow: 0 8px 32px rgba(0,0,0,.18);
      z-index: 400; overflow: hidden;
    }
    .notif-dropdown.open { display: block; animation: dropIn .18s cubic-bezier(.16,1,.3,1); }
    @keyframes dropIn { from { opacity:0; transform: translateY(-6px); } to { opacity:1; transform:none; } }
    .notif-drop-head { padding: 12px 16px; background: var(--green-deep); display: flex; align-items: center; justify-content: space-between; }
    .notif-drop-title { font-size: .78rem; font-weight: 600; color: var(--gold-light); letter-spacing: .5px; }
    .notif-drop-mark { font-size: .68rem; color: rgba(245,240,232,.45); cursor: pointer; background: none; border: none; font-family: 'DM Sans', sans-serif; transition: color .15s; }
    .notif-drop-mark:hover { color: var(--cream); }
    .notif-list { max-height: 260px; overflow-y: auto; }
    .notif-list::-webkit-scrollbar { width: 3px; }
    .notif-list::-webkit-scrollbar-thumb { background: var(--border); border-radius: 4px; }
    .notif-item { display: flex; align-items: flex-start; gap: 10px; padding: 11px 16px; border-bottom: 1px solid var(--border); transition: background .12s; cursor: pointer; }
    .notif-item:last-child { border-bottom: none; }
    .notif-item.unread { background: #f5fbf7; }
    .notif-item:hover { background: #f0f7f3; }
    .notif-item-icon { width: 30px; height: 30px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: .8rem; flex-shrink: 0; margin-top: 1px; }
    .ni-green { background: var(--green-light); color: var(--green-accent); }
    .ni-gold  { background: #fdf3dc; color: var(--gold); }
    .ni-red   { background: #fdf0ef; color: var(--red); }
    .notif-item-body { flex: 1; min-width: 0; }
    .notif-item-text { font-size: .78rem; color: var(--text-dark); line-height: 1.4; }
    .notif-item-time { font-size: .67rem; color: var(--muted); margin-top: 3px; }
    .notif-unread-dot { width: 7px; height: 7px; border-radius: 50%; background: var(--green-accent); flex-shrink: 0; margin-top: 6px; }
    .notif-empty { padding: 30px 16px; text-align: center; }
    .notif-empty i { font-size: 1.6rem; color: var(--border); display: block; margin-bottom: 8px; }
    .notif-empty p { font-size: .78rem; color: var(--muted); }
    .notif-drop-foot { padding: 9px 16px; border-top: 1px solid var(--border); text-align: center; }
    .notif-drop-foot a { font-size: .72rem; color: var(--green-accent); text-decoration: none; font-weight: 600; }
    .notif-drop-foot a:hover { text-decoration: underline; }

    .btn-logout {
      display: flex; align-items: center; gap: 6px; padding: 8px 16px;
      background: linear-gradient(135deg, var(--gold), var(--gold-light));
      border: 1px solid rgba(201,153,42,.35); border-radius: 8px; color: var(--green-deep);
      font-family: 'DM Sans', sans-serif; font-weight: 700; font-size: .75rem; letter-spacing: .5px;
      cursor: pointer; transition: all .18s ease; box-shadow: 0 2px 6px rgba(0,0,0,.08);
    }
    .btn-logout:hover { background: linear-gradient(135deg, #d6a73b, #f0cf7b); transform: translateY(-1px); }

    /* ── LAYOUT ── */
    .outer-wrapper { display: flex; min-height: calc(100vh - 72px); }

    /* ── SIDEBAR ── */
    .sidebar {
      width: 260px; flex-shrink: 0; background: var(--green-deep);
      border-right: 1px solid rgba(255,255,255,.07);
      position: sticky; top: 72px; height: calc(100vh - 72px);
      display: flex; flex-direction: column;
    }
    .sidebar-inner { flex: 1; overflow-y: auto; padding: 24px 0 0; }
    .sidebar-inner::-webkit-scrollbar { width: 3px; }
    .sidebar-inner::-webkit-scrollbar-thumb { background: rgba(255,255,255,.12); border-radius: 4px; }
    .sidebar-profile { padding: 0 22px 20px; display: flex; align-items: center; gap: 11px; }
    .profile-avatar {
      width: 40px; height: 40px; border-radius: 50%;
      background: linear-gradient(135deg, var(--gold), var(--gold-light));
      display: flex; align-items: center; justify-content: center;
      font-size: .85rem; font-weight: 700; color: var(--green-deep); flex-shrink: 0;
    }
    .profile-name { font-size: .83rem; font-weight: 600; color: var(--cream); }
    .profile-role { font-size: .63rem; color: rgba(245,240,232,.35); letter-spacing: 1px; text-transform: uppercase; margin-top: 2px; }
    .sidebar-divider { border: none; border-top: 1px solid rgba(255,255,255,.07); margin: 0 22px 16px; }
    .nav-section-label { padding: 0 22px; font-size: .6rem; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; color: rgba(245,240,232,.28); margin-bottom: 6px; margin-top: 12px; }
    .nav-item { display: flex; align-items: center; gap: 11px; padding: 10px 22px; cursor: pointer; transition: background .15s; border-left: 3px solid transparent; text-decoration: none; }
    .nav-item:hover { background: rgba(255,255,255,.04); }
    .nav-item.active { background: rgba(45,122,79,.18); border-left-color: var(--gold); }
    .nav-icon { width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: .88rem; flex-shrink: 0; transition: background .15s, color .15s; }
    .nav-item:not(.active) .nav-icon { background: rgba(255,255,255,.07); color: rgba(245,240,232,.55); }
    .nav-item.active .nav-icon { background: var(--gold); color: var(--green-deep); }
    .nav-label { font-size: .81rem; font-weight: 600; color: rgba(245,240,232,.7); }
    .nav-item.active .nav-label { color: var(--cream); }
    .sidebar-footer { padding: 14px 22px; border-top: 1px solid rgba(255,255,255,.07); flex-shrink: 0; }
    .sidebar-footer-label { font-size: .6rem; letter-spacing: 1.5px; text-transform: uppercase; color: rgba(245,240,232,.3); margin-bottom: 4px; }
    .sidebar-footer-value { font-size: .73rem; color: rgba(245,240,232,.5); font-weight: 300; }

    /* ── MAIN ── */
    .main-content { flex: 1; min-width: 0; }
    .page-body { max-width: 1100px; margin: 0 auto; padding: 36px 28px 60px; }

    /* ── PAGE TITLE ── */
    .page-title-row { display: flex; align-items: flex-end; justify-content: space-between; margin-bottom: 24px; gap: 16px; flex-wrap: wrap; }
    .page-title { font-family: 'Cormorant Garamond', serif; font-size: 1.7rem; font-weight: 700; color: var(--text-dark); margin-bottom: 3px; }
    .page-sub { font-size: .8rem; color: var(--muted); font-weight: 300; }

    /* ── ALERTS ── */
    .alert-bar { display: flex; align-items: center; gap: 10px; padding: 12px 18px; border-radius: 10px; margin-bottom: 20px; font-size: .84rem; font-weight: 500; }
    .alert-success { background: var(--green-light); color: var(--green-accent); border: 1px solid rgba(45,122,79,.2); }
    .alert-danger   { background: #fdf0ef; color: var(--red); border: 1px solid rgba(160,37,28,.2); }

    /* ── STAT CARDS ── */
    .stat-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 24px; }
    .stat-card { background: var(--surface); border: 1.5px solid var(--border); border-radius: 12px; padding: 16px 18px; display: flex; align-items: center; gap: 14px; }
    .stat-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0; }
    .si-green { background: var(--green-light); color: var(--green-accent); }
    .si-gold  { background: #fdf3dc; color: var(--gold); }
    .si-amber { background: #fff7ed; color: #c2640a; }
    .si-red   { background: #fdf0ef; color: var(--red); }
    .stat-value { font-size: 1.35rem; font-weight: 700; color: var(--text-dark); line-height: 1.2; }
    .stat-label { font-size: .7rem; color: var(--muted); font-weight: 400; margin-top: 2px; }

    /* ── TOOLBAR ── */
    .toolbar { display: flex; align-items: center; gap: 10px; margin-bottom: 16px; flex-wrap: wrap; }
    .search-wrap { position: relative; flex: 1; min-width: 200px; }
    .search-wrap i { position: absolute; left: 11px; top: 50%; transform: translateY(-50%); color: var(--muted); font-size: .88rem; pointer-events: none; }
    .search-wrap input { width: 100%; padding: 9px 12px 9px 34px; border: 1.5px solid var(--border); border-radius: 9px; font-family: 'DM Sans', sans-serif; font-size: .85rem; color: var(--text-dark); background: var(--surface); outline: none; transition: border-color .2s, box-shadow .2s; }
    .search-wrap input:focus { border-color: var(--green-accent); box-shadow: 0 0 0 3px rgba(45,122,79,.1); }
    .filter-select { padding: 9px 32px 9px 12px; border: 1.5px solid var(--border); border-radius: 9px; font-family: 'DM Sans', sans-serif; font-size: .82rem; color: var(--text-dark); background: var(--surface); outline: none; appearance: none; -webkit-appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%238a9e90' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14L2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 10px center; cursor: pointer; transition: border-color .2s; }
    .filter-select:focus { border-color: var(--green-accent); }

    /* ── TABLE CARD ── */
    .table-card { background: var(--surface); border: 1.5px solid var(--border); border-radius: 14px; overflow: hidden; }
    .table-card-header { padding: 14px 22px; background: linear-gradient(90deg, var(--green-mid), var(--green-deep)); display: flex; align-items: center; justify-content: space-between; gap: 12px; }
    .table-card-title { font-family: 'Cormorant Garamond', serif; font-size: 1rem; font-weight: 700; color: var(--gold-light); display: flex; align-items: center; gap: 9px; }
    .table-record-count { font-size: .68rem; font-weight: 600; padding: 3px 10px; border-radius: 20px; background: rgba(201,153,42,.2); color: var(--gold-light); border: 1px solid rgba(201,153,42,.25); }

    /* ── TABLE ── */
    .approvals-table { width: 100%; border-collapse: collapse; }
    .approvals-table thead tr { background: #faf8f4; border-bottom: 1.5px solid var(--border); }
    .approvals-table thead th { padding: 11px 16px; font-size: .68rem; font-weight: 700; letter-spacing: 1.2px; text-transform: uppercase; color: var(--text-mid); white-space: nowrap; }
    .approvals-table thead th:first-child { padding-left: 22px; }
    .approvals-table thead th:last-child  { padding-right: 22px; }
    .approvals-table tbody tr { border-bottom: 1px solid var(--border); transition: background .13s; }
    .approvals-table tbody tr:last-child { border-bottom: none; }
    .approvals-table tbody tr:hover { background: #f9f7f2; }
    .approvals-table tbody td { padding: 13px 16px; font-size: .85rem; color: var(--text-dark); vertical-align: middle; }
    .approvals-table tbody td:first-child { padding-left: 22px; }
    .approvals-table tbody td:last-child  { padding-right: 22px; }

    /* ── CELL STYLES ── */
    .payor-cell { display: flex; align-items: center; gap: 10px; }
    .payor-avatar { width: 32px; height: 32px; border-radius: 50%; background: var(--green-mid); color: #fff; font-size: .75rem; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .payor-name { font-weight: 600; font-size: .87rem; color: var(--text-dark); }
    .payor-contact { font-size: .72rem; color: var(--muted); margin-top: 1px; }
    .amount-cell { font-weight: 700; font-size: .92rem; color: var(--green-mid); }
    .fund-badge { display: inline-block; padding: 3px 9px; border-radius: 20px; background: #fdf3dc; color: var(--gold); font-size: .68rem; font-weight: 700; white-space: nowrap; }
    .op-number { font-size: .78rem; color: var(--text-mid); font-weight: 500; }
    .date-main { font-size: .82rem; color: var(--text-dark); font-weight: 500; }
    .date-time  { font-size: .7rem; color: var(--muted); margin-top: 2px; }

    /* ── STATUS BADGES ── */
    .status-badge { display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; border-radius: 20px; font-size: .68rem; font-weight: 700; letter-spacing: .4px; text-transform: uppercase; white-space: nowrap; }
    .sb-approved { background: var(--green-light); color: var(--green-accent); }
    .sb-waiting  { background: #fdf3dc; color: #a0700a; }
    .sb-rejected { background: #fdf0ef; color: var(--red); }

    /* ── ACTION BUTTONS ── */
    .actions-cell { display: flex; align-items: center; gap: 6px; }
    .btn-approve { display: inline-flex; align-items: center; gap: 5px; padding: 6px 12px; border: none; border-radius: 7px; background: var(--green-accent); color: #fff; font-family: 'DM Sans', sans-serif; font-size: .72rem; font-weight: 700; cursor: pointer; transition: background .15s; }
    .btn-approve:hover { background: var(--green-mid); }
    .btn-reject  { display: inline-flex; align-items: center; gap: 5px; padding: 6px 12px; border: 1.5px solid #e8c5c5; border-radius: 7px; background: #fdf0ef; color: var(--red); font-family: 'DM Sans', sans-serif; font-size: .72rem; font-weight: 700; cursor: pointer; transition: background .15s, border-color .15s; }
    .btn-reject:hover { background: #fde0de; border-color: #f0a8a8; }

    /* ── TABLE FOOTER / PAGINATION ── */
    .table-footer { padding: 12px 22px; background: #faf8f4; border-top: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; }
    .table-footer-info { font-size: .75rem; color: var(--muted); }
    .table-footer-info strong { color: var(--text-mid); }
    .pagination-wrap { display: flex; align-items: center; gap: 8px; margin-left: auto; }
    .page-link, .page-number { padding: 6px 10px; border-radius: 8px; text-decoration: none; font-weight: 700; color: var(--green-accent); border: 1px solid transparent; background: transparent; }
    .page-link.disabled { opacity: .5; pointer-events: none; color: var(--muted); }
    .page-number { color: var(--text-dark); border: 1px solid transparent; }
    .page-number:hover { background: #f2faf5; border-color: var(--border); }
    .page-number.active { background: var(--gold); color: var(--green-deep); border-color: var(--gold); }
    .page-summary { font-size: .85rem; color: var(--muted); margin-left: 12px; }

    /* ── EMPTY STATE ── */
    .empty-row td { padding: 60px 20px; text-align: center; }
    .empty-icon { font-size: 2.4rem; color: var(--border); margin-bottom: 12px; }
    .empty-text { font-size: .85rem; color: var(--muted); }

    /* ── RESPONSIVE ── */
    @media (max-width: 1024px) { .stat-row { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 768px) {
      .outer-wrapper { flex-direction: column; }
      .sidebar { width: 100%; height: auto; position: static; }
      .sidebar-profile, .sidebar-divider, .nav-section-label, .sidebar-footer { display: none; }
      .sidebar-inner { display: flex; overflow-x: auto; padding: 8px 0; }
      .nav-item { white-space: nowrap; border-left: none; border-bottom: 2px solid transparent; }
      .nav-item.active { border-bottom-color: var(--gold); }
      .page-body { padding: 20px 16px 48px; }
      .notif-dropdown { right: -60px; width: 280px; }
    }
    @media (max-width: 640px) {
      .stat-row { grid-template-columns: 1fr 1fr; }
      .approvals-table thead { display: none; }
      .approvals-table tbody td { display: block; padding: 6px 16px; }
      .approvals-table tbody td:first-child { padding-top: 14px; }
      .approvals-table tbody td:last-child  { padding-bottom: 14px; }
    }
  </style>
</head>
<body>

<div class="top-stripe"></div>

<header class="page-header">
  <div class="header-seal">
    <img src="{{ asset('img/dar_logo.png') }}" alt="DAR logo" />
  </div>
  <div class="header-text">
    <div class="t1">Republic of the Philippines</div>
    <div class="t2">Department of Agrarian Reform</div>
  </div>
  <div class="header-sep"></div>
  <div class="header-page">For Review</div>

  <div class="header-actions">

    <!-- Notification Button -->
    <button class="notif-btn" id="notif-btn" title="Notifications" onclick="toggleNotifDropdown(event)" aria-label="Notifications">
      <i class="bi bi-bell" style="font-size:1.25rem;"></i>
      <span class="notif-badge" id="notif-badge"></span>
    </button>

    <!-- Notification Dropdown -->
    <div class="notif-dropdown" id="notif-dropdown">
      <div class="notif-drop-head">
        <span class="notif-drop-title">Notifications</span>
        <button class="notif-drop-mark" onclick="markAllRead()">Mark all as read</button>
      </div>
      <div class="notif-list" id="notif-list"></div>
      <div class="notif-drop-foot">
        <a href="#">View all notifications</a>
      </div>
    </div>

    <!-- Logout -->
    <form method="POST" action="{{ route('logout') }}" style="display:inline; margin:0;">
      @csrf
      <button type="submit" class="btn-logout">
        <i class="bi bi-box-arrow-right"></i> Logout
      </button>
    </form>

  </div>
</header>

<div class="outer-wrapper">

  <aside class="sidebar">
    <div class="sidebar-inner">
      <div class="sidebar-profile">
        @php
          $displayName = trim((auth()->user()->first_name ?? '') . ' ' . (auth()->user()->last_name ?? '')) ?: (auth()->user()->name ?? 'Accountant');
        @endphp
        @if(!empty(auth()->user()->profile_picture) && \Illuminate\Support\Facades\Storage::disk('public')->exists(auth()->user()->profile_picture))
          <div class="profile-avatar"><img src="{{ asset('storage/' . auth()->user()->profile_picture) }}" alt="{{ $displayName }}" style="width:100%; height:100%; object-fit:cover; border-radius:50%; display:block;"></div>
        @else
          <div class="profile-avatar">{{ strtoupper(substr($displayName ?? 'AC', 0, 2)) }}</div>
        @endif
        <div>
          <div class="profile-name">{{ $displayName }}</div>
          <div class="profile-role">Accountant</div>
        </div>
      </div>
      <hr class="sidebar-divider">

      <div class="nav-section-label" style="margin-top:16px;">Transactions</div>
      <a class="nav-item active" href="{{ route('accountant.approval') }}">
        <div class="nav-icon"><i class="bi bi-hourglass-split"></i></div>
        <span class="nav-label">For Review</span>
      </a>
      <a class="nav-item" href="{{ route('accountant.approved') }}">
        <div class="nav-icon"><i class="bi bi-check2-circle"></i></div>
        <span class="nav-label">Approved Records</span>
      </a>

      <div class="nav-section-label" style="margin-top:16px;">Account</div>
      <a class="{{ request()->routeIs('accountant.profile') ? 'nav-item active' : 'nav-item' }}" href="{{ route('accountant.profile') }}">
        <div class="nav-icon"><i class="bi bi-person-badge"></i></div>
        <span class="nav-label">My Profile</span>
      </a>
    </div>
    <div class="sidebar-footer">
      <div class="sidebar-footer-label">System</div>
      <div class="sidebar-footer-value">DAR Cashier — Regional Office V</div>
    </div>
  </aside>

  <main class="main-content">
    <div class="page-body">

      @if(session('success'))
        <div class="alert-bar alert-success">
          <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
        </div>
      @endif
      @if(session('error'))
        <div class="alert-bar alert-danger">
          <i class="bi bi-exclamation-circle-fill"></i> {{ session('error') }}
        </div>
      @endif

      <div class="page-title-row">
        <div>
          <div class="page-title">Accountant — Approval Queue</div>
          <div class="page-sub">Department of Agrarian Reform — Regional Office V</div>
        </div>
      </div>

      @php
        $total    = $payments->total() ?? count($payments);
        $waiting  = $payments->whereIn('status', ['forwarded', 'accountant_rejected'])->count();
        $approved = \App\Models\Payment::where('status', 'approved')->count();
        $rejected = \App\Models\Payment::where('status', 'accountant_rejected')->count();
      @endphp

      <!-- STAT CARDS -->
      <div class="stat-row">
        <div class="stat-card">
          <div class="stat-icon si-green"><i class="bi bi-receipt"></i></div>
          <div>
            <div class="stat-value">{{ $total }}</div>
            <div class="stat-label">Total Transactions</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon si-amber"><i class="bi bi-hourglass-split"></i></div>
          <div>
            <div class="stat-value">{{ $waiting }}</div>
            <div class="stat-label">Awaiting Approval</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon si-green"><i class="bi bi-check-circle"></i></div>
          <div>
            <div class="stat-value">{{ $approved }}</div>
            <div class="stat-label">Approved</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon si-red"><i class="bi bi-x-circle"></i></div>
          <div>
            <div class="stat-value">{{ $rejected }}</div>
            <div class="stat-label">Rejected</div>
          </div>
        </div>
      </div>

      <!-- TOOLBAR -->
      <div class="toolbar">
        <div class="search-wrap">
          <i class="bi bi-search"></i>
          <input type="text" id="tbl-search" placeholder="Search by payor name or O.P. number…" oninput="filterTable()"/>
        </div>
        <select class="filter-select" id="filter-status" onchange="filterTable()">
          <option value="">All Statuses</option>
          <option value="approved">Approved</option>
          <option value="forwarded">Waiting</option>
          <option value="accountant_rejected">Rejected</option>
        </select>
        <select class="filter-select" id="filter-fund" onchange="filterTable()">
          <option value="">All Funds</option>
          <option value="F01">Fund 01 — Regular</option>
        </select>
      </div>

      <!-- TABLE -->
      <div class="table-card">
        <div class="table-card-header">
          <div class="table-card-title">
            <i class="bi bi-clipboard2-check"></i> Transactions for Review
          </div>
          <span class="table-record-count" id="record-count">
            {{ count($payments) }} record{{ count($payments) !== 1 ? 's' : '' }}
          </span>
        </div>

        <table class="approvals-table" id="approvals-table">
          <thead>
            <tr>
              <th>Payor</th>
              <th>Amount</th>
              <th>Fund</th>
              <th>O.P. Number</th>
              <th>Date Submitted</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="table-body">
            @forelse($payments as $p)
              @php
                $status    = $p->status ?? 'submitted';
                $statusMap = [
                  'approved'            => 'sb-approved',
                  'forwarded'           => 'sb-waiting',
                  'under_review'        => 'sb-waiting',
                  'submitted'           => 'sb-waiting',
                  'accountant_rejected' => 'sb-rejected',
                  'rejected'            => 'sb-rejected',
                ];
                $statusCls  = $statusMap[$status] ?? 'sb-waiting';
                $statusIcon = match($status) {
                  'approved'                        => 'bi-check-circle-fill',
                  'accountant_rejected', 'rejected' => 'bi-x-circle-fill',
                  default                           => 'bi-hourglass-split',
                };
                $nameParts = explode(' ', trim($p->name));
                $initials  = strtoupper(substr($nameParts[0], 0, 1)) . (isset($nameParts[1]) ? strtoupper(substr($nameParts[1], 0, 1)) : '');
              @endphp
              <tr
                data-search="{{ strtolower($p->name . ' ' . ($p->op_number ?? '')) }}"
                data-status="{{ $status }}"
                data-fund="{{ $p->fund_type ?? '' }}"
              >
                <td>
                  <div class="payor-cell">
                    <div class="payor-avatar">{{ $initials }}</div>
                    <div>
                      <div class="payor-name">{{ $p->name }}</div>
                      <div class="payor-contact">{{ $p->email ?? ($p->contact ?? '—') }}</div>
                    </div>
                  </div>
                </td>
                <td><span class="amount-cell">₱{{ number_format($p->amount, 2) }}</span></td>
                <td><span class="fund-badge">{{ $p->fund_type ?? '—' }}</span></td>
                <td><span class="op-number">{{ $p->op_number ?? '—' }}</span></td>
                <td>
                  <div class="date-main">{{ $p->created_at->format('M d, Y') }}</div>
                  <div class="date-time">{{ $p->created_at->format('h:i A') }}</div>
                </td>
                <td>
                  <span class="status-badge {{ $statusCls }}">
                    <i class="bi {{ $statusIcon }}"></i> {{ ucwords(str_replace('_', ' ', $status)) }}
                  </span>
                </td>
                <td>
                  <div class="actions-cell">
                    @if($status !== 'approved')
                      <form method="POST" action="{{ route('accountant.approve', $p->id) }}"
                        onsubmit="return confirm('Approve payment from {{ addslashes($p->name) }} (₱{{ number_format($p->amount, 2) }})?')">
                        @csrf
                        <button type="submit" class="btn-approve"><i class="bi bi-check-lg"></i> Approve</button>
                      </form>
                    @endif
                    @if($status !== 'accountant_rejected')
                      <form method="POST" action="{{ route('accountant.reject', $p->id) }}"
                        onsubmit="var r=prompt('Enter rejection remarks (optional):');if(r===null)return false;this.querySelector('input[name=remarks]').value=r;return confirm('Reject payment from {{ addslashes($p->name) }} (₱{{ number_format($p->amount, 2) }})?')">
                        @csrf
                        <input type="hidden" name="remarks" value=""/>
                        <button type="submit" class="btn-reject"><i class="bi bi-x-lg"></i> Reject</button>
                      </form>
                    @endif
                  </div>
                </td>
              </tr>
            @empty
              <tr class="empty-row">
                <td colspan="7">
                  <div class="empty-icon"><i class="bi bi-inbox"></i></div>
                  <div class="empty-text">No payment records found.</div>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>

        <div class="table-footer">
          <span class="table-footer-info" id="footer-info">
            Showing <strong>{{ count($payments) }}</strong>
            @if(method_exists($payments, 'total') && $payments->total() > count($payments))
              of <strong>{{ $payments->total() }}</strong>
            @endif
            records
          </span>

          @if(method_exists($payments, 'lastPage'))
            <div class="pagination-wrap" aria-label="Pagination">
              @if($payments->onFirstPage())
                <span class="page-link disabled">« Previous</span>
              @else
                <a class="page-link" href="{{ $payments->previousPageUrl() }}">« Previous</a>
              @endif

              @for($i = 1; $i <= $payments->lastPage(); $i++)
                @if($i == $payments->currentPage())
                  <span class="page-number active">{{ $i }}</span>
                @else
                  <a class="page-number" href="{{ $payments->url($i) }}">{{ $i }}</a>
                @endif
              @endfor

              @if($payments->hasMorePages())
                <a class="page-link" href="{{ $payments->nextPageUrl() }}">Next »</a>
              @else
                <span class="page-link disabled">Next »</span>
              @endif

              <div class="page-summary">Showing {{ $payments->firstItem() }} to {{ $payments->lastItem() }} of {{ $payments->total() }} results</div>
            </div>
          @endif
        </div>
      </div>

    </div>
  </main>
</div>

<script>
  /* ── NOTIFICATIONS ── */
  const NOTIF_DATA = {!! json_encode($notif_data ?? []) !!};
  let notifOpen = false;

  function timeAgo(iso) {
    try {
      if (!iso) return '';
      const then = new Date(iso);
      const now = new Date();
      const s = Math.floor((now - then) / 1000);
      if (s < 5) return 'just now';
      if (s < 60) return s + ' seconds ago';
      const m = Math.floor(s/60);
      if (m < 60) return m + (m===1 ? ' minute ago' : ' minutes ago');
      const h = Math.floor(m/60);
      if (h < 24) return h + (h===1 ? ' hour ago' : ' hours ago');
      const d = Math.floor(h/24);
      return d + (d===1 ? ' day ago' : ' days ago');
    } catch(e) { return '' }
  }

  function renderNotifList() {
    const list = document.getElementById('notif-list');
    const unreadCount = NOTIF_DATA.filter(n => n.unread).length;
    const badge = document.getElementById('notif-badge');
    if (unreadCount > 0) {
      badge.classList.add('show');
      badge.textContent = unreadCount > 99 ? '99+' : String(unreadCount);
      badge.setAttribute('title', unreadCount + ' unread notifications');
    } else {
      badge.classList.remove('show');
      badge.textContent = '';
      badge.removeAttribute('title');
    }
    if (NOTIF_DATA.length === 0) {
      list.innerHTML = '<div class="notif-empty"><i class="bi bi-bell-slash"></i><p>No notifications yet.</p></div>';
      return;
    }
    list.innerHTML = NOTIF_DATA.map(n => {
      const t = n.ts ? timeAgo(n.ts) : (n.time || '');
      return `<div class="notif-item${n.unread ? ' unread' : ''}" onclick="readNotif('${n.id}')">
        <div class="notif-item-icon ${n.cls}"><i class="bi ${n.icon}"></i></div>
        <div class="notif-item-body">
          <div class="notif-item-text">${n.text}</div>
          <div class="notif-item-time">${t}</div>
        </div>
        ${n.unread ? '<div class="notif-unread-dot"></div>' : ''}
      </div>`;
    }).join('');
  }

  function readNotif(id) {
    const n = NOTIF_DATA.find(x => x.id === id);
    if (n) n.unread = false;
    renderNotifList();
  }

  function markAllRead() {
    NOTIF_DATA.forEach(n => n.unread = false);
    renderNotifList();
  }

  function toggleNotifDropdown(e) {
    e.stopPropagation();
    const dropdown = document.getElementById('notif-dropdown');
    notifOpen = !notifOpen;
    if (notifOpen) { dropdown.classList.add('open'); renderNotifList(); }
    else dropdown.classList.remove('open');
  }

  document.addEventListener('click', function(e) {
    const btn = document.getElementById('notif-btn');
    const dropdown = document.getElementById('notif-dropdown');
    if (notifOpen && !btn.contains(e.target) && !dropdown.contains(e.target)) {
      dropdown.classList.remove('open');
      notifOpen = false;
    }
  });

  window.addEventListener('load', function() {
    const unreadCount = NOTIF_DATA.filter(n => n.unread).length;
    const badge = document.getElementById('notif-badge');
    if (unreadCount > 0) {
      badge.classList.add('show');
      badge.textContent = unreadCount > 99 ? '99+' : String(unreadCount);
      badge.setAttribute('title', unreadCount + ' unread notifications');
    } else {
      badge.classList.remove('show');
      badge.textContent = '';
    }
  });

  /* ── TABLE FILTER ── */
  function filterTable() {
    const q    = document.getElementById('tbl-search').value.toLowerCase();
    const sf   = document.getElementById('filter-status').value.toLowerCase();
    const ff   = document.getElementById('filter-fund').value.toLowerCase();
    const rows = document.querySelectorAll('#table-body tr[data-search]');
    let visible = 0;
    rows.forEach(row => {
      const show =
        (!q  || row.dataset.search.includes(q))  &&
        (!sf || row.dataset.status === sf)        &&
        (!ff || row.dataset.fund.toLowerCase() === ff);
      row.style.display = show ? '' : 'none';
      if (show) visible++;
    });
    document.getElementById('record-count').textContent = visible + (visible === 1 ? ' record' : ' records');
    document.getElementById('footer-info').innerHTML = 'Showing <strong>' + visible + '</strong> of <strong>' + rows.length + '</strong> records';
  }
</script>

</body>
</html>