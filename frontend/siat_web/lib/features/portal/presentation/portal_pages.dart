import '../../../core/platform/download_helper.dart';

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/network/dio_provider.dart';
import '../../../shared/widgets/admin_collection_page.dart';
import '../../../shared/widgets/endpoint_page.dart';

class ForgotPasswordPage extends ConsumerStatefulWidget {
  const ForgotPasswordPage({super.key});

  @override
  ConsumerState<ForgotPasswordPage> createState() => _ForgotPasswordPageState();
}

class _ForgotPasswordPageState extends ConsumerState<ForgotPasswordPage> {
  final _emailController = TextEditingController(text: 'admin@siat.local');
  String? _token;
  bool _loading = false;

  @override
  void dispose() {
    _emailController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Center(
        child: ConstrainedBox(
          constraints: const BoxConstraints(maxWidth: 520),
          child: Card(
            margin: const EdgeInsets.all(24),
            child: Padding(
              padding: const EdgeInsets.all(24),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Lupa Password',
                    style: Theme.of(context).textTheme.headlineSmall,
                  ),
                  const SizedBox(height: 8),
                  const Text(
                    'Minta token reset password dari backend SIAT.',
                    style: TextStyle(color: Color(0xFF64748B)),
                  ),
                  const SizedBox(height: 20),
                  TextField(
                    controller: _emailController,
                    decoration:
                        const InputDecoration(labelText: 'Email institusi'),
                  ),
                  const SizedBox(height: 20),
                  SizedBox(
                    width: double.infinity,
                    child: FilledButton(
                      onPressed: _loading ? null : _submit,
                      child: const Text('Minta Token Reset'),
                    ),
                  ),
                  if (_token != null) ...[
                    const SizedBox(height: 20),
                    SelectableText(
                      'Token reset: $_token',
                      style: const TextStyle(fontWeight: FontWeight.w700),
                    ),
                  ],
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }

  Future<void> _submit() async {
    setState(() => _loading = true);

    try {
      final dio = ref.read(dioProvider);
      final response = await dio.post<Map<String, dynamic>>(
        '/auth/forgot-password',
        data: {'email': _emailController.text.trim()},
      );
      final data = response.data?['data'] as Map<String, dynamic>? ?? {};

      setState(() {
        _token = data['reset_token_preview'] != null
            ? '${data['reset_token_preview']}'
            : 'Token tidak ditampilkan. Gunakan email/reset channel yang terhubung.';
      });
    } catch (error) {
      if (!mounted) {
        return;
      }
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Gagal meminta token: $error')),
      );
    } finally {
      if (mounted) {
        setState(() => _loading = false);
      }
    }
  }
}

class ResetPasswordPage extends ConsumerStatefulWidget {
  const ResetPasswordPage({super.key});

  @override
  ConsumerState<ResetPasswordPage> createState() => _ResetPasswordPageState();
}

class _ResetPasswordPageState extends ConsumerState<ResetPasswordPage> {
  final _emailController = TextEditingController(text: 'admin@siat.local');
  final _tokenController = TextEditingController();
  final _passwordController = TextEditingController(text: 'password123');
  final _passwordConfirmationController =
      TextEditingController(text: 'password123');
  bool _loading = false;

  @override
  void dispose() {
    _emailController.dispose();
    _tokenController.dispose();
    _passwordController.dispose();
    _passwordConfirmationController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Center(
        child: ConstrainedBox(
          constraints: const BoxConstraints(maxWidth: 520),
          child: Card(
            margin: const EdgeInsets.all(24),
            child: Padding(
              padding: const EdgeInsets.all(24),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Reset Password',
                    style: Theme.of(context).textTheme.headlineSmall,
                  ),
                  const SizedBox(height: 20),
                  TextField(
                    controller: _emailController,
                    decoration: const InputDecoration(labelText: 'Email'),
                  ),
                  const SizedBox(height: 12),
                  TextField(
                    controller: _tokenController,
                    decoration: const InputDecoration(labelText: 'Token reset'),
                  ),
                  const SizedBox(height: 12),
                  TextField(
                    controller: _passwordController,
                    obscureText: true,
                    decoration:
                        const InputDecoration(labelText: 'Password baru'),
                  ),
                  const SizedBox(height: 12),
                  TextField(
                    controller: _passwordConfirmationController,
                    obscureText: true,
                    decoration:
                        const InputDecoration(labelText: 'Konfirmasi password'),
                  ),
                  const SizedBox(height: 20),
                  SizedBox(
                    width: double.infinity,
                    child: FilledButton(
                      onPressed: _loading ? null : _submit,
                      child: const Text('Reset Password'),
                    ),
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }

  Future<void> _submit() async {
    setState(() => _loading = true);

    try {
      final dio = ref.read(dioProvider);
      await dio.post<Map<String, dynamic>>(
        '/auth/reset-password',
        data: {
          'email': _emailController.text.trim(),
          'token': _tokenController.text.trim(),
          'password': _passwordController.text,
          'password_confirmation': _passwordConfirmationController.text,
        },
      );

      if (!mounted) {
        return;
      }

      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Password berhasil direset.')),
      );
    } catch (error) {
      if (!mounted) {
        return;
      }
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Gagal reset password: $error')),
      );
    } finally {
      if (mounted) {
        setState(() => _loading = false);
      }
    }
  }
}

class KhsPage extends StatelessWidget {
  const KhsPage({super.key});

  @override
  Widget build(BuildContext context) => const EndpointPage(
        title: 'KHS Mahasiswa',
        description:
            'Menampilkan hasil studi per semester, IPS, dan status finalisasi.',
        endpoint: '/student/khs',
      );
}

class TranscriptPage extends StatelessWidget {
  const TranscriptPage({super.key});

  @override
  Widget build(BuildContext context) => const EndpointPage(
        title: 'Transkrip Nilai',
        description: 'Menampilkan transkrip kumulatif dan IPK mahasiswa.',
        endpoint: '/student/transcript',
      );
}

class SchedulePage extends StatelessWidget {
  const SchedulePage({super.key});

  @override
  Widget build(BuildContext context) => const EndpointPage(
        title: 'Jadwal Kuliah',
        description:
            'Menampilkan jadwal perkuliahan aktif hasil KRS mahasiswa.',
        endpoint: '/student/schedule',
      );
}

class StudentAttendancePage extends StatelessWidget {
  const StudentAttendancePage({super.key});

  @override
  Widget build(BuildContext context) => const EndpointPage(
        title: 'Presensi Mahasiswa',
        description:
            'Menampilkan presensi per mata kuliah dan rekap kehadiran.',
        endpoint: '/student/attendance',
      );
}

class LecturerClassesPage extends StatelessWidget {
  const LecturerClassesPage({super.key});

  @override
  Widget build(BuildContext context) => const EndpointPage(
        title: 'Kelas Dosen',
        description:
            'Menampilkan kelas aktif, beban mengajar, dan jadwal dosen.',
        endpoint: '/lecturer/classes',
      );
}

class LecturerAttendancePage extends StatelessWidget {
  const LecturerAttendancePage({super.key});

  @override
  Widget build(BuildContext context) => const AdminCollectionPage(
        title: 'Presensi Mengajar',
        description:
            'Mencatat pertemuan mengajar dosen sekaligus menampilkan riwayat pertemuan.',
        listEndpoint: '/lecturer/attendances',
        submitEndpoint: '/lecturer/attendances',
        submitButtonLabel: 'Catat Pertemuan',
        fields: [
          AdminFieldConfig(
            name: 'academic_class_id',
            label: 'Kelas',
            type: AdminFieldType.select,
            optionsEndpoint: '/lecturer/classes',
            optionValueKey: 'class_id',
            optionLabelKey: 'course_name',
          ),
          AdminFieldConfig(
            name: 'meeting_no',
            label: 'Pertemuan Ke',
            type: AdminFieldType.number,
          ),
          AdminFieldConfig(
            name: 'topic',
            label: 'Topik',
            type: AdminFieldType.text,
          ),
          AdminFieldConfig(
            name: 'held_on',
            label: 'Tanggal',
            type: AdminFieldType.date,
          ),
          AdminFieldConfig(
            name: 'status',
            label: 'Status',
            type: AdminFieldType.select,
            staticOptions: [
              AdminFieldOption(value: 'held', label: 'Held'),
              AdminFieldOption(value: 'rescheduled', label: 'Rescheduled'),
              AdminFieldOption(value: 'cancelled', label: 'Cancelled'),
            ],
          ),
        ],
      );
}

class LecturerSummaryPage extends StatelessWidget {
  const LecturerSummaryPage({super.key});

  @override
  Widget build(BuildContext context) => const EndpointPage(
        title: 'Rekap Mengajar',
        description:
            'Menampilkan ringkasan beban mengajar dan total pertemuan.',
        endpoint: '/lecturer/teaching-summary',
      );
}

class AnnouncementsPage extends StatelessWidget {
  const AnnouncementsPage({super.key});

  @override
  Widget build(BuildContext context) => const AdminCollectionPage(
        title: 'Pengumuman',
        description:
            'Kelola pengumuman akademik dan langsung publish ke portal institusi.',
        listEndpoint: '/announcements',
        submitEndpoint: '/announcements',
        fields: [
          AdminFieldConfig(
            name: 'title',
            label: 'Judul',
            type: AdminFieldType.text,
          ),
          AdminFieldConfig(
            name: 'content',
            label: 'Konten',
            type: AdminFieldType.multiline,
          ),
          AdminFieldConfig(
            name: 'status',
            label: 'Status',
            type: AdminFieldType.select,
            staticOptions: [
              AdminFieldOption(value: 'draft', label: 'Draft'),
              AdminFieldOption(value: 'published', label: 'Published'),
              AdminFieldOption(value: 'archived', label: 'Archived'),
            ],
          ),
        ],
      );
}

class AcademicCalendarPage extends StatelessWidget {
  const AcademicCalendarPage({super.key});

  @override
  Widget build(BuildContext context) => const AdminCollectionPage(
        title: 'Kalender Akademik',
        description:
            'Buat event kalender akademik untuk periode KRS, UTS, UAS, dan agenda institusi.',
        listEndpoint: '/academic-calendar',
        submitEndpoint: '/academic-calendar',
        fields: [
          AdminFieldConfig(
            name: 'title',
            label: 'Judul Event',
            type: AdminFieldType.text,
          ),
          AdminFieldConfig(
            name: 'category',
            label: 'Kategori',
            type: AdminFieldType.text,
          ),
          AdminFieldConfig(
            name: 'start_date',
            label: 'Mulai',
            type: AdminFieldType.date,
          ),
          AdminFieldConfig(
            name: 'end_date',
            label: 'Selesai',
            type: AdminFieldType.date,
          ),
          AdminFieldConfig(
            name: 'status',
            label: 'Status',
            type: AdminFieldType.select,
            staticOptions: [
              AdminFieldOption(value: 'draft', label: 'Draft'),
              AdminFieldOption(value: 'published', label: 'Published'),
              AdminFieldOption(value: 'archived', label: 'Archived'),
            ],
          ),
          AdminFieldConfig(
            name: 'notes',
            label: 'Catatan',
            type: AdminFieldType.multiline,
            required: false,
          ),
        ],
      );
}

class AcademicLettersPage extends StatelessWidget {
  const AcademicLettersPage({super.key});

  @override
  Widget build(BuildContext context) => const AdminCollectionPage(
        title: 'Surat Akademik',
        description:
            'Kelola pengajuan surat akademik mahasiswa dari form operasional admin.',
        listEndpoint: '/academic-letters',
        submitEndpoint: '/academic-letters',
        fields: [
          AdminFieldConfig(
            name: 'student_id',
            label: 'Mahasiswa',
            type: AdminFieldType.select,
            optionsEndpoint: '/master/students',
            optionValueKey: 'id',
            optionLabelKey: 'name',
          ),
          AdminFieldConfig(
            name: 'type',
            label: 'Jenis Surat',
            type: AdminFieldType.text,
          ),
          AdminFieldConfig(
            name: 'title',
            label: 'Judul Surat',
            type: AdminFieldType.text,
          ),
          AdminFieldConfig(
            name: 'status',
            label: 'Status',
            type: AdminFieldType.select,
            staticOptions: [
              AdminFieldOption(value: 'requested', label: 'Requested'),
              AdminFieldOption(value: 'verified', label: 'Verified'),
              AdminFieldOption(value: 'approved', label: 'Approved'),
              AdminFieldOption(value: 'rejected', label: 'Rejected'),
              AdminFieldOption(value: 'issued', label: 'Issued'),
            ],
          ),
          AdminFieldConfig(
            name: 'notes',
            label: 'Catatan',
            type: AdminFieldType.multiline,
            required: false,
          ),
        ],
      );
}

class VerificationsPage extends StatelessWidget {
  const VerificationsPage({super.key});

  @override
  Widget build(BuildContext context) => const AdminCollectionPage(
        title: 'Verifikasi Data',
        description:
            'Buat dan kelola permintaan verifikasi perubahan data akademik.',
        listEndpoint: '/verifications',
        submitEndpoint: '/verifications',
        fields: [
          AdminFieldConfig(
            name: 'type',
            label: 'Tipe Verifikasi',
            type: AdminFieldType.text,
          ),
          AdminFieldConfig(
            name: 'subject_type',
            label: 'Tipe Subjek',
            type: AdminFieldType.text,
          ),
          AdminFieldConfig(
            name: 'subject_id',
            label: 'ID Subjek',
            type: AdminFieldType.text,
          ),
          AdminFieldConfig(
            name: 'status',
            label: 'Status',
            type: AdminFieldType.select,
            staticOptions: [
              AdminFieldOption(value: 'pending', label: 'Pending'),
              AdminFieldOption(value: 'approved', label: 'Approved'),
              AdminFieldOption(value: 'rejected', label: 'Rejected'),
            ],
          ),
          AdminFieldConfig(
            name: 'old_payload',
            label: 'Payload Lama',
            type: AdminFieldType.json,
            required: false,
          ),
          AdminFieldConfig(
            name: 'new_payload',
            label: 'Payload Baru',
            type: AdminFieldType.json,
            required: false,
          ),
          AdminFieldConfig(
            name: 'evidence_url',
            label: 'URL Bukti',
            type: AdminFieldType.text,
            required: false,
          ),
          AdminFieldConfig(
            name: 'notes',
            label: 'Catatan',
            type: AdminFieldType.multiline,
            required: false,
          ),
        ],
      );
}

class ApprovalsPage extends ConsumerStatefulWidget {
  const ApprovalsPage({super.key});

  @override
  ConsumerState<ApprovalsPage> createState() => _ApprovalsPageState();
}

class _ApprovalsPageState extends ConsumerState<ApprovalsPage> {
  String? _selectedApprovalId;
  String _decision = 'approved';
  final _notesController = TextEditingController();
  bool _loading = true;
  bool _submitting = false;
  List<Map<String, dynamic>> _approvals = const [];

  @override
  void initState() {
    super.initState();
    _load();
  }

  @override
  void dispose() {
    _notesController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text('Approval Queue',
            style: Theme.of(context).textTheme.headlineMedium),
        const SizedBox(height: 8),
        const Text(
          'Admin dapat memilih approval dan langsung memberi keputusan dari halaman ini.',
          style: TextStyle(color: Color(0xFF64748B)),
        ),
        const SizedBox(height: 24),
        Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Expanded(
              child: Card(
                child: Padding(
                  padding: const EdgeInsets.all(20),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        'Form Keputusan',
                        style: TextStyle(
                            fontSize: 18, fontWeight: FontWeight.w700),
                      ),
                      const SizedBox(height: 16),
                      DropdownButtonFormField<String>(
                        value: _selectedApprovalId,
                        decoration:
                            const InputDecoration(labelText: 'Approval'),
                        items: _approvals
                            .map(
                              (item) => DropdownMenuItem<String>(
                                value: '${item['id']}',
                                child: Text('${item['title']}'),
                              ),
                            )
                            .toList(),
                        onChanged: (value) =>
                            setState(() => _selectedApprovalId = value),
                      ),
                      const SizedBox(height: 12),
                      DropdownButtonFormField<String>(
                        value: _decision,
                        decoration:
                            const InputDecoration(labelText: 'Keputusan'),
                        items: const [
                          DropdownMenuItem(
                              value: 'approved', child: Text('Approved')),
                          DropdownMenuItem(
                              value: 'rejected', child: Text('Rejected')),
                          DropdownMenuItem(
                              value: 'needs_revision',
                              child: Text('Needs Revision')),
                        ],
                        onChanged: (value) =>
                            setState(() => _decision = value ?? 'approved'),
                      ),
                      const SizedBox(height: 12),
                      TextField(
                        controller: _notesController,
                        minLines: 3,
                        maxLines: 5,
                        decoration: const InputDecoration(labelText: 'Catatan'),
                      ),
                      const SizedBox(height: 16),
                      FilledButton(
                        onPressed: _loading || _submitting ? null : _submit,
                        child: const Text('Kirim Keputusan'),
                      ),
                    ],
                  ),
                ),
              ),
            ),
            const SizedBox(width: 16),
            Expanded(
              flex: 2,
              child: Card(
                child: Padding(
                  padding: const EdgeInsets.all(20),
                  child: _loading
                      ? const Center(child: CircularProgressIndicator())
                      : Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Row(
                              children: [
                                const Expanded(
                                  child: Text(
                                    'Daftar Approval',
                                    style: TextStyle(
                                        fontSize: 18,
                                        fontWeight: FontWeight.w700),
                                  ),
                                ),
                                OutlinedButton.icon(
                                  onPressed: _load,
                                  icon: const Icon(Icons.refresh),
                                  label: const Text('Refresh'),
                                ),
                              ],
                            ),
                            const SizedBox(height: 12),
                            for (final item in _approvals)
                              ListTile(
                                contentPadding: EdgeInsets.zero,
                                title: Text('${item['title']}'),
                                subtitle: Text(
                                    'Tipe: ${item['type']} · Status: ${item['status']}'),
                              ),
                          ],
                        ),
                ),
              ),
            ),
          ],
        ),
      ],
    );
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    try {
      final response =
          await ref.read(dioProvider).get<Map<String, dynamic>>('/approvals');
      final data = response.data?['data'];
      final items = data is List ? data : const <dynamic>[];
      _approvals = items.whereType<Map<String, dynamic>>().toList();
      _selectedApprovalId ??=
          _approvals.isNotEmpty ? '${_approvals.first['id']}' : null;
    } finally {
      if (mounted) {
        setState(() => _loading = false);
      }
    }
  }

  Future<void> _submit() async {
    if (_selectedApprovalId == null || _selectedApprovalId!.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Pilih approval terlebih dahulu.')),
      );
      return;
    }

    setState(() => _submitting = true);
    try {
      await ref.read(dioProvider).post<Map<String, dynamic>>(
        '/approvals/$_selectedApprovalId/decision',
        data: {
          'decision': _decision,
          'notes': _notesController.text.trim(),
        },
      );
      _notesController.clear();
      await _load();
      if (!mounted) {
        return;
      }
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Keputusan approval berhasil dikirim.')),
      );
    } catch (error) {
      if (!mounted) {
        return;
      }
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Gagal mengirim keputusan: $error')),
      );
    } finally {
      if (mounted) {
        setState(() => _submitting = false);
      }
    }
  }
}

class ReportsPage extends ConsumerStatefulWidget {
  const ReportsPage({super.key});

  @override
  ConsumerState<ReportsPage> createState() => _ReportsPageState();
}

class _ReportsPageState extends ConsumerState<ReportsPage> {
  late Future<Map<String, dynamic>> _future;

  @override
  void initState() {
    super.initState();
    _future = _load();
  }

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text('Laporan Institusi',
            style: Theme.of(context).textTheme.headlineMedium),
        const SizedBox(height: 8),
        const Text(
          'Ringkasan KPI akademik dan akses ekspor manajerial dalam satu panel.',
          style: TextStyle(color: Color(0xFF64748B)),
        ),
        const SizedBox(height: 24),
        FutureBuilder<Map<String, dynamic>>(
          future: _future,
          builder: (context, snapshot) {
            if (snapshot.connectionState == ConnectionState.waiting) {
              return const Center(child: CircularProgressIndicator());
            }

            if (snapshot.hasError) {
              return Card(
                child: Padding(
                  padding: const EdgeInsets.all(20),
                  child: Text('Gagal memuat laporan: ${snapshot.error}'),
                ),
              );
            }

            final response = snapshot.data ?? const {};
            final data = response['data'] as Map<String, dynamic>? ?? const {};
            final summary =
                data['summary'] as Map<String, dynamic>? ?? const {};
            final highlights =
                (data['highlights'] as List<dynamic>? ?? const [])
                    .whereType<Map<String, dynamic>>()
                    .toList();
            final exports = (data['exports'] as List<dynamic>? ?? const [])
                .whereType<Map<String, dynamic>>()
                .toList();

            return Column(
              children: [
                Wrap(
                  spacing: 16,
                  runSpacing: 16,
                  children: summary.entries
                      .map(
                        (entry) => SizedBox(
                          width: 220,
                          child: Card(
                            child: Padding(
                              padding: const EdgeInsets.all(18),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    entry.key.replaceAll('_', ' '),
                                    style: const TextStyle(
                                        color: Color(0xFF64748B)),
                                  ),
                                  const SizedBox(height: 8),
                                  Text(
                                    '${entry.value}',
                                    style: Theme.of(context)
                                        .textTheme
                                        .headlineSmall
                                        ?.copyWith(
                                          fontWeight: FontWeight.w700,
                                        ),
                                  ),
                                ],
                              ),
                            ),
                          ),
                        ),
                      )
                      .toList(),
                ),
                const SizedBox(height: 16),
                Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Expanded(
                      child: Card(
                        child: Padding(
                          padding: const EdgeInsets.all(20),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const Text(
                                'Insight Operasional',
                                style: TextStyle(
                                    fontSize: 18, fontWeight: FontWeight.w700),
                              ),
                              const SizedBox(height: 16),
                              for (final item in highlights)
                                ListTile(
                                  contentPadding: EdgeInsets.zero,
                                  leading: Icon(
                                    '${item['tone']}' == 'positive'
                                        ? Icons.trending_up_outlined
                                        : Icons.error_outline,
                                    color: '${item['tone']}' == 'positive'
                                        ? const Color(0xFF15803D)
                                        : const Color(0xFFB45309),
                                  ),
                                  title: Text('${item['label']}'),
                                  subtitle: Text('${item['value']}'),
                                ),
                            ],
                          ),
                        ),
                      ),
                    ),
                    const SizedBox(width: 16),
                    Expanded(
                      child: Card(
                        child: Padding(
                          padding: const EdgeInsets.all(20),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Row(
                                children: [
                                  const Expanded(
                                    child: Text(
                                      'Ekspor Laporan',
                                      style: TextStyle(
                                          fontSize: 18,
                                          fontWeight: FontWeight.w700),
                                    ),
                                  ),
                                  OutlinedButton.icon(
                                    onPressed: _refresh,
                                    icon: const Icon(Icons.refresh),
                                    label: const Text('Refresh'),
                                  ),
                                ],
                              ),
                              const SizedBox(height: 16),
                              for (final item in exports)
                                Container(
                                  margin: const EdgeInsets.only(bottom: 12),
                                  padding: const EdgeInsets.all(16),
                                  decoration: BoxDecoration(
                                    color: const Color(0xFFF8FAFC),
                                    borderRadius: BorderRadius.circular(16),
                                    border: Border.all(
                                        color: const Color(0xFFE2E8F0)),
                                  ),
                                  child: Row(
                                    children: [
                                      Expanded(
                                        child: Column(
                                          crossAxisAlignment:
                                              CrossAxisAlignment.start,
                                          children: [
                                            Text(
                                              '${item['name']}',
                                              style: const TextStyle(
                                                  fontWeight: FontWeight.w700),
                                            ),
                                            const SizedBox(height: 4),
                                            Text(
                                                'Format: ${item['type']} · Status: ${item['status']}'),
                                          ],
                                        ),
                                      ),
                                      const SizedBox(width: 12),
                                      FilledButton.tonalIcon(
                                        onPressed: () => _download(
                                            '${item['download_url'] ?? ''}'),
                                        icon:
                                            const Icon(Icons.download_outlined),
                                        label: const Text('Unduh'),
                                      ),
                                    ],
                                  ),
                                ),
                            ],
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
              ],
            );
          },
        ),
      ],
    );
  }

  Future<Map<String, dynamic>> _load() async {
    final response =
        await ref.read(dioProvider).get<Map<String, dynamic>>('/reports');
    return response.data ?? const {};
  }

  void _refresh() {
    setState(() {
      _future = _load();
    });
  }

  void _download(String path) {
    if (path.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Link unduhan tidak tersedia.')),
      );
      return;
    }

    final baseUrl = ref.read(dioProvider).options.baseUrl;
    final url = '${baseUrl.replaceFirst('/api/v1', '')}/api/v1$path';
    triggerBrowserDownload(url);
  }
}

class AuditTrailPage extends StatelessWidget {
  const AuditTrailPage({super.key});

  @override
  Widget build(BuildContext context) => const EndpointPage(
        title: 'Audit Trail',
        description: 'Menampilkan jejak perubahan data dan audit institusi.',
        endpoint: '/audit-trail',
      );
}

class ActivityLogsPage extends StatelessWidget {
  const ActivityLogsPage({super.key});

  @override
  Widget build(BuildContext context) => const EndpointPage(
        title: 'Activity Logs',
        description: 'Menampilkan log aktivitas operasional sistem.',
        endpoint: '/activity-logs',
      );
}

class DepartmentsPage extends StatelessWidget {
  const DepartmentsPage({super.key});

  @override
  Widget build(BuildContext context) => const AdminCollectionPage(
        title: 'Jurusan',
        description: 'Kelola jurusan per fakultas langsung dari panel admin.',
        listEndpoint: '/master/departments',
        submitEndpoint: '/master/departments',
        fields: [
          AdminFieldConfig(
            name: 'faculty_id',
            label: 'Fakultas',
            type: AdminFieldType.select,
            optionsEndpoint: '/master/faculties',
            optionValueKey: 'id',
            optionLabelKey: 'name',
          ),
          AdminFieldConfig(
            name: 'code',
            label: 'Kode Jurusan',
            type: AdminFieldType.text,
          ),
          AdminFieldConfig(
            name: 'name',
            label: 'Nama Jurusan',
            type: AdminFieldType.text,
          ),
        ],
      );
}

class CurriculaPage extends StatelessWidget {
  const CurriculaPage({super.key});

  @override
  Widget build(BuildContext context) => const AdminCollectionPage(
        title: 'Kurikulum',
        description:
            'Kelola kurikulum aktif dan total SKS untuk program studi.',
        listEndpoint: '/master/curricula',
        submitEndpoint: '/master/curricula',
        fields: [
          AdminFieldConfig(
            name: 'study_program_id',
            label: 'Program Studi',
            type: AdminFieldType.select,
            optionsEndpoint: '/master/study-programs',
            optionValueKey: 'id',
            optionLabelKey: 'name',
          ),
          AdminFieldConfig(
            name: 'code',
            label: 'Kode Kurikulum',
            type: AdminFieldType.text,
          ),
          AdminFieldConfig(
            name: 'name',
            label: 'Nama Kurikulum',
            type: AdminFieldType.text,
          ),
          AdminFieldConfig(
            name: 'total_credits',
            label: 'Total SKS',
            type: AdminFieldType.number,
          ),
          AdminFieldConfig(
            name: 'is_active',
            label: 'Aktif',
            type: AdminFieldType.boolean,
          ),
        ],
      );
}

class RoomsPage extends StatelessWidget {
  const RoomsPage({super.key});

  @override
  Widget build(BuildContext context) => const AdminCollectionPage(
        title: 'Ruangan',
        description: 'Kelola ruangan dan kapasitas untuk perkuliahan.',
        listEndpoint: '/master/rooms',
        submitEndpoint: '/master/rooms',
        fields: [
          AdminFieldConfig(
            name: 'code',
            label: 'Kode Ruangan',
            type: AdminFieldType.text,
          ),
          AdminFieldConfig(
            name: 'name',
            label: 'Nama Ruangan',
            type: AdminFieldType.text,
          ),
          AdminFieldConfig(
            name: 'building',
            label: 'Gedung',
            type: AdminFieldType.text,
            required: false,
          ),
          AdminFieldConfig(
            name: 'capacity',
            label: 'Kapasitas',
            type: AdminFieldType.number,
          ),
        ],
      );
}

class SchedulesPage extends StatelessWidget {
  const SchedulesPage({super.key});

  @override
  Widget build(BuildContext context) => const AdminCollectionPage(
        title: 'Jadwal Kelas',
        description:
            'Kelola jadwal kelas dengan validasi konflik dosen dan ruangan.',
        listEndpoint: '/master/schedules',
        submitEndpoint: '/master/schedules',
        fields: [
          AdminFieldConfig(
            name: 'academic_class_id',
            label: 'Kelas Akademik',
            type: AdminFieldType.select,
            optionsEndpoint: '/master/academic-classes',
            optionValueKey: 'id',
            optionLabelKey: 'name',
          ),
          AdminFieldConfig(
            name: 'lecturer_id',
            label: 'Dosen',
            type: AdminFieldType.select,
            optionsEndpoint: '/master/lecturers',
            optionValueKey: 'id',
            optionLabelKey: 'name',
            required: false,
          ),
          AdminFieldConfig(
            name: 'room_id',
            label: 'Ruangan',
            type: AdminFieldType.select,
            optionsEndpoint: '/master/rooms',
            optionValueKey: 'id',
            optionLabelKey: 'name',
            required: false,
          ),
          AdminFieldConfig(
            name: 'day_of_week',
            label: 'Hari',
            type: AdminFieldType.select,
            staticOptions: [
              AdminFieldOption(value: '1', label: 'Senin'),
              AdminFieldOption(value: '2', label: 'Selasa'),
              AdminFieldOption(value: '3', label: 'Rabu'),
              AdminFieldOption(value: '4', label: 'Kamis'),
              AdminFieldOption(value: '5', label: 'Jumat'),
              AdminFieldOption(value: '6', label: 'Sabtu'),
            ],
          ),
          AdminFieldConfig(
            name: 'start_time',
            label: 'Jam Mulai',
            type: AdminFieldType.text,
            hintText: '09:40',
          ),
          AdminFieldConfig(
            name: 'end_time',
            label: 'Jam Selesai',
            type: AdminFieldType.text,
            hintText: '12:10',
          ),
        ],
      );
}

class UsersSecurityPage extends ConsumerStatefulWidget {
  const UsersSecurityPage({super.key});

  @override
  ConsumerState<UsersSecurityPage> createState() => _UsersSecurityPageState();
}

class _UsersSecurityPageState extends ConsumerState<UsersSecurityPage> {
  bool _loading = true;
  bool _submitting = false;
  String? _selectedUserId;
  final Set<String> _selectedRoles = <String>{};
  List<Map<String, dynamic>> _users = const [];
  List<Map<String, dynamic>> _roles = const [];

  @override
  void initState() {
    super.initState();
    _load();
  }

  @override
  Widget build(BuildContext context) {
    Map<String, dynamic>? selectedUser;
    for (final user in _users) {
      if ('${user['id']}' == _selectedUserId) {
        selectedUser = user;
        break;
      }
    }

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text('Manajemen Pengguna',
            style: Theme.of(context).textTheme.headlineMedium),
        const SizedBox(height: 8),
        const Text(
          'Kelola assignment role pengguna langsung dari frontend admin.',
          style: TextStyle(color: Color(0xFF64748B)),
        ),
        const SizedBox(height: 24),
        Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Expanded(
              child: Card(
                child: Padding(
                  padding: const EdgeInsets.all(20),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        'Assign Role',
                        style: TextStyle(
                            fontSize: 18, fontWeight: FontWeight.w700),
                      ),
                      const SizedBox(height: 16),
                      DropdownButtonFormField<String>(
                        value: _selectedUserId,
                        decoration:
                            const InputDecoration(labelText: 'Pengguna'),
                        items: _users
                            .map(
                              (user) => DropdownMenuItem<String>(
                                value: '${user['id']}',
                                child:
                                    Text('${user['name']} (${user['email']})'),
                              ),
                            )
                            .toList(),
                        onChanged: (value) {
                          Map<String, dynamic>? user;
                          for (final item in _users) {
                            if ('${item['id']}' == value) {
                              user = item;
                              break;
                            }
                          }
                          setState(() {
                            _selectedUserId = value;
                            _selectedRoles
                              ..clear()
                              ..addAll(
                                  (user?['roles'] as List<dynamic>? ?? const [])
                                      .map((role) => '$role'));
                          });
                        },
                      ),
                      const SizedBox(height: 16),
                      Wrap(
                        spacing: 8,
                        runSpacing: 8,
                        children: _roles
                            .map(
                              (role) => FilterChip(
                                label: Text('${role['name']}'),
                                selected:
                                    _selectedRoles.contains('${role['code']}'),
                                onSelected: (selected) {
                                  setState(() {
                                    if (selected) {
                                      _selectedRoles.add('${role['code']}');
                                    } else {
                                      _selectedRoles.remove('${role['code']}');
                                    }
                                  });
                                },
                              ),
                            )
                            .toList(),
                      ),
                      const SizedBox(height: 16),
                      FilledButton(
                        onPressed: _loading || _submitting ? null : _submit,
                        child: const Text('Simpan Role'),
                      ),
                    ],
                  ),
                ),
              ),
            ),
            const SizedBox(width: 16),
            Expanded(
              flex: 2,
              child: Card(
                child: Padding(
                  padding: const EdgeInsets.all(20),
                  child: _loading
                      ? const Center(child: CircularProgressIndicator())
                      : Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Row(
                              children: [
                                const Expanded(
                                  child: Text(
                                    'Daftar Pengguna',
                                    style: TextStyle(
                                        fontSize: 18,
                                        fontWeight: FontWeight.w700),
                                  ),
                                ),
                                OutlinedButton.icon(
                                  onPressed: _load,
                                  icon: const Icon(Icons.refresh),
                                  label: const Text('Refresh'),
                                ),
                              ],
                            ),
                            const SizedBox(height: 12),
                            if (selectedUser != null)
                              Text(
                                'Dipilih: ${selectedUser['name']} · Role: ${(selectedUser['roles'] as List<dynamic>? ?? const []).join(', ')}',
                              ),
                            const SizedBox(height: 8),
                            for (final user in _users)
                              ListTile(
                                contentPadding: EdgeInsets.zero,
                                title: Text('${user['name']}'),
                                subtitle: Text(
                                    '${user['email']} · Role: ${(user['roles'] as List<dynamic>? ?? const []).join(', ')}'),
                              ),
                          ],
                        ),
                ),
              ),
            ),
          ],
        ),
      ],
    );
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    try {
      final dio = ref.read(dioProvider);
      final usersResponse =
          await dio.get<Map<String, dynamic>>('/security/users');
      final rolesResponse =
          await dio.get<Map<String, dynamic>>('/security/roles');
      _users = ((usersResponse.data?['data'] as Map<String, dynamic>? ??
                  const {})['items'] as List<dynamic>? ??
              const [])
          .whereType<Map<String, dynamic>>()
          .toList();
      _roles = ((rolesResponse.data?['data'] as Map<String, dynamic>? ??
                  const {})['items'] as List<dynamic>? ??
              const [])
          .whereType<Map<String, dynamic>>()
          .toList();
      if (_selectedUserId == null && _users.isNotEmpty) {
        _selectedUserId = '${_users.first['id']}';
        _selectedRoles
          ..clear()
          ..addAll((_users.first['roles'] as List<dynamic>? ?? const [])
              .map((role) => '$role'));
      }
    } finally {
      if (mounted) {
        setState(() => _loading = false);
      }
    }
  }

  Future<void> _submit() async {
    if (_selectedUserId == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Pilih pengguna terlebih dahulu.')),
      );
      return;
    }

    setState(() => _submitting = true);
    try {
      await ref.read(dioProvider).put<Map<String, dynamic>>(
        '/security/users/$_selectedUserId/roles',
        data: {'role_codes': _selectedRoles.toList()},
      );
      await _load();
      if (!mounted) {
        return;
      }
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Role pengguna berhasil diperbarui.')),
      );
    } catch (error) {
      if (!mounted) {
        return;
      }
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Gagal memperbarui role: $error')),
      );
    } finally {
      if (mounted) {
        setState(() => _submitting = false);
      }
    }
  }
}

class RolesPage extends StatelessWidget {
  const RolesPage({super.key});

  @override
  Widget build(BuildContext context) => const EndpointPage(
        title: 'Role',
        description: 'Menampilkan daftar role sistem dan izin turunannya.',
        endpoint: '/security/roles',
      );
}

class PermissionsPage extends StatelessWidget {
  const PermissionsPage({super.key});

  @override
  Widget build(BuildContext context) => const EndpointPage(
        title: 'Permission',
        description: 'Menampilkan permission granular untuk RBAC SIAT.',
        endpoint: '/security/permissions',
      );
}

class SettingsPage extends StatelessWidget {
  const SettingsPage({super.key});

  @override
  Widget build(BuildContext context) => const AdminCollectionPage(
        title: 'Pengaturan Sistem',
        description:
            'Kelola konfigurasi sistem operasional dari frontend admin.',
        listEndpoint: '/security/settings',
        submitEndpoint: '/security/settings',
        fields: [
          AdminFieldConfig(
            name: 'key',
            label: 'Key',
            type: AdminFieldType.text,
          ),
          AdminFieldConfig(
            name: 'label',
            label: 'Label',
            type: AdminFieldType.text,
          ),
          AdminFieldConfig(
            name: 'value',
            label: 'Value',
            type: AdminFieldType.text,
            required: false,
          ),
        ],
      );
}
