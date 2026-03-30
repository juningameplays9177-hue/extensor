<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Top Rio - Deposito de Cacambas' }}</title>
    <style>
        :root {
            --bg: #f4f7fb;
            --card: #ffffff;
            --text: #1f2937;
            --muted: #64748b;
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --border: #e5e7eb;
            --warning: #fef3c7;
            --warning-text: #92400e;
            --danger: #fee2e2;
            --danger-text: #991b1b;
            --ok: #e8f4ff;
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(160deg, #f9fbff 0%, var(--bg) 55%, #eef2f7 100%);
            color: var(--text);
        }
        a { color: inherit; text-decoration: none; }

        /* Sidebar Lateral Retrátil */
        .app-wrapper {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            width: 280px;
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
            color: #fff;
            z-index: 1000;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 4px 0 24px rgba(0, 0, 0, 0.15);
            overflow-y: auto;
            overflow-x: hidden;
        }
        
        .sidebar.collapsed {
            transform: translateX(-240px);
        }
        
        .sidebar-header {
            padding: 24px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }
        
        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 700;
            font-size: 18px;
            letter-spacing: 0.3px;
        }
        
        .brand-badge {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            display: grid;
            place-items: center;
            background: linear-gradient(135deg, #3b82f6, #60a5fa);
            color: #fff;
            font-weight: 700;
            font-size: 16px;
            flex-shrink: 0;
        }
        
        .brand-text {
            white-space: nowrap;
            opacity: 1;
            transition: opacity 0.2s;
        }
        
        .sidebar.collapsed .brand-text {
            opacity: 0;
            width: 0;
            overflow: hidden;
        }
        
        .sidebar-toggle {
            background: rgba(255, 255, 255, 0.1);
            border: none;
            color: #fff;
            width: 36px;
            height: 36px;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s;
            flex-shrink: 0;
        }
        
        .sidebar-toggle:hover {
            background: rgba(255, 255, 255, 0.2);
        }
        
        .sidebar-toggle svg {
            width: 20px;
            height: 20px;
            transition: transform 0.3s;
        }
        
        .sidebar.collapsed .sidebar-toggle svg {
            transform: rotate(180deg);
        }
        
        .sidebar-nav {
            padding: 16px 12px;
        }
        
        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            margin-bottom: 4px;
            border-radius: 10px;
            color: rgba(255, 255, 255, 0.8);
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s;
            cursor: pointer;
            text-decoration: none;
            white-space: nowrap;
        }
        
        .nav-item:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
        }
        
        .nav-item.active {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.2), rgba(96, 165, 250, 0.2));
            color: #fff;
            border-left: 3px solid #60a5fa;
        }
        
        .nav-item-icon {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .nav-item-text {
            opacity: 1;
            transition: opacity 0.2s;
        }
        
        .sidebar.collapsed .nav-item-text {
            opacity: 0;
            width: 0;
            overflow: hidden;
        }
        
        .nav-submenu {
            margin-left: 20px;
            margin-top: 4px;
            padding-left: 12px;
            border-left: 2px solid rgba(255, 255, 255, 0.2);
        }
        
        .nav-submenu-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 6px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            font-size: 14px;
            transition: all 0.2s;
        }
        
        .nav-submenu-item:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
        }
        
        .nav-submenu-item.active {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.2), rgba(96, 165, 250, 0.2));
            color: #fff;
        }
        
        .nav-submenu-icon {
            width: 16px;
            height: 16px;
            flex-shrink: 0;
        }
        
        .nav-submenu-text {
            opacity: 1;
            transition: opacity 0.2s;
        }
        
        .sidebar.collapsed .nav-submenu {
            display: none;
        }
        
        .nav-item.has-submenu.active ~ .nav-submenu {
            display: block;
        }
        
        .sidebar-footer {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 16px 12px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .logout-btn {
            width: 100%;
            border: none;
            background: rgba(239, 68, 68, 0.2);
            color: #fff;
            border-radius: 10px;
            padding: 12px 16px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .logout-btn:hover {
            background: rgba(239, 68, 68, 0.3);
        }
        
        .logout-btn span {
            opacity: 1;
            transition: opacity 0.2s;
        }
        
        .sidebar.collapsed .logout-btn {
            padding: 12px;
            justify-content: center;
        }
        
        .sidebar.collapsed .logout-btn span {
            opacity: 0;
            width: 0;
            overflow: hidden;
        }
        
        .main-content {
            flex: 1;
            margin-left: 280px;
            transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            min-height: 100vh;
        }
        
        .sidebar.collapsed ~ .main-content {
            margin-left: 40px;
        }
        
        .topbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--border);
            padding: 16px 24px;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 24px;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 16px;
            }
        }
        
        /* Mobile */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.mobile-open {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .sidebar-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.5);
                z-index: 999;
            }
            
            .sidebar-overlay.active {
                display: block;
            }
            
            .mobile-menu-btn {
                display: flex;
                align-items: center;
                justify-content: center;
                width: 40px;
                height: 40px;
                background: var(--primary);
                color: #fff;
                border: none;
                border-radius: 8px;
                cursor: pointer;
            }
        }
        
        @media (min-width: 769px) {
            .mobile-menu-btn {
                display: none;
            }
        }

        .title {
            margin: 0;
            font-size: 28px;
            font-weight: 750;
        }
        .subtitle {
            margin-top: 6px;
            color: var(--muted);
            font-size: 14px;
        }

        .grid {
            display: grid;
            gap: 16px;
        }
        .grid.cols-4 { grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); }
        .grid.cols-3 { grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); }
        .grid.cols-2 { grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); }

        .card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(15, 23, 42, 0.06);
            padding: 16px;
        }
        .card h3, .card h4 { margin: 0 0 8px; }
        .meta {
            font-size: 13px;
            color: var(--muted);
            margin-top: 6px;
        }
        .value {
            font-size: 32px;
            font-weight: 700;
            margin-top: 8px;
        }

        .badge {
            display: inline-block;
            border-radius: 999px;
            font-size: 12px;
            padding: 4px 10px;
            background: #e2e8f0;
            color: #334155;
            font-weight: 600;
        }
        .badge.ok { background: #dbeafe; color: #1e40af; }
        .badge.warning { background: #fef3c7; color: #92400e; }
        .badge.danger { background: #fee2e2; color: #991b1b; }

        .rental-card.warning { border-color: #fbbf24; background: var(--warning); }
        .rental-card.danger { border-color: #ef4444; background: var(--danger); }
        .rental-card.service-done { 
            border-color: #10b981; 
            background: #ecfdf5; 
            border-width: 2px;
        }
        .rental-card.warning .meta { color: var(--warning-text); }
        .rental-card.danger .meta { color: var(--danger-text); }
        .rental-card.service-done .meta { color: #065f46; }
        .rental-card.service-done h4 { color: #047857; }

        form.inline { display: inline; }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 12px;
            margin-top: 14px;
        }
        label {
            display: block;
            font-size: 12px;
            color: #334155;
            margin-bottom: 6px;
        }
        input, select {
            width: 100%;
            border: 1px solid #d4dae3;
            border-radius: 10px;
            font-size: 14px;
            padding: 10px 12px;
            background: #fff;
        }
        input:focus, select:focus {
            outline: 2px solid #bfdbfe;
            border-color: #60a5fa;
        }
        .btn {
            border: 0;
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 14px;
            cursor: pointer;
            font-weight: 600;
        }
        .btn-primary { background: var(--primary); color: #fff; }
        .btn-primary:hover { background: var(--primary-dark); }
        .btn-muted { background: #e2e8f0; color: #1e293b; }
        .btn-danger { background: #ef4444; color: #fff; }

        .table-wrap { overflow: auto; margin-top: 10px; }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        th, td {
            text-align: left;
            border-bottom: 1px solid #ecf0f5;
            padding: 10px 8px;
            vertical-align: top;
        }
        th {
            color: #475569;
            font-weight: 700;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .02em;
        }
        .flash {
            margin: 16px 0;
            padding: 12px 14px;
            border-radius: 10px;
            font-size: 14px;
        }
        .flash.ok { background: #dcfce7; color: #166534; }
        .flash.error { background: #fee2e2; color: #991b1b; }

        /* Modal para imagens - Mobile First */
        .image-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.95);
            z-index: 9999;
            padding: 0;
            overflow: hidden;
            touch-action: pan-y;
        }
        .image-modal.active {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        .image-modal-content {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            padding: 20px;
        }
        .image-modal img {
            max-width: 100%;
            max-height: 100%;
            width: auto;
            height: auto;
            object-fit: contain;
            border-radius: 8px;
            user-select: none;
            -webkit-user-select: none;
            -webkit-touch-callout: none;
        }
        .image-modal-close {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(255, 255, 255, 0.9);
            border: none;
            border-radius: 50%;
            width: 44px;
            height: 44px;
            font-size: 24px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #1f2937;
            font-weight: bold;
            z-index: 10000;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            touch-action: manipulation;
        }
        .image-modal-close:hover {
            background: rgba(255, 255, 255, 1);
        }
        .image-modal-close:active {
            transform: scale(0.95);
        }
        .clickable-image {
            cursor: pointer;
            transition: transform 0.2s, opacity 0.2s;
            touch-action: manipulation;
        }
        .clickable-image:active {
            transform: scale(0.98);
            opacity: 0.9;
        }
        @media (max-width: 768px) {
            .image-modal-close {
                top: 10px;
                right: 10px;
                width: 40px;
                height: 40px;
                font-size: 20px;
            }
            .image-modal-content {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
@auth
<div class="app-wrapper">
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="brand">
                <div class="brand-badge">TR</div>
                <span class="brand-text">Top Rio</span>
            </div>
            <button class="sidebar-toggle" onclick="toggleSidebar()" aria-label="Toggle sidebar">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>
        </div>
        
        <nav class="sidebar-nav">
            <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <span class="nav-item-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                </span>
                <span class="nav-item-text">Painel</span>
            </a>
            
            <a href="{{ route('rentals.index') }}" class="nav-item {{ request()->routeIs('rentals.*') ? 'active' : '' }}">
                <span class="nav-item-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </span>
                <span class="nav-item-text">Locacao</span>
            </a>
            
            <a href="{{ route('clients.index') }}" class="nav-item {{ request()->routeIs('clients.*') ? 'active' : '' }}">
                <span class="nav-item-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </span>
                <span class="nav-item-text">Clientes</span>
            </a>
            
            <a href="{{ route('depots.index') }}" class="nav-item {{ request()->routeIs('depots.*') ? 'active' : '' }}">
                <span class="nav-item-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </span>
                <span class="nav-item-text">Depositos</span>
            </a>
            
            <a href="{{ route('containers.index') }}" class="nav-item {{ request()->routeIs('containers.*') ? 'active' : '' }}">
                <span class="nav-item-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </span>
                <span class="nav-item-text">Cacambas</span>
            </a>
            
            @if(auth()->check() && isset(auth()->user()->role) && auth()->user()->isAdmin())
                <div class="nav-item has-submenu {{ (request()->routeIs('expenses.*') || request()->routeIs('receivables.*')) ? 'active' : '' }}" id="financeMenu" style="cursor: pointer;">
                    <span class="nav-item-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </span>
                    <span class="nav-item-text">Financeiro</span>
                </div>
                <div class="nav-submenu" id="financeSubmenu" style="display: {{ (request()->routeIs('expenses.*') || request()->routeIs('receivables.*')) ? 'block' : 'none' }};">
                    <a href="{{ route('expenses.index') }}" class="nav-submenu-item {{ request()->routeIs('expenses.*') ? 'active' : '' }}">
                        <svg class="nav-submenu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span class="nav-submenu-text">Planilha de cotação e retirada</span>
                    </a>
                    <a href="{{ route('receivables.index') }}" class="nav-submenu-item {{ request()->routeIs('receivables.*') ? 'active' : '' }}">
                        <svg class="nav-submenu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span class="nav-submenu-text">Contas a Receber</span>
                    </a>
                </div>
                
                <a href="{{ route('users.index') }}" class="nav-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
                    <span class="nav-item-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </span>
                    <span class="nav-item-text">Usuarios</span>
                </a>
            @endif
        </nav>
        
        <div class="sidebar-footer">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="logout-btn" type="submit">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    <span class="nav-item-text">Sair</span>
                </button>
            </form>
        </div>
    </aside>
    
    <!-- Overlay para mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeMobileSidebar()"></div>
    
    <!-- Conteúdo Principal -->
    <div class="main-content">
        <header class="topbar">
            <button class="mobile-menu-btn" onclick="toggleMobileSidebar()" aria-label="Menu">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="24" height="24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
        </header>
        
        <main class="container">
            @if (session('status'))
                <div class="flash ok">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="flash error">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>
@endauth

@guest
<main class="container">
    @if (session('status'))
        <div class="flash ok">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="flash error">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    @yield('content')
</main>
@endguest

<!-- Modal para visualização de imagens -->
<div id="imageModal" class="image-modal" onclick="closeImageModal(event)">
    <div class="image-modal-content">
        <button class="image-modal-close" onclick="closeImageModal()" aria-label="Fechar">×</button>
        <img id="modalImage" src="" alt="Imagem ampliada">
    </div>
</div>

<script>
    function openImageModal(imageSrc) {
        const modal = document.getElementById('imageModal');
        const modalImg = document.getElementById('modalImage');
        modalImg.src = imageSrc;
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeImageModal(event) {
        const modal = document.getElementById('imageModal');
        const modalContent = document.querySelector('.image-modal-content');
        
        // Fechar se clicar no botão ou fora da imagem
        if (!event || event.target === modal || event.target.classList.contains('image-modal-close')) {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }
    }

    // Fechar com ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeImageModal();
        }
    });
    
    // Toggle submenu financeiro
    document.addEventListener('DOMContentLoaded', function() {
        const financeNavItem = document.getElementById('financeMenu');
        const financeSubmenu = document.getElementById('financeSubmenu');
        
        if (financeNavItem && financeSubmenu) {
            financeNavItem.addEventListener('click', function(e) {
                // Não fazer toggle se clicar em um link do submenu
                if (e.target.closest('.nav-submenu-item')) {
                    return;
                }
                
                e.preventDefault();
                e.stopPropagation();
                
                const isVisible = financeSubmenu.style.display !== 'none';
                financeSubmenu.style.display = isVisible ? 'none' : 'block';
                
                // Adicionar/remover classe active
                if (!isVisible) {
                    financeNavItem.classList.add('active');
                } else {
                    // Só remover active se não estiver em uma rota financeira
                    if (!window.location.pathname.includes('/expenses') && 
                        !window.location.pathname.includes('/receivables')) {
                        financeNavItem.classList.remove('active');
                    }
                }
            });
        }
    });

    // Prevenir fechar ao clicar na imagem
    document.getElementById('modalImage')?.addEventListener('click', function(e) {
        e.stopPropagation();
    });

    // Sidebar Toggle
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        sidebar.classList.toggle('collapsed');
        localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
    }

    function toggleMobileSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        sidebar.classList.toggle('mobile-open');
        overlay.classList.toggle('active');
    }

    function closeMobileSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        sidebar.classList.remove('mobile-open');
        overlay.classList.remove('active');
    }

    // Restaurar estado da sidebar
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        if (sidebar && window.innerWidth > 768) {
            const collapsed = localStorage.getItem('sidebarCollapsed') === 'true';
            if (collapsed) {
                sidebar.classList.add('collapsed');
            }
        }
    });

    // Fechar sidebar no mobile ao clicar em um link (exceto menu financeiro que tem submenu)
    document.querySelectorAll('.nav-item').forEach(item => {
        // Não adicionar listener em itens com submenu (já tem seu próprio handler)
        if (!item.classList.contains('has-submenu')) {
            item.addEventListener('click', function() {
                if (window.innerWidth <= 768) {
                    closeMobileSidebar();
                }
            });
        }
    });
    
    // Fechar sidebar mobile ao clicar em links do submenu
    document.querySelectorAll('.nav-submenu-item').forEach(item => {
        item.addEventListener('click', function() {
            if (window.innerWidth <= 768) {
                closeMobileSidebar();
            }
        });
    });
</script>
</body>
</html>
