@extends('layouts.admin')

@section('title', 'Audit Log')

@section('content')
    <div class="page-header">
        <h2>Audit Log</h2>
        <p class="muted">Jejak perubahan data dan aktivitas penting pada SIAT.</p>
    </div>

    <div class="panel">
        <h3>Audit Trail</h3>
        @if ($auditLogs->isEmpty())
            <div class="empty">Belum ada audit trail.</div>
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Modul</th>
                            <th>Aksi</th>
                            <th>Objek</th>
                            <th>Pengguna</th>
                            <th>Waktu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($auditLogs as $audit)
                            <tr>
                                <td>{{ $audit->module }}</td>
                                <td>{{ $audit->action }}</td>
                                <td>{{ $audit->auditable_type }}#{{ $audit->auditable_id }}</td>
                                <td>{{ $audit->user?->name ?? 'System' }}</td>
                                <td>{{ optional($audit->created_at)?->format('d M Y H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="panel">
        <h3>Activity Logs</h3>
        @if ($activityLogs->isEmpty())
            <div class="empty">Belum ada activity log.</div>
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Modul</th>
                            <th>Aksi</th>
                            <th>Pengguna</th>
                            <th>Konteks</th>
                            <th>Waktu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($activityLogs as $activity)
                            <tr>
                                <td>{{ $activity->module }}</td>
                                <td>{{ $activity->action }}</td>
                                <td>{{ $activity->user?->name ?? 'System' }}</td>
                                <td>{{ is_array($activity->context) ? json_encode($activity->context) : '-' }}</td>
                                <td>{{ optional($activity->created_at)?->format('d M Y H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
