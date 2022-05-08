<?php
namespace EdcomsCMS\AuthBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
/**
 * This class is used for handling access restrictions
 */

class AccessControl extends \Twig_Extension {

    const ADMIN_GROUP_NAME = 'cms_admin';
    private $user = null;
    private $groups;
    private $em;
    private $container;
    public function __construct($doctrine, $container, TokenStorage $tokenStorage)
    {
        $this->doctrine = $doctrine;
        $this->container = $container;
        $this->tokenStorage = $tokenStorage;
    }
    public function getName() {
        return 'AccessControl';
    }
    private function set_user()
    {
        $this->user = $this->tokenStorage->getToken()->getUser();
    }
    public function get_access_by_item($item)
    {
        
    }
    public function get_access_by_context($context)
    {
        
    }

    /**
     * Get an array of permissions for the currently authenticated user
     * Function takes in to account whether user is admin and calculates admin access for all permissions in DB
     *
     * @return array
     */
    public function get_permissions()
    {
        $return = [];
        //check if user is cms_admin
        $isAdmin = self::has_group(self::ADMIN_GROUP_NAME);
        if ($isAdmin) {
            //if user is admin then we need to get all the permissions for the site from the DB.
            //These are then run through the $this->has_permission function to find if any permissions
            //have been explicitly removed from the admins.
            $permissionsRepo = $this->doctrine->getManager('edcoms_cms')->getRepository('EdcomsCMSAuthBundle:cmsGroupPerms');
            $permissions = $permissionsRepo->findAll();
            if (count($permissions) > 0) {
                foreach ($permissions as &$permission) {
                    $permission->setValue($this->has_permission($permission->getContext(), $permission->getName()));
                }
                $return = $permissions;
            }
        } else {
            //if false return only the permissions explicit for this user
            //Get user's groups
            $groups = $this->get_groups();

            //merge all permissions into an array
            foreach($groups as $group) {
                $permissions = $group->getPerms()->toArray();
                $return = array_merge($return, $permissions);
            }
        }

        return $return;
    }
    public function get_groups()
    {
        if (is_null($this->user)) {
            $this->set_user();
        }
        $this->groups = $this->user->getGroups();
        return $this->groups;
    }
    public function get_user_default_permission()
    {
        // loop through each of the groups, if one is false, it takes priority \\
        $default = true;
        $this->get_groups();
        foreach ($this->groups as $group) {
            if (!$group->getDefaultValue()) {
                $default = false;
            }
        }
        return $default;
    }
    public function has_group($name)
    {
        $groups = $this->get_groups();
        $group = $groups->filter(function($grp) use ($name) {
            if ($grp->getName() === $name) {
                return true;
            }
            return false;
        });
        if ($group->count() > 0) {
            return true;
        }
        return false;
    }
    public function has_permission($context, $name)
    {
        $groups = $this->get_groups();
        // get all permissions for my groups \\
        $perm = true;//default to true
        $eperm = null;
        foreach ($groups as $group) {
            if (!$group->getDefaultValue()) {
                $perm = false;
            }
            $perms = $group->getPerms()->filter(function($item) use ($context, $name) {
                if ($item->getContext() === $context && $item->getName() === $name) {
                    return true;
                }
                return false;
            });
            if ($perms->count() > 0) {
                // this is the permission we wanted \\
                $eperm = $perms->first()->getValue();
                if (!$eperm) {
                    // if it's set to false then end the loop as explicitly false prioritises everything \\
                    break;
                }
            }
        }
        if (!is_null($eperm) && (!$eperm || ($eperm && !$perm))) {
            $perm = $eperm;
        }
        return $perm;
    }
}

