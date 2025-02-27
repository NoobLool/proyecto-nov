<?php

namespace App\Traits;

trait HasRoles
{
    public function hasRole($role){

        return $this->rol === $role;
        
    }
}