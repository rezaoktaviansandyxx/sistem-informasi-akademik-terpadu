import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/auth/app_session.dart';
import '../../../core/network/dio_provider.dart';

class DashboardPage extends ConsumerStatefulWidget {
  const DashboardPage({super.key});

  @override
  ConsumerState<DashboardPage> createState() => _DashboardPageState();
}

class _DashboardPageState extends ConsumerState<DashboardPage> {
  late Future<Map<String, dynamic>> _future;

  @override
  void initState() {
    super.initState();
    _future = _load();
  }

  @override
  Widget build(BuildContext context) {
    final session = ref.watch(sessionProvider);
    final role = session.activeRole;

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          'Dashboard ${_roleLabel(role)}',
          style: Theme.of(context).textTheme.headlineMedium,
        ),
        const SizedBox(height: 8),
        Text(
          'Ringkasan akademik, antrian kerja, dan insight strategis untuk ${session.email}.',
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
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Dashboard gagal dimuat: ${snapshot.error}',
                        style: const TextStyle(color: Colors.red),
                      ),
                      const SizedBox(height: 12),
                      OutlinedButton.icon(
                        onPressed: _refresh,
                        icon: const Icon(Icons.refresh),
                        label: const Text('Coba lagi'),
                      ),
                    ],
                  ),
                ),
              );
            }

            final response = snapshot.data ?? const {};
            final data = response['data'] as Map<String, dynamic>? ?? const {};
            final cards = (data['cards'] as List<dynamic>? ?? const [])
                .whereType<Map<String, dynamic>>()
                .toList();
            final charts = (data['charts'] as List<dynamic>? ?? const [])
                .whereType<Map<String, dynamic>>()
                .toList();
            final todos = (data['todos'] as List<dynamic>? ?? const [])
                .whereType<Map<String, dynamic>>()
                .toList();

            return Column(
              children: [
                Wrap(
                  spacing: 16,
                  runSpacing: 16,
                  children: cards
                      .map(
                        (item) => SizedBox(
                          width: 260,
                          child: _DashboardCard(
                            label: '${item['label'] ?? '-'}',
                            value: '${item['value'] ?? '-'}',
                            note: '${item['trend'] ?? 'Monitor'}',
                          ),
                        ),
                      )
                      .toList(),
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
                              Row(
                                children: [
                                  const Expanded(
                                    child: Text(
                                      'Tindakan Prioritas',
                                      style: TextStyle(
                                        fontSize: 18,
                                        fontWeight: FontWeight.w700,
                                      ),
                                    ),
                                  ),
                                  OutlinedButton.icon(
                                    onPressed: _refresh,
                                    icon: const Icon(Icons.refresh),
                                    label: const Text('Refresh'),
                                  ),
                                ],
                              ),
                              const SizedBox(height: 12),
                              if (todos.isEmpty)
                                const Text('Belum ada antrian prioritas.')
                              else
                                for (final todo in todos)
                                  ListTile(
                                    contentPadding: EdgeInsets.zero,
                                    leading: Icon(
                                      _todoIcon('${todo['status'] ?? 'pending'}'),
                                      color: const Color(0xFF1D4ED8),
                                    ),
                                    title: Text('${todo['title'] ?? '-'}'),
                                    subtitle: Text(
                                      'Status: ${todo['status'] ?? '-'}${todo['due_at'] != null ? ' · Due ${todo['due_at']}' : ''}',
                                    ),
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
                              const Text(
                                'Trend Akademik',
                                style: TextStyle(
                                  fontSize: 18,
                                  fontWeight: FontWeight.w700,
                                ),
                              ),
                              const SizedBox(height: 16),
                              if (charts.isEmpty)
                                const Text('Belum ada data chart.')
                              else
                                for (final chart in charts)
                                  Padding(
                                    padding: const EdgeInsets.only(bottom: 18),
                                    child: _MiniTrendChart(
                                      label: '${chart['label'] ?? '-'}',
                                      points: (chart['points'] as List<dynamic>? ?? const [])
                                          .map((point) => (point as num).toDouble())
                                          .toList(),
                                    ),
                                  ),
                            ],
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 16),
                const Card(
                  child: Padding(
                    padding: EdgeInsets.all(20),
                    child: Row(
                      children: [
                        Expanded(
                          child: ListTile(
                            contentPadding: EdgeInsets.zero,
                            leading: Icon(Icons.school_outlined),
                            title: Text('SDG 4'),
                            subtitle: Text('Layanan akademik, hasil studi, dan presensi dipantau lebih transparan.'),
                          ),
                        ),
                        Expanded(
                          child: ListTile(
                            contentPadding: EdgeInsets.zero,
                            leading: Icon(Icons.hub_outlined),
                            title: Text('SDG 9'),
                            subtitle: Text('Arsitektur modular dan API-first mempercepat integrasi institusi.'),
                          ),
                        ),
                        Expanded(
                          child: ListTile(
                            contentPadding: EdgeInsets.zero,
                            leading: Icon(Icons.gavel_outlined),
                            title: Text('SDG 16'),
                            subtitle: Text('Audit trail, RBAC, dan approval workflow menjaga tata kelola.'),
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ],
            );
          },
        ),
      ],
    );
  }

  Future<Map<String, dynamic>> _load() async {
    final session = ref.read(sessionProvider);
    final role = session.activeRole == AppRole.superAdmin
        ? 'super_admin'
        : session.activeRole.name;
    final response = await ref.read(dioProvider).get<Map<String, dynamic>>(
          '/dashboard',
          queryParameters: {'role': role},
        );
    return response.data ?? const {};
  }

  void _refresh() {
    setState(() {
      _future = _load();
    });
  }

  String _roleLabel(AppRole role) {
    return switch (role) {
      AppRole.student => 'Mahasiswa',
      AppRole.lecturer => 'Dosen',
      AppRole.admin => 'Admin Akademik',
      AppRole.leader => 'Pimpinan',
      AppRole.superAdmin => 'Super Admin',
    };
  }

  IconData _todoIcon(String status) {
    return switch (status) {
      'done' => Icons.check_circle_outline,
      'in_progress' => Icons.timelapse_outlined,
      _ => Icons.schedule_outlined,
    };
  }
}

class _DashboardCard extends StatelessWidget {
  const _DashboardCard({
    required this.label,
    required this.value,
    required this.note,
  });

  final String label;
  final String value;
  final String note;

  @override
  Widget build(BuildContext context) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              label,
              style: const TextStyle(color: Color(0xFF64748B)),
            ),
            const SizedBox(height: 10),
            Text(
              value,
              style: const TextStyle(
                fontSize: 28,
                fontWeight: FontWeight.w700,
              ),
            ),
            const SizedBox(height: 10),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
              decoration: BoxDecoration(
                color: const Color(0xFFDBEAFE),
                borderRadius: BorderRadius.circular(999),
              ),
              child: Text(
                note,
                style: const TextStyle(
                  color: Color(0xFF1D4ED8),
                  fontWeight: FontWeight.w700,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _MiniTrendChart extends StatelessWidget {
  const _MiniTrendChart({
    required this.label,
    required this.points,
  });

  final String label;
  final List<double> points;

  @override
  Widget build(BuildContext context) {
    final safePoints = points.isEmpty ? const [0.0] : points;
    final max = safePoints.reduce((a, b) => a > b ? a : b);

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          label,
          style: const TextStyle(fontWeight: FontWeight.w700),
        ),
        const SizedBox(height: 12),
        Row(
          crossAxisAlignment: CrossAxisAlignment.end,
          children: safePoints
              .map(
                (point) => Expanded(
                  child: Padding(
                    padding: const EdgeInsets.only(right: 8),
                    child: Column(
                      children: [
                        Text(point.toStringAsFixed(0)),
                        const SizedBox(height: 8),
                        Container(
                          height: max == 0 ? 12 : (point / max) * 120,
                          decoration: BoxDecoration(
                            borderRadius: BorderRadius.circular(999),
                            gradient: const LinearGradient(
                              begin: Alignment.bottomCenter,
                              end: Alignment.topCenter,
                              colors: [Color(0xFF1D4ED8), Color(0xFF60A5FA)],
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              )
              .toList(),
        ),
      ],
    );
  }
}
