<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SIAT Admin')</title>
    <style>
        body { margin: 0; font-family: Arial, sans-serif; background: #f8fafc; color: #0f172a; }
        .shell { display: grid; grid-template-columns: 260px 1fr; min-height: 100vh; }
        .sidebar { background: #102a43; color: white; padding: 24px; }
        .sidebar h1 { font-size: 24px; margin: 0 0 24px; }
        .brand { display: block; color: white; text-decoration: none; margin-bottom: 24px; }
        .sidebar a { display: block; color: #dbeafe; text-decoration: none; margin-bottom: 10px; padding: 10px 12px; border-radius: 10px; }
        .sidebar a.active { background: #1d4ed8; color: white; }
        .content { padding: 24px; }
        .page-header { margin-bottom: 20px; }
        .card-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 20px; }
        .card { background: white; border: 1px solid #e2e8f0; border-radius: 18px; padding: 20px; }
        .panel { background: white; border: 1px solid #e2e8f0; border-radius: 18px; padding: 20px; margin-bottom: 16px; }
        .muted { color: #64748b; }
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 12px 10px; border-bottom: 1px solid #e2e8f0; vertical-align: top; }
        th { font-size: 13px; text-transform: uppercase; color: #64748b; }
        .badge { display: inline-block; padding: 4px 10px; border-radius: 999px; background: #dbeafe; color: #1e3a8a; font-size: 12px; }
        .empty { padding: 18px; border: 1px dashed #cbd5e1; border-radius: 12px; color: #64748b; }
    </style>
</head>
<body>
    <div class="shell">
        <aside class="sidebar">
            <a class="brand" href="{{ route('admin.dashboard') }}">
                <h1>SIAT Admin</h1>
            </a>
            <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">Dashboard</a>
            <a href="{{ route('admin.students') }}" class="{{ request()->routeIs('admin.students') ? 'active' : '' }}">Mahasiswa</a>
            <a href="{{ route('admin.lecturers') }}" class="{{ request()->routeIs('admin.lecturers') ? 'active' : '' }}">Dosen</a>
            <a href="{{ route('admin.approvals') }}" class="{{ request()->routeIs('admin.approvals') ? 'active' : '' }}">Approval</a>
            <a href="{{ route('admin.audit-logs') }}" class="{{ request()->routeIs('admin.audit-logs') ? 'active' : '' }}">Audit Log</a>
        </aside>
        <main class="content">
            @yield('content')
        </main>
    </div>
</body>
</html>
