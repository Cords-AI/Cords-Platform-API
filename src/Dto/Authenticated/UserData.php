<?php

namespace App\Dto\Authenticated;

use App\Entity\Account;

class UserData
{
    public string $id;

    public string $email;

    public string $name;

    public string $initials;

    public ?string $avatar;

    public bool $emailVerified;

    public ?string $status;

    public bool $isAdmin;

    public bool $isOnboarded = false;

    public function __construct($data, Account|null $account)
    {
        $this->id = $data->id;
        $this->email = $data->email;
        $this->name = $data->name;
        $this->initials = $data->initials;
        $this->avatar = $data->avatar;
        $this->emailVerified = $data->emailVerified;
        $this->status = $data->status;
        $this->isAdmin = $data->isAdmin;
        if (!$account) {
            return;
        }
        $this->isOnboarded = !!$account->getProfile();
    }
}
