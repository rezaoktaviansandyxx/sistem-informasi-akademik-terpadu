import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../core/network/dio_provider.dart';

class EndpointPage extends ConsumerStatefulWidget {
  const EndpointPage({
    super.key,
    required this.title,
    required this.description,
    required this.endpoint,
  });

  final String title;
  final String description;
  final String endpoint;

  @override
  ConsumerState<EndpointPage> createState() => _EndpointPageState();
}

class _EndpointPageState extends ConsumerState<EndpointPage> {
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
          widget.title,
          style: Theme.of(context).textTheme.headlineMedium,
        ),
        const SizedBox(height: 8),
        Text(
          widget.description,
          style: const TextStyle(color: Color(0xFF64748B)),
        ),
        const SizedBox(height: 24),
        Card(
          child: Padding(
            padding: const EdgeInsets.all(20),
            child: FutureBuilder<Map<String, dynamic>>(
              future: _future,
              builder: (context, snapshot) {
                if (snapshot.connectionState == ConnectionState.waiting) {
                  return const Padding(
                    padding: EdgeInsets.all(24),
                    child: Center(child: CircularProgressIndicator()),
                  );
                }

                if (snapshot.hasError) {
                  return Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Gagal memuat data: ${snapshot.error}',
                        style: const TextStyle(color: Colors.red),
                      ),
                      const SizedBox(height: 12),
                      FilledButton(
                        onPressed: _refresh,
                        child: const Text('Coba lagi'),
                      ),
                    ],
                  );
                }

                final data = snapshot.data ?? const {};
                final payload = data['data'];
                return Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Expanded(
                          child: Wrap(
                            spacing: 8,
                            runSpacing: 8,
                            children: [
                              _StatusChip(
                                label: 'Endpoint',
                                value: widget.endpoint,
                                tone: _StatusTone.info,
                              ),
                              _StatusChip(
                                label: 'Status',
                                value: '${data['success'] == true ? 'Terhubung' : 'Unknown'}',
                                tone: data['success'] == true ? _StatusTone.positive : _StatusTone.warning,
                              ),
                            ],
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
                    if (payload is Map<String, dynamic>) ...[
                      _SummaryCards(data: payload),
                      const SizedBox(height: 16),
                    ],
                    if (payload is Map<String, dynamic>) ...[
                      ...payload.entries.map(
                        (entry) => Padding(
                          padding: const EdgeInsets.only(bottom: 16),
                          child: _PayloadSection(
                            title: _titleize(entry.key),
                            value: entry.value,
                          ),
                        ),
                      ),
                    ] else
                      _PayloadSection(
                        title: 'Response',
                        value: payload ?? data,
                      ),
                  ],
                );
              },
            ),
          ),
        ),
      ],
    );
  }

  Future<Map<String, dynamic>> _load() async {
    final dio = ref.read(dioProvider);
    final response = await dio.get<Map<String, dynamic>>(widget.endpoint);
    return response.data ?? const {};
  }

  void _refresh() {
    setState(() {
      _future = _load();
    });
  }
}

class _SummaryCards extends StatelessWidget {
  const _SummaryCards({
    required this.data,
  });

  final Map<String, dynamic> data;

  @override
  Widget build(BuildContext context) {
    final summaryItems = data.entries
        .where(
          (entry) => entry.value is String || entry.value is num || entry.value is bool,
        )
        .take(4)
        .toList();

    if (summaryItems.isEmpty) {
      return const SizedBox.shrink();
    }

    return Wrap(
      spacing: 12,
      runSpacing: 12,
      children: summaryItems
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
                        _titleize(entry.key),
                        style: const TextStyle(color: Color(0xFF64748B)),
                      ),
                      const SizedBox(height: 10),
                      Text(
                        '${entry.value}',
                        style: Theme.of(context).textTheme.headlineSmall?.copyWith(
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
    );
  }
}

class _PayloadSection extends StatelessWidget {
  const _PayloadSection({
    required this.title,
    required this.value,
  });

  final String title;
  final dynamic value;

  @override
  Widget build(BuildContext context) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              title,
              style: const TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.w700,
              ),
            ),
            const SizedBox(height: 16),
            _PayloadValue(value: value),
          ],
        ),
      ),
    );
  }
}

class _PayloadValue extends StatelessWidget {
  const _PayloadValue({
    required this.value,
  });

  final dynamic value;

  @override
  Widget build(BuildContext context) {
    if (value is List) {
      final list = value as List<dynamic>;
      if (list.isEmpty) {
        return const Text('Belum ada data.');
      }

      if (list.every((item) => item is Map<String, dynamic>)) {
        final items = list.cast<Map<String, dynamic>>();
        return _DataTableCard(items: items);
      }

      return Wrap(
        spacing: 8,
        runSpacing: 8,
        children: list
            .map(
              (item) => Chip(label: Text('$item')),
            )
            .toList(),
      );
    }

    if (value is Map<String, dynamic>) {
      final map = value as Map<String, dynamic>;
      return Column(
        children: map.entries
            .map(
              (entry) => Container(
                margin: const EdgeInsets.only(bottom: 10),
                padding: const EdgeInsets.all(14),
                decoration: BoxDecoration(
                  color: const Color(0xFFF8FAFC),
                  borderRadius: BorderRadius.circular(14),
                  border: Border.all(color: const Color(0xFFE2E8F0)),
                ),
                child: Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Expanded(
                      child: Text(
                        _titleize(entry.key),
                        style: const TextStyle(
                          color: Color(0xFF64748B),
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      flex: 2,
                      child: Text(
                        _stringify(entry.value),
                        textAlign: TextAlign.right,
                      ),
                    ),
                  ],
                ),
              ),
            )
            .toList(),
      );
    }

    return Text(_stringify(value));
  }
}

class _DataTableCard extends StatelessWidget {
  const _DataTableCard({
    required this.items,
  });

  final List<Map<String, dynamic>> items;

  @override
  Widget build(BuildContext context) {
    final columns = <String>{};
    for (final item in items.take(8)) {
      columns.addAll(item.keys.take(6));
    }
    final orderedColumns = columns.toList(growable: false);

    return SingleChildScrollView(
      scrollDirection: Axis.horizontal,
      child: DataTable(
        columnSpacing: 24,
        headingRowColor: WidgetStatePropertyAll(
          const Color(0xFFF8FAFC),
        ),
        columns: orderedColumns
            .map(
              (column) => DataColumn(label: Text(_titleize(column))),
            )
            .toList(),
        rows: items
            .take(10)
            .map(
              (item) => DataRow(
                cells: orderedColumns
                    .map(
                      (column) => DataCell(
                        ConstrainedBox(
                          constraints: const BoxConstraints(maxWidth: 220),
                          child: Text(_stringify(item[column])),
                        ),
                      ),
                    )
                    .toList(),
              ),
            )
            .toList(),
      ),
    );
  }
}

enum _StatusTone { positive, warning, info }

class _StatusChip extends StatelessWidget {
  const _StatusChip({
    required this.label,
    required this.value,
    required this.tone,
  });

  final String label;
  final String value;
  final _StatusTone tone;

  @override
  Widget build(BuildContext context) {
    final color = switch (tone) {
      _StatusTone.positive => const Color(0xFF15803D),
      _StatusTone.warning => const Color(0xFFB45309),
      _StatusTone.info => const Color(0xFF1D4ED8),
    };

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
      decoration: BoxDecoration(
        color: color.withOpacity(0.08),
        borderRadius: BorderRadius.circular(999),
      ),
      child: Text(
        '$label: $value',
        style: TextStyle(
          color: color,
          fontWeight: FontWeight.w600,
        ),
      ),
    );
  }
}

String _stringify(dynamic value) {
  if (value == null) {
    return '-';
  }

  if (value is List) {
    return value.map((item) => _stringify(item)).join(', ');
  }

  if (value is Map<String, dynamic>) {
    return value.entries.map((entry) => '${entry.key}: ${entry.value}').join(' | ');
  }

  return '$value';
}

String _titleize(String value) {
  return value
      .replaceAll('_', ' ')
      .split(' ')
      .where((part) => part.isNotEmpty)
      .map((part) => '${part[0].toUpperCase()}${part.substring(1)}')
      .join(' ');
}
