<?php

namespace App\Security;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use JsonSerializable;
use Symfony\Component\Security\Core\User\UserInterface;

class User implements UserInterface, JsonSerializable
{
    private ?string $id;

    private string $email;

    private string $name;

    private string $initials;

    private ?string $avatar;

    public static function create($token): ?User
    {
        $user = new User();

        $keyUrl = "https://www.googleapis.com/service_accounts/v1/metadata/x509/securetoken@system.gserviceaccount.com";
        $keys = json_decode(file_get_contents($keyUrl), true);

        $decoded = null;
        foreach($keys as $key) {
            try {
                $key = new Key($key, 'RS256');
                $decoded = JWT::decode($token, $key);

            } catch(\Exception $e) {
            }
        }

        if(!$decoded) {
            return null;
        }

        if(!$decoded->email_verified) {
            return null;
        }

        $user->id = $decoded->user_id;
        $user->email = $decoded->email;
        if(!empty($decoded->name)) {
            $user->name = $decoded->name;
            $user->initials = User::computeInitials($decoded->name);
        }
        $user->avatar = $decoded->picture ?? null;

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
        if(!empty($this->id)) {
            $roles[] = "ROLE_AUTHENTICATED";
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

    public function jsonSerialize(): mixed
    {
        return [
            "id" => $this->id,
            "email" => $this->email,
            "name" => $this->name,
            "initials" => $this->initials,
            "avatar" => $this->avatar
        ];
    }
}
