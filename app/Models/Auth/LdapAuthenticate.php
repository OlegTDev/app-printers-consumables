<?php
namespace App\Models\Auth;

use App\Models\Auth\LdapFinder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Log;

class LdapAuthenticate
{

    /**
     * Аттрибуты, которые будут извлечены из LDAP
     * ['attribute1', 'attribute2:array']
     */
    CONST USER_LDAP_ATTRIBUTES = ['userPrincipalName', 'cn', 'title', 'department', 'mail', 'memberOf:array', 'telephoneNumber', 'company'];

    /**
     * Ldap аутентификация
     * @param Request $request
     * @return bool
     */
    public function login(Request $request): bool
    {
        // извлечение имени пользователя передаваемое веб-сервером
        $authUser = $this->getAuthUserName( $request );        
        
        // разделение этого имени на имя и домен (Domain\Name -> [Name, Domain])
        list($username, $domain) = $this->splitDomainAndUser($authUser);
        
        // поиск пользователя в LDAP и извлечение данных согласно аттрибутам (self::USER_LDAP_ATTRIBUTES)
        $userAttributes = $this->getLdapData($username);

        // поиск/создание пользователя в БД
        $userProvisioner = new UserProvisioner($username, $domain, $userAttributes);
        $userModel = $userProvisioner->get();                
        
        // пользователь заблокирован (удален)
        if ($userModel->deleted_at) {
            abort(401, 'Пользователь заблокирован или удален');
        }

        // аутентификация
        Auth::login($userModel, true);

        return true;
    }

    /**
     * Извлечение имени пользователя из параметра веб-сервера (например, AUTH_USER)
     * @param Request $request
     */
    private function getAuthUserName(Request $request)
    {
        $serverAttr = env('LDAP_SERVER_ATTRIBUTE', 'AUTH_USER');
        $authUser = $request->server->get($serverAttr);
        if (empty($authUser)) {
            Log::error("Параметр сервера $serverAttr пустой или не задан. Убедитесь, что на веб-сервере включена аутентификация через LDAP!");
            abort(401, 'Не удалось определить имя пользователя (см. лог)');
        }
        return $authUser;
    }

    /**
     * Поиск пользователя в LDAP и извлечение его данных
     * @param string $username имя пользователя
     * @return array|array{username: string}
     */
    private function getLdapData(string $username)
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
            Log::error("Пользователь '$username' не найден. Проверьте настройки к LDAP и убедитесь, что пользователь присутствует в LDAP!");
            abort(401, 'Не удалось найти пользователя в LDAP (см. лог)');
        }

        $userAttributes = ['username' => $username];
        
        foreach(self::USER_LDAP_ATTRIBUTES as $attribute) {
            $attributeExplode = explode(':', $attribute);
            // array
            if (!empty($attributeExplode[1]) && strtolower($attributeExplode[1]) === 'array') {
                $userAttributes[$attributeExplode[0]] = $dataLdap->getAttribute($attributeExplode[0]) ?? [];
            } 
            // string
            else {
                $userAttributes[$attributeExplode[0]] = $dataLdap->getAttribute($attributeExplode[0])[0] ?? null;
            }
        }
        return $userAttributes;
    }    


    /**
     * Разбор имени пользователя и домена из полного имени пользователя
     * @param string $fullUsername полное имя пользователя с доменом
     * @return array
     */
    private function splitDomainAndUser(string $fullUsername): array
    {
        $splitUsername = explode('\\', $fullUsername);
        return [
            $splitUsername[1] ?? '',
            $splitUsername[0] ?? '.',
        ];        
    }
}