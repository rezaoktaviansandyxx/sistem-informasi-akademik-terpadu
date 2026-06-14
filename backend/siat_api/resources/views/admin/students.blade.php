@extends('layouts.admin')

@section('title', 'Data Mahasiswa')

@section('content')
    <div class="page-header">
        <h2>Data Mahasiswa</h2>
        <p class="muted">Daftar mahasiswa aktif yang tersimpan pada sistem akademik.</p>
    </div>

    <div class="panel">
        @if ($students->isEmpty())
            <div class="empty">Belum ada data mahasiswa.</div>
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>NIM</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Program Studi</th>
                            <th>Fakultas</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($students as $student)
                            <tr>
                                <td>{{ $student->nim }}</td>
                                <td>{{ $student->name }}</td>
                                <td>{{ $student->user?->email ?? '-' }}</td>
                                <td>{{ $student->studyProgram?->name ?? '-' }}</td>
                                <td>{{ $student->studyProgram?->faculty?->name ?? '-' }}</td>
                                <td><span class="badge">{{ $student->academic_status }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
