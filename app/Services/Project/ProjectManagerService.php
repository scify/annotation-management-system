<?php

declare(strict_types=1);

namespace App\Services\Project;

use App\Exceptions\ProjectOwnershipException;
use App\Queries\Project\AcceptOwnershipTransferQuery;
use App\Queries\Project\ProposeOwnershipTransferQuery;
use App\Queries\Project\RemoveProjectManagerQuery;
use App\Queries\Project\RequestToLeaveQuery;
use Illuminate\Support\Facades\DB;
use Throwable;

readonly class ProjectManagerService {
    public function __construct(
        private AcceptOwnershipTransferQuery $acceptOwnershipTransferQuery,
        private ProposeOwnershipTransferQuery $proposeOwnershipTransferQuery,
        private RemoveProjectManagerQuery $removeProjectManagerQuery,
        private RequestToLeaveQuery $requestToLeaveQuery,
    ) {}

    public function proposeOwnershipTransfer(int $projectId, int $userId): void {
        if ($this->proposeOwnershipTransferQuery->hasActiveProposal($projectId)) {
            throw ProjectOwnershipException::ownershipAlreadyProposed();
        }

        $this->proposeOwnershipTransferQuery->execute($projectId, $userId);
    }

    /**
     * @throws Throwable
     */
    public function acceptOwnershipTransfer(int $projectId, int $userId): int {
        $oldOwnerUserId = $this->acceptOwnershipTransferQuery->getOwnerUserId($projectId);

        DB::transaction(function () use ($projectId, $userId): void {
            $this->acceptOwnershipTransferQuery->clearProposal($projectId, $userId);
            $this->acceptOwnershipTransferQuery->transferOwner($projectId, $userId);
        });

        return $oldOwnerUserId;
    }

    public function rejectOwnershipTransfer(int $projectId, int $userId): void {
        $this->acceptOwnershipTransferQuery->clearProposal($projectId, $userId);
    }

    public function cancelOwnershipTransfer(int $projectId, int $userId): void {
        $this->acceptOwnershipTransferQuery->clearProposal($projectId, $userId);
    }

    public function removeManager(int $projectId, int $userId): void {
        $this->removeProjectManagerQuery->execute($projectId, $userId);
    }

    public function requestToLeave(int $projectId, int $userId): void {
        $this->requestToLeaveQuery->execute($projectId, $userId);
    }

    public function cancelRequestToLeave(int $projectId, int $userId): void {
        $this->requestToLeaveQuery->clear($projectId, $userId);
    }

    public function rejectRequestToLeave(int $projectId, int $userId): void {
        $this->requestToLeaveQuery->clear($projectId, $userId);
    }

    public function acceptRequestToLeave(int $projectId, int $userId): void {
        $this->removeProjectManagerQuery->execute($projectId, $userId);
    }
}
