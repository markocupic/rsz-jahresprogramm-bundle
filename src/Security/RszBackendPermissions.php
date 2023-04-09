<?php

declare(strict_types=1);



namespace Markocupic\RszJahresprogrammBundle\Security;

final class RszBackendPermissions
{
    /**
     * Access is granted if the current user has access to rsz jahresprogramm.
     * Subject must be an operation: create/delete.
     */
    public const USER_CAN_EDIT_RSZ_JAHRESPROGRAMM = 'contao_user.rszjahresprogrammp';
}
