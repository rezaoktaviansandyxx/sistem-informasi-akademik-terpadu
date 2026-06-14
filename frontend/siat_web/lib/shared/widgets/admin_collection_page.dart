import 'dart:convert';

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../core/network/dio_provider.dart';

enum AdminFieldType { text, multiline, number, date, select, boolean, json }

class AdminFieldOption {
  const AdminFieldOption({
    required this.value,
    required this.label,
  });

  final String value;
  final String label;
}

class AdminFieldConfig {
  const AdminFieldConfig({
    required this.name,
    required this.label,
    required this.type,
    this.required = true,
    this.hintText,
    this.staticOptions = const [],
    this.optionsEndpoint,
    this.optionValueKey,
    this.optionLabelKey,
  });

  final String name;
  final String label;
  final AdminFieldType type;
  final bool required;
  final String? hintText;
  final List<AdminFieldOption> staticOptions;
  final String? optionsEndpoint;
  final String? optionValueKey;
  final String? optionLabelKey;
}

class AdminCollectionPage extends ConsumerStatefulWidget {
  const AdminCollectionPage({
    super.key,
    required this.title,
    required this.description,
    required this.listEndpoint,
    required this.submitEndpoint,
    required this.fields,
    this.submitButtonLabel = 'Simpan',
  });

  final String title;
  final String description;
  final String listEndpoint;
  final String submitEndpoint;
  final List<AdminFieldConfig> fields;
  final String submitButtonLabel;

  @override
  ConsumerState<AdminCollectionPage> createState() =>
      _AdminCollectionPageState();
}

class _AdminCollectionPageState extends ConsumerState<AdminCollectionPage> {
  late final Map<String, TextEditingController> _controllers;
  final TextEditingController _searchController = TextEditingController();
  final Map<String, dynamic> _values = <String, dynamic>{};
  final Map<String, List<AdminFieldOption>> _options =
      <String, List<AdminFieldOption>>{};
  bool _loading = true;
  bool _submitting = false;
  List<Map<String, dynamic>> _items = const [];
  int _currentPage = 1;
  int _lastPage = 1;
  int _total = 0;
  static const int _perPage = 10;

  @override
  void initState() {
    super.initState();
    _controllers = {
      for (final field in widget.fields)
        if (field.type != AdminFieldType.boolean &&
            field.type != AdminFieldType.select)
          field.name: TextEditingController(),
    };
    _initializeValues();
    _bootstrap();
  }

  @override
  void dispose() {
    _searchController.dispose();
    for (final controller in _controllers.values) {
      controller.dispose();
    }
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(widget.title, style: Theme.of(context).textTheme.headlineMedium),
        const SizedBox(height: 8),
        Text(widget.description,
            style: const TextStyle(color: Color(0xFF64748B))),
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
                        'Form Input',
                        style: TextStyle(
                            fontSize: 18, fontWeight: FontWeight.w700),
                      ),
                      const SizedBox(height: 16),
                      for (final field in widget.fields) ...[
                        _buildField(field),
                        const SizedBox(height: 12),
                      ],
                      const SizedBox(height: 12),
                      Row(
                        children: [
                          FilledButton(
                            onPressed: _loading || _submitting ? null : _submit,
                            child: Text(widget.submitButtonLabel),
                          ),
                          const SizedBox(width: 12),
                          OutlinedButton(
                            onPressed: _submitting ? null : _resetForm,
                            child: const Text('Reset'),
                          ),
                        ],
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
                            Wrap(
                              spacing: 12,
                              runSpacing: 12,
                              crossAxisAlignment: WrapCrossAlignment.center,
                              children: [
                                const Text(
                                  'Daftar Data',
                                  style: TextStyle(
                                      fontSize: 18,
                                      fontWeight: FontWeight.w700),
                                ),
                                SizedBox(
                                  width: 260,
                                  child: TextField(
                                    controller: _searchController,
                                    decoration: InputDecoration(
                                      hintText: 'Cari data...',
                                      prefixIcon: const Icon(Icons.search),
                                      suffixIcon: _searchController.text.isEmpty
                                          ? null
                                          : IconButton(
                                              onPressed: () {
                                                _searchController.clear();
                                                _currentPage = 1;
                                                _bootstrap();
                                              },
                                              icon: const Icon(Icons.close),
                                            ),
                                    ),
                                    onSubmitted: (_) {
                                      _currentPage = 1;
                                      _bootstrap();
                                    },
                                  ),
                                ),
                                OutlinedButton.icon(
                                  onPressed: _bootstrap,
                                  icon: const Icon(Icons.refresh),
                                  label: const Text('Refresh'),
                                ),
                              ],
                            ),
                            const SizedBox(height: 12),
                            Wrap(
                              spacing: 8,
                              runSpacing: 8,
                              children: [
                                _InfoChip(label: 'Total', value: '$_total'),
                                _InfoChip(
                                    label: 'Halaman',
                                    value: '$_currentPage / $_lastPage'),
                                _InfoChip(
                                    label: 'Per Page', value: '$_perPage'),
                              ],
                            ),
                            const SizedBox(height: 16),
                            if (_items.isEmpty)
                              const Text('Belum ada data.')
                            else
                              _ItemsTable(items: _items),
                            if (_lastPage > 1) ...[
                              const SizedBox(height: 16),
                              Row(
                                mainAxisAlignment: MainAxisAlignment.end,
                                children: [
                                  OutlinedButton(
                                    onPressed: _currentPage > 1
                                        ? () {
                                            setState(() => _currentPage -= 1);
                                            _bootstrap();
                                          }
                                        : null,
                                    child: const Text('Sebelumnya'),
                                  ),
                                  const SizedBox(width: 12),
                                  FilledButton.tonal(
                                    onPressed: null,
                                    child: Text('Halaman $_currentPage'),
                                  ),
                                  const SizedBox(width: 12),
                                  OutlinedButton(
                                    onPressed: _currentPage < _lastPage
                                        ? () {
                                            setState(() => _currentPage += 1);
                                            _bootstrap();
                                          }
                                        : null,
                                    child: const Text('Berikutnya'),
                                  ),
                                ],
                              ),
                            ],
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

  Widget _buildField(AdminFieldConfig field) {
    switch (field.type) {
      case AdminFieldType.multiline:
        return TextField(
          controller: _controllers[field.name],
          minLines: 3,
          maxLines: 5,
          decoration: InputDecoration(
            labelText: field.label,
            hintText: field.hintText,
          ),
        );
      case AdminFieldType.number:
        return TextField(
          controller: _controllers[field.name],
          keyboardType: TextInputType.number,
          decoration: InputDecoration(
            labelText: field.label,
            hintText: field.hintText,
          ),
        );
      case AdminFieldType.date:
        return TextField(
          controller: _controllers[field.name],
          decoration: InputDecoration(
            labelText: field.label,
            hintText: field.hintText ?? 'YYYY-MM-DD',
          ),
        );
      case AdminFieldType.select:
        final options = _options[field.name] ?? field.staticOptions;
        final currentValue = _values[field.name] as String?;
        return DropdownButtonFormField<String>(
          value: currentValue != null &&
                  options.any((option) => option.value == currentValue)
              ? currentValue
              : null,
          decoration: InputDecoration(labelText: field.label),
          items: options
              .map(
                (option) => DropdownMenuItem<String>(
                  value: option.value,
                  child: Text(option.label),
                ),
              )
              .toList(),
          onChanged: (value) => setState(() => _values[field.name] = value),
        );
      case AdminFieldType.boolean:
        return SwitchListTile(
          contentPadding: EdgeInsets.zero,
          title: Text(field.label),
          value: (_values[field.name] as bool?) ?? false,
          onChanged: (value) => setState(() => _values[field.name] = value),
        );
      case AdminFieldType.json:
        return TextField(
          controller: _controllers[field.name],
          minLines: 3,
          maxLines: 5,
          decoration: InputDecoration(
            labelText: field.label,
            hintText: field.hintText ?? '{"key":"value"}',
          ),
        );
      case AdminFieldType.text:
        return TextField(
          controller: _controllers[field.name],
          decoration: InputDecoration(
            labelText: field.label,
            hintText: field.hintText,
          ),
        );
    }
  }

  Future<void> _bootstrap() async {
    setState(() => _loading = true);
    try {
      await _loadOptions();
      await _loadItems();
    } finally {
      if (mounted) {
        setState(() => _loading = false);
      }
    }
  }

  Future<void> _loadItems() async {
    final response = await ref.read(dioProvider).get<Map<String, dynamic>>(
      widget.listEndpoint,
      queryParameters: {
        'page': _currentPage,
        'per_page': _perPage,
        if (_searchController.text.trim().isNotEmpty)
          'search': _searchController.text.trim(),
      },
    );
    final data = response.data?['data'];
    final items = _extractItems(data);
    _items = items;
    final pagination = data is Map<String, dynamic> ? data['pagination'] : null;
    if (pagination is Map<String, dynamic>) {
      _currentPage =
          (pagination['current_page'] as num?)?.toInt() ?? _currentPage;
      _lastPage = (pagination['last_page'] as num?)?.toInt() ?? 1;
      _total = (pagination['total'] as num?)?.toInt() ?? items.length;
    } else {
      _lastPage = 1;
      _total = items.length;
    }
  }

  Future<void> _loadOptions() async {
    for (final field
        in widget.fields.where((field) => field.optionsEndpoint != null)) {
      final response = await ref
          .read(dioProvider)
          .get<Map<String, dynamic>>(field.optionsEndpoint!);
      final data = response.data?['data'];
      final items = _extractItems(data);
      _options[field.name] = items.map((item) {
        final value = _resolvePath(item, field.optionValueKey ?? 'id');
        final label = _resolvePath(item, field.optionLabelKey ?? 'name');
        return AdminFieldOption(
          value: '$value',
          label: '$label',
        );
      }).toList();
      if ((_values[field.name] == null || '${_values[field.name]}'.isEmpty) &&
          (_options[field.name]?.isNotEmpty ?? false)) {
        _values[field.name] = _options[field.name]!.first.value;
      }
    }
  }

  Future<void> _submit() async {
    final payload = <String, dynamic>{};

    for (final field in widget.fields) {
      final value = _readFieldValue(field);

      if (field.required &&
          (value == null || (value is String && value.trim().isEmpty))) {
        _showMessage('Field "${field.label}" wajib diisi.');
        return;
      }

      if (value != null && (!(value is String) || value.trim().isNotEmpty)) {
        payload[field.name] = value;
      }
    }

    setState(() => _submitting = true);
    try {
      await ref.read(dioProvider).post<Map<String, dynamic>>(
            widget.submitEndpoint,
            data: payload,
          );
      _showMessage('${widget.title} berhasil disimpan.');
      _resetForm();
      await _bootstrap();
    } catch (error) {
      _showMessage('Gagal menyimpan ${widget.title}: $error');
    } finally {
      if (mounted) {
        setState(() => _submitting = false);
      }
    }
  }

  dynamic _readFieldValue(AdminFieldConfig field) {
    switch (field.type) {
      case AdminFieldType.boolean:
        return (_values[field.name] as bool?) ?? false;
      case AdminFieldType.select:
        return _values[field.name];
      case AdminFieldType.number:
        final text = _controllers[field.name]?.text.trim() ?? '';
        return text.isEmpty ? null : int.tryParse(text);
      case AdminFieldType.json:
        final text = _controllers[field.name]?.text.trim() ?? '';
        return text.isEmpty ? null : jsonDecode(text);
      case AdminFieldType.text:
      case AdminFieldType.multiline:
      case AdminFieldType.date:
        return _controllers[field.name]?.text.trim();
    }
  }

  void _initializeValues() {
    for (final field in widget.fields) {
      if (field.type == AdminFieldType.boolean) {
        _values[field.name] = false;
      } else if (field.type == AdminFieldType.select &&
          field.staticOptions.isNotEmpty) {
        _values[field.name] = field.staticOptions.first.value;
      } else {
        _values[field.name] = null;
      }
    }
  }

  void _resetForm() {
    for (final controller in _controllers.values) {
      controller.clear();
    }
    _initializeValues();
    if (mounted) {
      setState(() {});
    }
  }

  List<Map<String, dynamic>> _extractItems(dynamic data) {
    if (data is Map<String, dynamic>) {
      final items = data['items'];
      if (items is List) {
        return items.whereType<Map<String, dynamic>>().toList();
      }
      return [data];
    }

    if (data is List) {
      return data.whereType<Map<String, dynamic>>().toList();
    }

    return const [];
  }

  dynamic _resolvePath(Map<String, dynamic> item, String path) {
    dynamic current = item;
    for (final segment in path.split('.')) {
      if (current is Map<String, dynamic>) {
        current = current[segment];
      } else {
        return null;
      }
    }
    return current;
  }

  String _stringifyValue(dynamic value) {
    if (value is List) {
      return value.join(', ');
    }
    if (value is Map<String, dynamic>) {
      return value.entries
          .map((entry) => '${entry.key}: ${entry.value}')
          .join(', ');
    }
    return '${value ?? '-'}';
  }

  void _showMessage(String message) {
    if (!mounted) {
      return;
    }
    ScaffoldMessenger.of(context)
        .showSnackBar(SnackBar(content: Text(message)));
  }
}

class _InfoChip extends StatelessWidget {
  const _InfoChip({
    required this.label,
    required this.value,
  });

  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
      decoration: BoxDecoration(
        color: const Color(0xFFF8FAFC),
        borderRadius: BorderRadius.circular(999),
        border: Border.all(color: const Color(0xFFE2E8F0)),
      ),
      child: Text(
        '$label: $value',
        style: const TextStyle(
          color: Color(0xFF334155),
          fontWeight: FontWeight.w600,
        ),
      ),
    );
  }
}

class _ItemsTable extends StatelessWidget {
  const _ItemsTable({
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
              (column) => DataColumn(label: Text(_labelize(column))),
            )
            .toList(),
        rows: items
            .map(
              (item) => DataRow(
                cells: orderedColumns
                    .map(
                      (column) => DataCell(
                        ConstrainedBox(
                          constraints: const BoxConstraints(maxWidth: 220),
                          child: Text(_displayValue(item[column])),
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

String _displayValue(dynamic value) {
  if (value is List) {
    return value.join(', ');
  }
  if (value is Map<String, dynamic>) {
    return value.entries
        .map((entry) => '${entry.key}: ${entry.value}')
        .join(', ');
  }
  return '${value ?? '-'}';
}

String _labelize(String value) {
  return value
      .replaceAll('_', ' ')
      .split(' ')
      .where((part) => part.isNotEmpty)
      .map((part) => '${part[0].toUpperCase()}${part.substring(1)}')
      .join(' ');
}
