<?php
namespace App\Enums;

enum PermissionEnum: string
{
    case VIEW_PROPERTIES = 'view-properties';
    case CREATE_PROPERTIES = 'create-properties';
    case UPDATE_PROPERTIES = 'update-properties';
    case DELETE_PROPERTIES = 'delete-properties';

    public static function all(): array
    {
        return array_column(self::cases(), 'value');
    }
}
