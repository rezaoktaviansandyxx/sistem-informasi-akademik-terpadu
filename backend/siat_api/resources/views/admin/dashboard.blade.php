@extends('layouts.admin')

@section('title', 'Dashboard Admin SIAT')

@section('content')
    <div class="page-header">
        <h2>Dashboard Admin Akademik</h2>
        <p class="muted">Ringkasan operasional, kualitas data, approval, dan aktivitas terbaru institusi.</p>
    </div>

    <div class="card-grid">
        <div class="card">
            <div class="muted">Approval Pending</div>
            <h3>{{ $stats['approvals_pending'] }}</h3>
        </div>
        <div class="card">
            <div class="muted">Mahasiswa</div>
            <h3>{{ $stats['students_total'] }}</h3>
        </div>
        <div class="card">
            <div class="muted">Dosen</div>
            <h3>{{ $stats['lecturers_total'] }}</h3>
        </div>
        <div class="card">
            <div class="muted">Audit Event</div>
            <h3>{{ $stats['audit_total'] }}</h3>
        </div>
    </div>

    <div class="panel">
        <h3>Approval Terbaru</h3>
        @if ($recentApprovals->isEmpty())
            <div class="empty">Belum ada data approval.</div>
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Judul</th>
                            <th>Tipe</th>
                            <th>Status</th>
                            <th>Dibuat</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentApprovals as $approval)
                            <tr>
                                <td>{{ $approval->title }}</td>
                                <td>{{ $approval->type }}</td>
                                <td><span class="badge">{{ $approval->status }}</span></td>
                                <td>{{ optional($approval->created_at)?->format('d M Y H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="panel">
        <h3>Aktivitas Terbaru</h3>
        @if ($recentActivities->isEmpty())
            <div class="empty">Belum ada activity log.</div>
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Modul</th>
                            <th>Aksi</th>
                            <th>Pengguna</th>
                            <th>Waktu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentActivities as $activity)
                            <tr>
                                <td>{{ $activity->module }}</td>
                                <td>{{ $activity->action }}</td>
                                <td>{{ $activity->user?->name ?? 'System' }}</td>
                                <td>{{ optional($activity->created_at)?->format('d M Y H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
