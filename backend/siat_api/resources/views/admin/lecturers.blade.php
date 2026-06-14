@extends('layouts.admin')

@section('title', 'Data Dosen')

@section('content')
    <div class="page-header">
        <h2>Data Dosen</h2>
        <p class="muted">Daftar dosen yang terhubung dengan akun institusi.</p>
    </div>

    <div class="panel">
        @if ($lecturers->isEmpty())
            <div class="empty">Belum ada data dosen.</div>
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>NIDN</th>
                            <th>Nama</th>
                            <th>Email</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($lecturers as $lecturer)
                            <tr>
                                <td>{{ $lecturer->nidn }}</td>
                                <td>{{ $lecturer->name }}</td>
                                <td>{{ $lecturer->user?->email ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
