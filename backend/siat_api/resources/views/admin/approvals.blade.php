@extends('layouts.admin')

@section('title', 'Approval')

@section('content')
    <div class="page-header">
        <h2>Approval</h2>
        <p class="muted">Daftar approval yang menunggu review atau sudah diputuskan.</p>
    </div>

    <div class="panel">
        @if ($approvals->isEmpty())
            <div class="empty">Belum ada data approval.</div>
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Judul</th>
                            <th>Tipe</th>
                            <th>Status</th>
                            <th>Catatan</th>
                            <th>Diputuskan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($approvals as $approval)
                            <tr>
                                <td>{{ $approval->title }}</td>
                                <td>{{ $approval->type }}</td>
                                <td><span class="badge">{{ $approval->status }}</span></td>
                                <td>{{ $approval->notes ?: '-' }}</td>
                                <td>{{ optional($approval->decided_at)?->format('d M Y H:i') ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
