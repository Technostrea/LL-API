<?php
namespace App\Enums;

enum RoleEnum: string
{
    case ADMIN = 'admin';
    case OWNER = 'owner';
    case AGENCY = 'agency';
    case TENANT = 'tenant';

    public static function all(): array
    {
        return array_column(self::cases(), 'value');
    }
}
