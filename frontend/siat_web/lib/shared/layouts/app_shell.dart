import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../core/auth/app_session.dart';

class AppShell extends ConsumerWidget {
  const AppShell({
    super.key,
    required this.child,
  });

  final Widget child;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final session = ref.watch(sessionProvider);
    final controller = ref.read(sessionProvider.notifier);
    final route = GoRouterState.of(context).matchedLocation;
    final allItems = <({String label, String route, IconData icon})>[
      (label: 'Dashboard', route: '/dashboard', icon: Icons.dashboard_outlined),
      (label: 'KRS Online', route: '/academic/krs', icon: Icons.fact_check_outlined),
      (label: 'KHS', route: '/academic/khs', icon: Icons.menu_book_outlined),
      (label: 'Transkrip', route: '/academic/transcript', icon: Icons.school_outlined),
      (label: 'Jadwal', route: '/academic/schedule', icon: Icons.calendar_month_outlined),
      (label: 'Presensi', route: '/academic/attendance', icon: Icons.how_to_reg_outlined),
      (label: 'Input Nilai', route: '/lecturer/grades', icon: Icons.grading_outlined),
      (label: 'Kelas Dosen', route: '/lecturer/classes', icon: Icons.class_outlined),
      (label: 'Presensi Ajar', route: '/lecturer/attendance', icon: Icons.event_available_outlined),
      (label: 'Rekap Ajar', route: '/lecturer/summary', icon: Icons.bar_chart_outlined),
      (label: 'Pengumuman', route: '/administration/announcements', icon: Icons.campaign_outlined),
      (label: 'Kalender', route: '/administration/calendar', icon: Icons.event_note_outlined),
      (label: 'Surat', route: '/administration/letters', icon: Icons.description_outlined),
      (label: 'Verifikasi', route: '/administration/verifications', icon: Icons.verified_user_outlined),
      (label: 'Approval', route: '/administration/approvals', icon: Icons.pending_actions_outlined),
      (label: 'Laporan', route: '/administration/reports', icon: Icons.assessment_outlined),
      (label: 'Audit', route: '/administration/audit', icon: Icons.manage_search_outlined),
      (label: 'Aktivitas', route: '/administration/activity-logs', icon: Icons.history_outlined),
      (label: 'Jurusan', route: '/master/departments', icon: Icons.account_tree_outlined),
      (label: 'Kurikulum', route: '/master/curricula', icon: Icons.auto_stories_outlined),
      (label: 'Ruangan', route: '/master/rooms', icon: Icons.meeting_room_outlined),
      (label: 'Jadwal Kelas', route: '/master/schedules', icon: Icons.schedule_outlined),
      (label: 'Users', route: '/security/users', icon: Icons.groups_outlined),
      (label: 'Roles', route: '/security/roles', icon: Icons.admin_panel_settings_outlined),
      (label: 'Permission', route: '/security/permissions', icon: Icons.lock_outline),
      (label: 'Settings', route: '/security/settings', icon: Icons.settings_outlined),
      (label: 'Profil', route: '/profile', icon: Icons.person_outline),
    ];
    final items = allItems
        .where((item) => session.canAccessRoute(item.route))
        .toList(growable: false);
    final selectedIndex = items.indexWhere((item) => route == item.route);

    return Scaffold(
      body: Row(
        children: [
          Container(
            width: 260,
            color: const Color(0xFF102A43),
            child: SafeArea(
              child: Column(
                children: [
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.fromLTRB(20, 20, 20, 16),
                    decoration: const BoxDecoration(
                      gradient: LinearGradient(
                        begin: Alignment.topLeft,
                        end: Alignment.bottomRight,
                        colors: [Color(0xFF0F172A), Color(0xFF1D4ED8)],
                      ),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Row(
                          children: [
                            CircleAvatar(
                              radius: 24,
                              backgroundColor: Colors.white24,
                              child: Text(
                                'SI',
                                style: TextStyle(
                                  color: Colors.white,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                            ),
                            SizedBox(width: 12),
                            Expanded(
                              child: Text(
                                'SIAT Portal',
                                style: TextStyle(
                                  color: Colors.white,
                                  fontSize: 18,
                                  fontWeight: FontWeight.w700,
                                ),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 16),
                        Text(
                          _roleLabel(session.activeRole),
                          style: const TextStyle(
                            color: Colors.white,
                            fontSize: 16,
                            fontWeight: FontWeight.w700,
                          ),
                        ),
                        const SizedBox(height: 6),
                        Text(
                          session.email,
                          style: const TextStyle(color: Color(0xFFDBEAFE)),
                        ),
                        const SizedBox(height: 12),
                        Container(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 12,
                            vertical: 10,
                          ),
                          decoration: BoxDecoration(
                            color: Colors.white.withOpacity(0.12),
                            borderRadius: BorderRadius.circular(16),
                          ),
                          child: Row(
                            children: [
                              const Icon(
                                Icons.shield_outlined,
                                color: Colors.white,
                                size: 18,
                              ),
                              const SizedBox(width: 8),
                              Expanded(
                                child: Text(
                                  '${items.length} modul aktif untuk role ini',
                                  style: const TextStyle(
                                    color: Colors.white,
                                    fontWeight: FontWeight.w600,
                                  ),
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ),
                  const Divider(color: Color(0xFF1E3A5F), height: 1),
                  Expanded(
                    child: ListView.builder(
                      padding: const EdgeInsets.all(12),
                      itemCount: items.length,
                      itemBuilder: (context, index) {
                        final item = items[index];
                        final isSelected = selectedIndex == index;

                        return Padding(
                          padding: const EdgeInsets.only(bottom: 6),
                          child: Material(
                            color: isSelected
                                ? const Color(0xFF1D4ED8)
                                : Colors.transparent,
                            borderRadius: BorderRadius.circular(12),
                            child: InkWell(
                              borderRadius: BorderRadius.circular(12),
                              onTap: () => context.go(item.route),
                              child: Padding(
                                padding: const EdgeInsets.symmetric(
                                  horizontal: 12,
                                  vertical: 12,
                                ),
                                child: Row(
                                  children: [
                                    Icon(
                                      item.icon,
                                      color: isSelected
                                          ? Colors.white
                                          : const Color(0xFFBFDBFE),
                                    ),
                                    const SizedBox(width: 12),
                                    Expanded(
                                      child: Text(
                                        item.label,
                                        style: TextStyle(
                                          color: isSelected
                                              ? Colors.white
                                              : const Color(0xFFBFDBFE),
                                          fontWeight: isSelected
                                              ? FontWeight.w600
                                              : FontWeight.w400,
                                        ),
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                            ),
                          ),
                        );
                      },
                    ),
                  ),
                  Padding(
                    padding: const EdgeInsets.fromLTRB(12, 0, 12, 12),
                    child: OutlinedButton.icon(
                      style: OutlinedButton.styleFrom(
                        minimumSize: const Size.fromHeight(48),
                        foregroundColor: Colors.white,
                        side: const BorderSide(color: Color(0xFF1E3A5F)),
                      ),
                      onPressed: () {
                        controller.logout();
                        context.go('/login');
                      },
                      icon: const Icon(Icons.logout_outlined),
                      label: const Text('Logout'),
                    ),
                  ),
                ],
              ),
            ),
          ),
          Expanded(
            child: Column(
              children: [
                Material(
                  color: Colors.white,
                  child: Padding(
                    padding: const EdgeInsets.all(20),
                    child: Row(
                      children: [
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const Text(
                                'Sistem Informasi Akademik Terpadu',
                                style: TextStyle(
                                  fontSize: 22,
                                  fontWeight: FontWeight.w700,
                                ),
                              ),
                              Text(
                                'Role aktif: ${_roleLabel(session.activeRole)} · ${session.email}',
                                style: const TextStyle(color: Color(0xFF64748B)),
                              ),
                            ],
                          ),
                        ),
                        if (session.canSwitchRole) ...[
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 12),
                            decoration: BoxDecoration(
                              color: const Color(0xFFF8FAFC),
                              borderRadius: BorderRadius.circular(16),
                              border: Border.all(color: const Color(0xFFE2E8F0)),
                            ),
                            child: DropdownButton<AppRole>(
                              value: session.activeRole,
                              underline: const SizedBox.shrink(),
                              borderRadius: BorderRadius.circular(16),
                              onChanged: (value) {
                                if (value != null) {
                                  final targetRoute =
                                      value == session.activeRole ? route : switch (value) {
                                    AppRole.student => '/academic/krs',
                                    AppRole.lecturer => '/lecturer/grades',
                                    AppRole.admin => '/administration/approvals',
                                    AppRole.leader => '/administration/reports',
                                    AppRole.superAdmin => '/security/users',
                                  };
                                  controller.switchRole(value);
                                  context.go(targetRoute);
                                }
                              },
                              items: session.roles
                                  .map(
                                    (role) => DropdownMenuItem(
                                      value: role,
                                      child: Text(_roleLabel(role)),
                                    ),
                                  )
                                  .toList(),
                            ),
                          ),
                        ],
                      ],
                    ),
                  ),
                ),
                Expanded(
                  child: SingleChildScrollView(
                    scrollDirection: Axis.horizontal,
                    child: SizedBox(
                      width: 1400,
                      child: SingleChildScrollView(
                        padding: const EdgeInsets.all(24),
                        child: child,
                      ),
                    ),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
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
}
