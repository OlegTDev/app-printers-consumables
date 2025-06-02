<?php
namespace App\Models\Auth;

class Ldap
{
    /**
     * Аттрибуты, которые будут извлечены из LDAP
     * ['attribute1', 'attribute2:array']
     */
    private CONST USER_LDAP_ATTRIBUTES = ['userPrincipalName', 'cn', 'title', 'department', 'mail', 'memberOf:array', 'telephoneNumber', 'company'];    

    /**
     * Поиск пользователя в LDAP
     * @param string $username имя пользователя
     * @return bool|array|array{username: string|bool}
     */
    public function find(string $username)
    {
        $ldapFinder = new LdapFinder(
            host: env('LDAP_SERVER_NAME'),
            port: env('LDAP_SERVER_PORT'),
            dn: env('LDAP_BASE_DN'),
            username: env('LDAP_BIND_USERNAME'),
            password: env('LDAP_BIND_PASSWORD'),
        );

        $dataLdap = $ldapFinder->query('(sAMAccountName=' . $username . ')');

        if (empty($dataLdap)) {
            return false;
        }

        $userAttributes = ['username' => $username];

        foreach(self::USER_LDAP_ATTRIBUTES as $attribute) {
            $attributeExplode = explode(':', $attribute);
            $userAttributes[$attributeExplode[0]] = (!empty($attributeExplode[1]) && strtolower($attributeExplode[1]) === 'array')
                ? ($dataLdap->getAttribute($attributeExplode[0]) ?? []) // array
                : ($dataLdap->getAttribute($attributeExplode[0])[0] ?? null); // string
        }
        return $userAttributes;
    }

}