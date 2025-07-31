<?php
// app/Auth/RBAC.php
namespace app\Auth;

class RBAC {
    private static $roles = [
        'guest' => [],
        'user' => ['anasayfa', 'profil'],
        'admin' => ['anasayfa', 'profil', 'admin', 'users']
    ];

    public static function hasPermission($role, $permission) {
        return in_array($permission, self::$roles[$role]);
    }
}