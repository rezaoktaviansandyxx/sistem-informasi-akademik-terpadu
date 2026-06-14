import 'package:flutter_riverpod/flutter_riverpod.dart';

enum AppRole { student, lecturer, admin, leader, superAdmin }

const Map<AppRole, List<String>> _roleRoutePrefixes = {
  AppRole.student: [
    '/dashboard',
    '/academic/',
    '/profile',
  ],
  AppRole.lecturer: [
    '/dashboard',
    '/lecturer/',
    '/profile',
  ],
  AppRole.admin: [
    '/dashboard',
    '/academic/',
    '/lecturer/',
    '/administration/',
    '/master/',
    '/security/',
    '/profile',
  ],
  AppRole.leader: [
    '/dashboard',
    '/administration/',
    '/profile',
  ],
  AppRole.superAdmin: [
    '/dashboard',
    '/security/',
    '/profile',
  ],
};

class AppSession {
  const AppSession({
    required this.name,
    required this.email,
    required this.activeRole,
    required this.isAuthenticated,
    required this.token,
    required this.roles,
    required this.permissions,
  });

  final String name;
  final String email;
  final AppRole activeRole;
  final bool isAuthenticated;
  final String token;
  final List<AppRole> roles;
  final List<String> permissions;

  bool get canSwitchRole => roles.length > 1;

  bool canAccessRoute(String route) {
    final prefixes = _roleRoutePrefixes[activeRole] ?? const ['/profile'];
    return prefixes.any((prefix) => route == prefix || route.startsWith(prefix));
  }

  String get defaultRoute {
    return switch (activeRole) {
      AppRole.student => '/academic/krs',
      AppRole.lecturer => '/lecturer/grades',
      AppRole.admin => '/administration/approvals',
      AppRole.leader => '/administration/reports',
      AppRole.superAdmin => '/security/users',
    };
  }

  AppSession copyWith({
    String? name,
    String? email,
    AppRole? activeRole,
    bool? isAuthenticated,
    String? token,
    List<AppRole>? roles,
    List<String>? permissions,
  }) {
    return AppSession(
      name: name ?? this.name,
      email: email ?? this.email,
      activeRole: activeRole ?? this.activeRole,
      isAuthenticated: isAuthenticated ?? this.isAuthenticated,
      token: token ?? this.token,
      roles: roles ?? this.roles,
      permissions: permissions ?? this.permissions,
    );
  }
}

class SessionController extends StateNotifier<AppSession> {
  SessionController()
      : super(
          const AppSession(
            name: 'Guest',
            email: 'guest@siat.local',
            activeRole: AppRole.student,
            isAuthenticated: false,
            token: '',
            roles: [AppRole.student],
            permissions: [],
          ),
        );

  void login({
    required String email,
    required AppRole role,
    String name = '',
    String token = '',
    List<AppRole>? roles,
    List<String>? permissions,
  }) {
    state = AppSession(
      name: name.isEmpty ? _displayName(role) : name,
      email: email,
      activeRole: role,
      isAuthenticated: true,
      token: token,
      roles: roles ?? [role],
      permissions: permissions ?? const [],
    );
  }

  void switchRole(AppRole role) {
    if (!state.roles.contains(role)) {
      return;
    }
    state = state.copyWith(activeRole: role);
  }

  void logout() {
    state = const AppSession(
      name: 'Guest',
      email: 'guest@siat.local',
      activeRole: AppRole.student,
      isAuthenticated: false,
      token: '',
      roles: [AppRole.student],
      permissions: [],
    );
  }

  String _displayName(AppRole role) {
    switch (role) {
      case AppRole.student:
        return 'Mahasiswa Aktif';
      case AppRole.lecturer:
        return 'Dosen Pengampu';
      case AppRole.admin:
        return 'Admin Akademik';
      case AppRole.leader:
        return 'Pimpinan Institusi';
      case AppRole.superAdmin:
        return 'Super Admin';
    }
  }
}

AppRole roleFromCode(String code) {
  return switch (code) {
    'lecturer' => AppRole.lecturer,
    'admin' => AppRole.admin,
    'leader' => AppRole.leader,
    'super_admin' => AppRole.superAdmin,
    _ => AppRole.student,
  };
}

final sessionProvider =
    StateNotifierProvider<SessionController, AppSession>((ref) {
  return SessionController();
});
