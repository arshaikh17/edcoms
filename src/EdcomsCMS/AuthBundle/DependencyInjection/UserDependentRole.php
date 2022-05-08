<?php
namespace EdcomsCMS\AuthBundle\DependencyInjection;

use Symfony\Component\Security\Core\Role\RoleInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserDependentRole implements RoleInterface
{
    private $user;
    private $group;

    public function __construct(UserInterface $user, \EdcomsCMS\AuthBundle\Entity\cmsUserGroups $group)
    {
        $this->user = $user;
        $this->group = $group->getName();
    }

    public function getRole()
    {
        return 'ROLE_'.strtoupper($this->group);
    }
}