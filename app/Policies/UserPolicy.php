<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function view(User $user, User $model): bool
    {
        return $user->canAccessAccount() && ($user->is($model) || $user->is_admin);
    }

    public function update(User $user, User $model): bool
    {
        return $this->view($user, $model);
    }
}
