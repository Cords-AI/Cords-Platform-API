<?php

namespace App\Dto\Admin;

use App\Entity\Account;

class AccountData
{
    public string $uid;

    public string $email;

    public ?string $name;

    public ?string $status;

    public bool $isOnboarded = false;

    public ?string $organization;

    public ?string $purpose;

    public function __construct(Account|null $account, object $firebaseUser)
    {
        $this->uid = $firebaseUser->uid;
        $this->email = $firebaseUser->email;
        $this->name = $firebaseUser->displayName ?? null;

        if (!$account) {
            return;
        }
        $this->status = $account->getStatus();
        $profile = $account->getProfile();
        if ($profile) {
            $this->isOnboarded = true;
            $this->organization = $profile->getOrganization();
            $this->purpose = $profile->getPurpose();
        }
    }
}
