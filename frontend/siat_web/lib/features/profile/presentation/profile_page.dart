import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/auth/app_session.dart';
import '../../../core/network/dio_provider.dart';

class ProfilePage extends ConsumerStatefulWidget {
  const ProfilePage({super.key});

  @override
  ConsumerState<ProfilePage> createState() => _ProfilePageState();
}

class _ProfilePageState extends ConsumerState<ProfilePage> {
  final _currentPasswordController = TextEditingController();
  final _newPasswordController = TextEditingController();
  final _newPasswordConfirmationController = TextEditingController();
  bool _submitting = false;

  @override
  void dispose() {
    _currentPasswordController.dispose();
    _newPasswordController.dispose();
    _newPasswordConfirmationController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final session = ref.watch(sessionProvider);
    final sessionsFuture = ref.read(dioProvider).get<Map<String, dynamic>>('/auth/sessions');

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          'Profil dan Session Management',
          style: Theme.of(context).textTheme.headlineMedium,
        ),
        const SizedBox(height: 24),
        Card(
          child: Padding(
            padding: const EdgeInsets.all(24),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                _ProfileRow(label: 'Nama', value: session.name),
                _ProfileRow(label: 'Email', value: session.email),
                _ProfileRow(label: 'Role aktif', value: session.activeRole.name),
                _ProfileRow(label: 'Autentikasi', value: session.isAuthenticated ? 'Sudah login' : 'Belum login'),
                _ProfileRow(label: 'Token aktif', value: session.token.isEmpty ? 'Tidak ada' : 'Tersimpan'),
                const SizedBox(height: 16),
                const Text(
                  'Halaman ini menutup flow perubahan password dan daftar sesi aktif dari backend.',
                  style: TextStyle(color: Color(0xFF64748B)),
                ),
              ],
            ),
          ),
        ),
        const SizedBox(height: 16),
        Card(
          child: Padding(
            padding: const EdgeInsets.all(24),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'Ubah Password',
                  style: TextStyle(fontSize: 18, fontWeight: FontWeight.w700),
                ),
                const SizedBox(height: 16),
                TextField(
                  controller: _currentPasswordController,
                  obscureText: true,
                  decoration: const InputDecoration(labelText: 'Password saat ini'),
                ),
                const SizedBox(height: 12),
                TextField(
                  controller: _newPasswordController,
                  obscureText: true,
                  decoration: const InputDecoration(labelText: 'Password baru'),
                ),
                const SizedBox(height: 12),
                TextField(
                  controller: _newPasswordConfirmationController,
                  obscureText: true,
                  decoration: const InputDecoration(labelText: 'Konfirmasi password baru'),
                ),
                const SizedBox(height: 16),
                FilledButton(
                  onPressed: _submitting ? null : _changePassword,
                  child: const Text('Simpan Password Baru'),
                ),
              ],
            ),
          ),
        ),
        const SizedBox(height: 16),
        Card(
          child: Padding(
            padding: const EdgeInsets.all(24),
            child: FutureBuilder(
              future: sessionsFuture,
              builder: (context, snapshot) {
                if (snapshot.connectionState == ConnectionState.waiting) {
                  return const Center(child: CircularProgressIndicator());
                }

                if (snapshot.hasError) {
                  return Text('Gagal memuat sesi: ${snapshot.error}');
                }

                final response = snapshot.data?.data ?? const <String, dynamic>{};
                final data = response['data'] as Map<String, dynamic>? ?? const {};
                final items = data['items'] as List<dynamic>? ?? const [];

                return Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'Sesi Aktif',
                      style: TextStyle(fontSize: 18, fontWeight: FontWeight.w700),
                    ),
                    const SizedBox(height: 12),
                    for (final sessionItem in items)
                      ListTile(
                        contentPadding: EdgeInsets.zero,
                        leading: const Icon(Icons.devices_outlined),
                        title: Text('${(sessionItem as Map<String, dynamic>)['ip_address'] ?? 'IP tidak tersedia'}'),
                        subtitle: Text('${sessionItem['user_agent'] ?? 'User agent tidak tersedia'}'),
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

  Future<void> _changePassword() async {
    setState(() => _submitting = true);

    try {
      await ref.read(dioProvider).post<Map<String, dynamic>>(
            '/auth/change-password',
            data: {
              'current_password': _currentPasswordController.text,
              'new_password': _newPasswordController.text,
              'new_password_confirmation': _newPasswordConfirmationController.text,
            },
          );

      if (!mounted) {
        return;
      }

      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Password berhasil diubah.')),
      );
    } catch (error) {
      if (!mounted) {
        return;
      }

      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Gagal mengubah password: $error')),
      );
    } finally {
      if (mounted) {
        setState(() => _submitting = false);
      }
    }
  }
}

class _ProfileRow extends StatelessWidget {
  const _ProfileRow({
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
          SizedBox(
            width: 140,
            child: Text(label, style: const TextStyle(color: Color(0xFF64748B))),
          ),
          Expanded(
            child: Text(
              value,
              style: const TextStyle(fontWeight: FontWeight.w600),
            ),
          ),
        ],
      ),
    );
  }
}
