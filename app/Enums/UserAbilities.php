<?php

namespace App\Enums;

enum UserAbilities: string {
    case view = 'view';
    case edit = 'edit';
    case export = 'export';
    case addMarkers = 'addMarkers';
    case upload = 'upload';
    case deleteMarkers = 'deleteMarkers';
    case completeWork = 'completeWork';
    case assignWork = 'assignWork';
    case editDictionaries = 'editDictionaries';
    case import = 'import';
    case editNews = 'editNews';
    case adminUsers = 'adminUsers';

    public function minRole(): UserRole {
        return match ($this) {
            self::view            => UserRole::VIEWER,
            self::export            => UserRole::VIEWER,
            self::edit            => UserRole::EDITOR,
            self::addMarkers      => UserRole::EDITOR,
            self::upload          => UserRole::EDITOR,
            self::deleteMarkers   => UserRole::EDITOR,
            self::completeWork    => UserRole::WORKER,
            self::assignWork      => UserRole::WORK_MANAGER,
            self::editDictionaries=> UserRole::EDITOR,
            self::import          => UserRole::WORK_MANAGER,
            self::editNews        => UserRole::NEWS_MANAGER,
            self::adminUsers      => UserRole::ADMIN,
        };
    }
}
