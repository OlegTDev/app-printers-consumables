<?php
namespace App\Models\Auth;

class Ldap
{

    private const array USER_LDAP_ATTRIBUTES = [
        'asString' => [
            'username',
            'userPrincipalName',
            'cn',
            'title',
            'department',
            'mail',
            'telephoneNumber',
            'company',
        ],
        'asArray' => [
            'memberOf',
        ],
    ];

    public function __construct(
        private LdapFinder $ldapFinder,
    ) {}

    public function find(string $username)
    {
        $userModelLdap = $this->ldapFinder->query($username);

        if (!$userModelLdap) {
            return [];
        }

        $userAttributes = [];
        foreach(self::USER_LDAP_ATTRIBUTES['asString'] as $attribute) {
            $userAttributes[$attribute] = $userModelLdap->getFirstAttribute($attribute);
        }
        foreach(self::USER_LDAP_ATTRIBUTES['asArray'] as $attribute) {
            $userAttributes[$attribute] = $userModelLdap->getAttributeValue($attribute);
        }

        return $userAttributes;
    }

}
