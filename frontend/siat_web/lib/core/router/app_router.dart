import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../auth/app_session.dart';
import '../../features/academic/presentation/krs_page.dart';
import '../../features/auth/presentation/login_page.dart';
import '../../features/dashboard/presentation/dashboard_page.dart';
import '../../features/lecturer/presentation/lecturer_grades_page.dart';
import '../../features/portal/presentation/portal_pages.dart';
import '../../features/profile/presentation/profile_page.dart';
import '../../shared/layouts/app_shell.dart';

final appRouterProvider = Provider<GoRouter>((ref) {
  final session = ref.watch(sessionProvider);
  const publicRoutes = <String>{
    '/login',
    '/forgot-password',
    '/reset-password',
  };

  return GoRouter(
    initialLocation: '/login',
    redirect: (context, state) {
      final location = state.matchedLocation;
      final isPublicRoute = publicRoutes.contains(location);

      if (!session.isAuthenticated) {
        return isPublicRoute ? null : '/login';
      }

      if (isPublicRoute) {
        return session.defaultRoute;
      }

      if (!session.canAccessRoute(location)) {
        return session.defaultRoute;
      }

      return null;
    },
    routes: [
      GoRoute(
        path: '/login',
        builder: (context, state) => const LoginPage(),
      ),
      GoRoute(
        path: '/forgot-password',
        builder: (context, state) => const ForgotPasswordPage(),
      ),
      GoRoute(
        path: '/reset-password',
        builder: (context, state) => const ResetPasswordPage(),
      ),
      ShellRoute(
        builder: (context, state, child) => AppShell(child: child),
        routes: [
          GoRoute(
            path: '/dashboard',
            builder: (context, state) => const DashboardPage(),
          ),
          GoRoute(
            path: '/academic/krs',
            builder: (context, state) => const KrsPage(),
          ),
          GoRoute(
            path: '/lecturer/grades',
            builder: (context, state) => const LecturerGradesPage(),
          ),
          GoRoute(
            path: '/profile',
            builder: (context, state) => const ProfilePage(),
          ),
          GoRoute(
            path: '/academic/khs',
            builder: (context, state) => const KhsPage(),
          ),
          GoRoute(
            path: '/academic/transcript',
            builder: (context, state) => const TranscriptPage(),
          ),
          GoRoute(
            path: '/academic/schedule',
            builder: (context, state) => const SchedulePage(),
          ),
          GoRoute(
            path: '/academic/attendance',
            builder: (context, state) => const StudentAttendancePage(),
          ),
          GoRoute(
            path: '/lecturer/classes',
            builder: (context, state) => const LecturerClassesPage(),
          ),
          GoRoute(
            path: '/lecturer/attendance',
            builder: (context, state) => const LecturerAttendancePage(),
          ),
          GoRoute(
            path: '/lecturer/summary',
            builder: (context, state) => const LecturerSummaryPage(),
          ),
          GoRoute(
            path: '/administration/announcements',
            builder: (context, state) => const AnnouncementsPage(),
          ),
          GoRoute(
            path: '/administration/calendar',
            builder: (context, state) => const AcademicCalendarPage(),
          ),
          GoRoute(
            path: '/administration/letters',
            builder: (context, state) => const AcademicLettersPage(),
          ),
          GoRoute(
            path: '/administration/verifications',
            builder: (context, state) => const VerificationsPage(),
          ),
          GoRoute(
            path: '/administration/approvals',
            builder: (context, state) => const ApprovalsPage(),
          ),
          GoRoute(
            path: '/administration/reports',
            builder: (context, state) => const ReportsPage(),
          ),
          GoRoute(
            path: '/administration/audit',
            builder: (context, state) => const AuditTrailPage(),
          ),
          GoRoute(
            path: '/administration/activity-logs',
            builder: (context, state) => const ActivityLogsPage(),
          ),
          GoRoute(
            path: '/master/departments',
            builder: (context, state) => const DepartmentsPage(),
          ),
          GoRoute(
            path: '/master/curricula',
            builder: (context, state) => const CurriculaPage(),
          ),
          GoRoute(
            path: '/master/rooms',
            builder: (context, state) => const RoomsPage(),
          ),
          GoRoute(
            path: '/master/schedules',
            builder: (context, state) => const SchedulesPage(),
          ),
          GoRoute(
            path: '/security/users',
            builder: (context, state) => const UsersSecurityPage(),
          ),
          GoRoute(
            path: '/security/roles',
            builder: (context, state) => const RolesPage(),
          ),
          GoRoute(
            path: '/security/permissions',
            builder: (context, state) => const PermissionsPage(),
          ),
          GoRoute(
            path: '/security/settings',
            builder: (context, state) => const SettingsPage(),
          ),
        ],
      ),
    ],
    errorBuilder: (context, state) => Scaffold(
      body: Center(
        child: Text('Halaman tidak ditemukan: ${state.uri}'),
      ),
    ),
  );
});
