<?php

namespace App\Security;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use stdClass;
use Symfony\Component\Security\Core\User\UserInterface;

class User implements UserInterface
{
    private ?string $id;

    private string $email;

    private bool $emailVerified = false;

    private string $name;

    private string $initials;

    private ?string $avatar;

    private ?string $status = "pending";

    private bool $isAdmin = false;

    public static function create($token, $keyUrl = "https://www.googleapis.com/identitytoolkit/v3/relyingparty/publicKeys"): ?User
    {
        $user = new User();

        $keys = json_decode(file_get_contents($keyUrl), true);

        $decoded = null;
        foreach ($keys as $key) {
            try {
                $key = new Key($key, 'RS256');
                $decoded = JWT::decode($token, $key);

            } catch (\Exception $e) {
            }
        }

        if (!$decoded) {
            return null;
        }

        if ($decoded->email_verified) {
            $user->emailVerified = true;
        }

        $user->id = $decoded->user_id;
        $user->email = $decoded->email;
        if (!empty($decoded->name)) {
            $user->name = $decoded->name;
            $user->initials = User::computeInitials($decoded->name);
        } else {
            $user->initials = strtoupper(substr($decoded->email, 0, 1));
        }
        $user->avatar = $decoded->picture ?? null;

        if (!empty($decoded->admin)) {
            $user->isAdmin = $decoded->admin;
        }

        return $user;
    }

    public static function createFromFireBaseUser(?stdClass $fireBaseUser): ?User
    {
        $user = new User();

        if (!$fireBaseUser) {
            return null;
        }

        $user->id = $fireBaseUser->uid;
        $user->email = $fireBaseUser->email;

        if ($fireBaseUser->emailVerified) {
            $user->emailVerified = true;
        }

        if (!empty($fireBaseUser->displayName)) {
            $user->name = $fireBaseUser->displayName;
            $user->initials = User::computeInitials($fireBaseUser->displayName);
        } else {
            $user->initials = strtoupper(substr($fireBaseUser->email, 0, 1));
        }

        $user->avatar = $fireBaseUser->photoURL ?? null;

        if (!empty($fireBaseUser->customClaims->admin)) {
            $user->isAdmin = $fireBaseUser->customClaims->admin;
        }

        return $user;
    }

    private static function computeInitials(string $name): string
    {
        $parts = explode(" ", $name);
        $firstName = array_shift($parts);
        $lastName = array_pop($parts);
        return substr($firstName, 0, 1) . substr($lastName, 0, 1);
    }

    public function getRoles(): array
    {
        $roles = [];
        if (!empty($this->id)) {
            $roles[] = "ROLE_AUTHENTICATED";
        }
        if ($this->emailVerified) {
            $roles[] = "ROLE_VERIFIED";
        }
        if ($this->isAdmin) {
            $roles[] = "ROLE_ADMIN";
        }
        if (isset($this->status) && $this->status === 'approved') {
            $roles[] = "ROLE_APPROVED";
        }
        return $roles;
    }

    public function eraseCredentials()
    {
    }

    public function getUserIdentifier(): string
    {
        return $this->id ?? 0;
    }

    public function setStatus(?string $status)
    {
        $this->status = $status;
    }

    public function dto(): object
    {
        return (object)[
            "id" => $this->id,
            "email" => $this->email,
            "name" => $this->name ?? "",
            "initials" => $this->initials ?? "",
            "avatar" => $this->avatar,
            "emailVerified" => $this->emailVerified,
            "status" => $this->status ?? null,
            "isAdmin" => $this->isAdmin,
        ];
    }
}
