<?php

declare(strict_types=1);

namespace App\Services\Project;

use App\Exceptions\ProjectOwnershipException;
use App\Queries\Project\AcceptOwnershipTransferQuery;
use App\Queries\Project\ProposeOwnershipTransferQuery;
use Illuminate\Support\Facades\DB;

readonly class ProjectManagerService {
    public function __construct(
        private AcceptOwnershipTransferQuery $acceptOwnershipTransferQuery,
        private ProposeOwnershipTransferQuery $proposeOwnershipTransferQuery,
    ) {}

    public function proposeOwnershipTransfer(int $projectId, int $userId): void {
        if ($this->proposeOwnershipTransferQuery->hasActiveProposal($projectId)) {
            throw ProjectOwnershipException::ownershipAlreadyProposed();
        }

        $this->proposeOwnershipTransferQuery->execute($projectId, $userId);
    }

    public function acceptOwnershipTransfer(int $projectId, int $userId): void {
        DB::transaction(function () use ($projectId, $userId): void {
            $this->acceptOwnershipTransferQuery->clearProposal($projectId, $userId);
            $this->acceptOwnershipTransferQuery->transferOwner($projectId, $userId);
        });
    }

    public function rejectOwnershipTransfer(int $projectId, int $userId): void {
        $this->acceptOwnershipTransferQuery->clearProposal($projectId, $userId);
    }
}
