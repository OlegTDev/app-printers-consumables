<?php

namespace App\Models\Auth;

/**
 * Поиск или создание пользователя 
 * 
 * Обязанность класса:
 * 1. найти пользователя в БД через посредника $this->userProvisioner (если найден возвращаем его)
 * 2. если не найден, то считаем, что это новый пользователь
 * - ищем этого пользователя в LDAP (через $this->ldap) по его имени
 * - если пользователь найден в LDAP, то передаем данные в $this->userProvisioner для создания нового пользователя и возвращаем его
 */
class LdapUser
{
    
    private UserProvisioner $userProvisioner = new UserProvisioner();
    private Ldap $ldap = new Ldap();    


    public function findOrCreate(string $username, string $domain)
    {
        $user = $this->userProvisioner->find($username);
        if (!$user) {
            $ldapUserAttr = $this->ldap->find($username);
            if ($ldapUserAttr) {
                $user = $this->userProvisioner->get($username, $domain, $ldapUserAttr);              
            }
        }
        return $user;
    }

}
