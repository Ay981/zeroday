<?php

namespace App\Policies;

use App\Models\Report;
use App\Models\User;

class ReportPolicy
{
    private function isOwner(User $user, Report $report): bool
    {
        return $user->id === $report->user_id;
    }

    public function create(User $_user): bool
    {
        return true;
    }

    public function view(User $user, Report $report): bool
    {
        return true;
    }

    public function viewAny(User $_user): bool
    {
        return true;
    }

    public function update(User $user, Report $report): bool
    {
        return $this->isOwner($user, $report);
    }

    public function delete(User $user, Report $report): bool
    {
        return $this->isOwner($user, $report);
    }
}
