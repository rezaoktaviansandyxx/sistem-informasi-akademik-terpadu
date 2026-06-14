<?php

namespace App\Repositories\Contracts;

interface KrsRepositoryInterface
{
    public function currentDraft(string $studentId): array;

    public function storeDraftEntry(array $payload): array;
}
