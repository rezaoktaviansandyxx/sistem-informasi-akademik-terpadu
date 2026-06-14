import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/network/dio_provider.dart';

class LecturerGradesPage extends ConsumerStatefulWidget {
  const LecturerGradesPage({super.key});

  @override
  ConsumerState<LecturerGradesPage> createState() => _LecturerGradesPageState();
}

class _LecturerGradesPageState extends ConsumerState<LecturerGradesPage> {
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
        Text(
          'Input Nilai Dosen',
          style: Theme.of(context).textTheme.headlineMedium,
        ),
        const SizedBox(height: 8),
        const Text(
          'Halaman ini menampilkan kelas dosen dan data nilai aktual dari API dosen.',
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
              return Text('Gagal memuat nilai dosen: ${snapshot.error}');
            }

            final data = snapshot.data ?? const {};
            final classSummary = data['class'] as Map<String, dynamic>? ?? const {};
            final rows = data['items'] as List<dynamic>? ?? const [];

            return Card(
              child: Padding(
                padding: const EdgeInsets.all(20),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Kelas: ${classSummary['course_code'] ?? '-'} - ${classSummary['course_name'] ?? '-'}',
                      style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w700),
                    ),
                    const SizedBox(height: 16),
                    DataTable(
                      columns: const [
                        DataColumn(label: Text('NIM')),
                        DataColumn(label: Text('Nama')),
                        DataColumn(label: Text('Tugas')),
                        DataColumn(label: Text('UTS')),
                        DataColumn(label: Text('UAS')),
                        DataColumn(label: Text('Akhir')),
                        DataColumn(label: Text('Status')),
                      ],
                      rows: rows
                          .map(
                            (item) => DataRow(
                              cells: [
                                DataCell(Text('${(item as Map<String, dynamic>)['student_id'] ?? '-'}')),
                                DataCell(Text('${item['student_name'] ?? '-'}')),
                                DataCell(Text('${item['assignment_score'] ?? '-'}')),
                                DataCell(Text('${item['mid_score'] ?? '-'}')),
                                DataCell(Text('${item['final_score'] ?? '-'}')),
                                DataCell(Text('${item['final_numeric'] ?? '-'}')),
                                DataCell(Text('${item['status'] ?? '-'}')),
                              ],
                            ),
                          )
                          .toList(),
                    ),
                    const SizedBox(height: 20),
                    Row(
                      children: [
                        const Expanded(
                          child: Text(
                            'Komponen nilai disajikan dari backend dan siap difinalisasi.',
                            style: TextStyle(color: Color(0xFF64748B)),
                          ),
                        ),
                        FilledButton(
                          onPressed: classSummary['class_id'] == null
                              ? null
                              : () => _finalize('${classSummary['class_id']}'),
                          child: const Text('Finalisasi Nilai'),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            );
          },
        ),
      ],
    );
  }

  Future<Map<String, dynamic>> _load() async {
    final dio = ref.read(dioProvider);
    final classResponse = await dio.get<Map<String, dynamic>>('/lecturer/classes');
    final classes = (classResponse.data?['data'] as Map<String, dynamic>? ?? const {})['items'] as List<dynamic>? ?? const [];

    if (classes.isEmpty) {
      return const {
        'class': <String, dynamic>{},
        'items': <dynamic>[],
      };
    }

    final firstClass = classes.first as Map<String, dynamic>;
    final gradesResponse = await dio.get<Map<String, dynamic>>(
      '/lecturer/classes/${firstClass['class_id']}/grades',
    );

    final gradesData = gradesResponse.data?['data'] as Map<String, dynamic>? ?? const {};

    return {
      'class': firstClass,
      'items': gradesData['items'] ?? const [],
    };
  }

  Future<void> _finalize(String classId) async {
    try {
      await ref.read(dioProvider).post<Map<String, dynamic>>(
            '/lecturer/classes/$classId/grades/finalize',
          );
      if (!mounted) {
        return;
      }
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Nilai berhasil difinalisasi.')),
      );
      setState(() {
        _future = _load();
      });
    } catch (error) {
      if (!mounted) {
        return;
      }
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Gagal finalisasi nilai: $error')),
      );
    }
  }
}
