<?php

namespace App\Services\Governance;

use App\Models\Approval;
use Illuminate\Database\Eloquent\Collection;

class ApprovalService
{
    public function list(): Collection
    {
        return Approval::query()
            ->latest()
            ->get();
    }

    public function decide(string $approvalId, array $payload): Approval
    {
        $approval = Approval::query()->findOrFail($approvalId);
        $approval->update([
            'status' => $payload['decision'],
            'notes' => $payload['notes'] ?? null,
            'decided_at' => now(),
        ]);

        $approval->refresh();

        return $approval;
    }
}
