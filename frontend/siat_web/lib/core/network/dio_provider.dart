import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../auth/app_session.dart';

const _defaultApiBaseUrl = String.fromEnvironment(
  'SIAT_API_BASE_URL',
  defaultValue: 'http://localhost:8000/api/v1',
);

final dioProvider = Provider<Dio>((ref) {
  final session = ref.watch(sessionProvider);

  return Dio(
    BaseOptions(
      baseUrl: _defaultApiBaseUrl,
      connectTimeout: const Duration(seconds: 15),
      receiveTimeout: const Duration(seconds: 15),
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        if (session.token.isNotEmpty) 'Authorization': 'Bearer ${session.token}',
      },
    ),
  );
});
