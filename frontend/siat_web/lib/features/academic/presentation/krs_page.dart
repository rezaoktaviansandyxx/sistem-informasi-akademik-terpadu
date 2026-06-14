import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/network/dio_provider.dart';

class KrsPage extends ConsumerStatefulWidget {
  const KrsPage({super.key});

  @override
  ConsumerState<KrsPage> createState() => _KrsPageState();
}

class _KrsPageState extends ConsumerState<KrsPage> {
  late Future<Map<String, dynamic>> _future;
  bool _submittingEntry = false;

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
          'KRS Online',
          style: Theme.of(context).textTheme.headlineMedium,
        ),
        const SizedBox(height: 8),
        const Text(
          'Validasi prasyarat, kapasitas, konflik jadwal, dan batas SKS sekarang berasal dari API KRS.',
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
              return Text('Gagal memuat KRS: ${snapshot.error}');
            }

            final response = snapshot.data ?? const {};
            final current = response['current'] as Map<String, dynamic>? ?? const {};
            final catalog = response['catalog'] as Map<String, dynamic>? ?? const {};
            final data = current['data'] as Map<String, dynamic>? ?? const {};
            final catalogData = catalog['data'] as Map<String, dynamic>? ?? const {};
            final entries = data['entries'] as List<dynamic>? ?? const [];
            final classes = catalogData['items'] as List<dynamic>? ?? const [];
            final totalCredits = entries.fold<int>(
              0,
              (sum, entry) => sum + (((entry as Map<String, dynamic>)['credits'] as num?)?.toInt() ?? 0),
            );

            return Column(
              children: [
                Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Expanded(
                      flex: 2,
                      child: Card(
                        child: Padding(
                          padding: const EdgeInsets.all(20),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const Text(
                                'Draft KRS Saat Ini',
                                style: TextStyle(fontSize: 18, fontWeight: FontWeight.w700),
                              ),
                              const SizedBox(height: 16),
                              if (entries.isEmpty)
                                const Text('Belum ada mata kuliah pada draft KRS.')
                              else
                                for (final item in entries)
                                  Container(
                                    margin: const EdgeInsets.only(bottom: 12),
                                    padding: const EdgeInsets.all(16),
                                    decoration: BoxDecoration(
                                      color: const Color(0xFFF8FAFC),
                                      borderRadius: BorderRadius.circular(16),
                                      border: Border.all(color: const Color(0xFFE2E8F0)),
                                    ),
                                    child: Row(
                                      children: [
                                        const Icon(Icons.book_outlined, color: Color(0xFF1D4ED8)),
                                        const SizedBox(width: 12),
                                        Expanded(
                                          child: Column(
                                            crossAxisAlignment: CrossAxisAlignment.start,
                                            children: [
                                              Text(
                                                '${(item as Map<String, dynamic>)['course_code']} - ${item['course_name']}',
                                                style: const TextStyle(fontWeight: FontWeight.w700),
                                              ),
                                              const SizedBox(height: 4),
                                              Text('${item['credits']} SKS'),
                                            ],
                                          ),
                                        ),
                                      ],
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
                                'Ringkasan KRS',
                                style: TextStyle(fontSize: 18, fontWeight: FontWeight.w700),
                              ),
                              const SizedBox(height: 16),
                              _SummaryRow(label: 'Semester aktif', value: '${data['semester'] ?? '-'}'),
                              _SummaryRow(label: 'Status draft', value: '${data['status'] ?? '-'}'),
                              _SummaryRow(label: 'Total SKS', value: '$totalCredits'),
                              _SummaryRow(label: 'Mata kuliah tersedia', value: '${classes.length}'),
                              const SizedBox(height: 16),
                              SizedBox(
                                width: double.infinity,
                                child: FilledButton(
                                  onPressed: entries.isEmpty ? null : _submitKrs,
                                  child: const Text('Submit KRS'),
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
                Card(
                  child: Padding(
                    padding: const EdgeInsets.all(20),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text(
                          'Daftar Mata Kuliah',
                          style: TextStyle(fontSize: 18, fontWeight: FontWeight.w700),
                        ),
                        const SizedBox(height: 8),
                        const Text(
                          'Keranjang KRS sekarang menggunakan katalog kelas aktif dengan indikator kapasitas dan jadwal.',
                          style: TextStyle(color: Color(0xFF64748B)),
                        ),
                        const SizedBox(height: 16),
                        if (classes.isEmpty)
                          const Text('Belum ada kelas yang tersedia pada semester aktif.')
                        else
                          for (final item in classes)
                            _CatalogTile(
                              item: item as Map<String, dynamic>,
                              busy: _submittingEntry,
                              onAdd: () => _addClass(item),
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
    final dio = ref.read(dioProvider);
    final current = await dio.get<Map<String, dynamic>>('/krs/current');
    final catalog = await dio.get<Map<String, dynamic>>('/krs/catalog');
    return {
      'current': current.data ?? const {},
      'catalog': catalog.data ?? const {},
    };
  }

  Future<void> _submitKrs() async {
    try {
      await ref.read(dioProvider).post<Map<String, dynamic>>('/krs/submit');
      if (!mounted) {
        return;
      }
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('KRS berhasil disubmit.')),
      );
      setState(() {
        _future = _load();
      });
    } catch (error) {
      if (!mounted) {
        return;
      }
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Gagal submit KRS: $error')),
      );
    }
  }

  Future<void> _addClass(Map<String, dynamic> item) async {
    setState(() => _submittingEntry = true);
    try {
      final latestData = await _load();
      final catalogResponse = latestData['catalog'] as Map<String, dynamic>? ?? const {};
      final catalogData = catalogResponse['data'] as Map<String, dynamic>? ?? const {};
      final semester = catalogData['semester'] as Map<String, dynamic>? ?? const {};
      await ref.read(dioProvider).post<Map<String, dynamic>>(
            '/krs/entries',
            data: {
              'class_id': item['class_id'],
              'semester_id': semester['id'],
            },
          );
      if (!mounted) {
        return;
      }
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Mata kuliah berhasil ditambahkan ke draft.')),
      );
      setState(() {
        _future = _load();
      });
    } catch (error) {
      if (!mounted) {
        return;
      }
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Gagal menambahkan mata kuliah: $error')),
      );
    } finally {
      if (mounted) {
        setState(() => _submittingEntry = false);
      }
    }
  }
}

class _SummaryRow extends StatelessWidget {
  const _SummaryRow({
    required this.label,
    required this.value,
  });

  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: Row(
        children: [
          Expanded(child: Text(label, style: const TextStyle(color: Color(0xFF64748B)))),
          Text(value, style: const TextStyle(fontWeight: FontWeight.w600)),
        ],
      ),
    );
  }
}

class _CatalogTile extends StatelessWidget {
  const _CatalogTile({
    required this.item,
    required this.busy,
    required this.onAdd,
  });

  final Map<String, dynamic> item;
  final bool busy;
  final VoidCallback onAdd;

  @override
  Widget build(BuildContext context) {
    final schedules = (item['schedules'] as List<dynamic>? ?? const [])
        .whereType<Map<String, dynamic>>()
        .map(
          (schedule) =>
              'H${schedule['day_of_week']} ${schedule['start_time']} - ${schedule['end_time']}',
        )
        .join(' · ');
    final selected = item['selected'] == true;
    final availableSeats = (item['available_seats'] as num?)?.toInt() ?? 0;

    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: selected ? const Color(0xFFEFF6FF) : const Color(0xFFF8FAFC),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(
          color: selected ? const Color(0xFF93C5FD) : const Color(0xFFE2E8F0),
        ),
      ),
      child: Row(
        children: [
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  '${item['course_code']} - ${item['course_name']}',
                  style: const TextStyle(fontWeight: FontWeight.w700),
                ),
                const SizedBox(height: 6),
                Text(
                  '${item['credits']} SKS · ${item['lecturer_name'] ?? 'Dosen belum diatur'} · ${item['room_code'] ?? 'Ruang TBD'}',
                ),
                const SizedBox(height: 6),
                Text(
                  schedules.isEmpty ? 'Jadwal belum diatur' : schedules,
                  style: const TextStyle(color: Color(0xFF64748B)),
                ),
              ],
            ),
          ),
          const SizedBox(width: 12),
          Column(
            crossAxisAlignment: CrossAxisAlignment.end,
            children: [
              Text('Sisa kursi: $availableSeats'),
              const SizedBox(height: 8),
              FilledButton.tonal(
                onPressed: busy || selected || availableSeats == 0 ? null : onAdd,
                child: Text(selected ? 'Sudah Dipilih' : 'Tambah'),
              ),
            ],
          ),
        ],
      ),
    );
  }
}
