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

    public function __construct(stdClass $data)
    {
        $this->uid = $data->uid;
        
        $this->email = $data->email;

        $this->emailVerified = $data->emailVerified;

        $this->displayName = $data->displayName ?? null;
        
        $this->photoURL = $data->photoURL ?? null;
    
        $this->isAdmin = $this->computeIsAdmin($data);
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
}
