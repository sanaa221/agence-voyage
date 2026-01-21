<?php
// app/Enums/UserRole.php
namespace App\Models\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case CLIENT = 'client';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}