<?php

namespace App\Dto\Admin;

use stdClass;

class UserData
{
    public string $uid;

    public string $email;

    public bool $emailVerified;

    public ?string $displayName;

    public ?string $photoURL;

    public bool $isAdmin;

    public int $createdDate;

    public function __construct(stdClass $data)
    {
        $this->uid = $data->uid;
        
        $this->email = $data->email;

        $this->emailVerified = $data->emailVerified;

        $this->displayName = $data->displayName ?? null;
        
        $this->photoURL = $data->photoURL ?? null;
    
        $this->isAdmin = $this->computeIsAdmin($data);

        $this->createdDate = $this->computeCreateDate($data);
    }

    public function computeIsAdmin(stdClass $data): bool
    {
        if(!empty($data->customClaims)) {
            if($data->customClaims->admin && $data->customClaims->admin === true) {
                return true;
            }
        }
        return false;
    }

    private function computeCreateDate(stdClass $data): int
    {
        $creationTime = $data->metadata->creationTime;
        $timestamp = strtotime($creationTime);
        return $timestamp;
    }
}
