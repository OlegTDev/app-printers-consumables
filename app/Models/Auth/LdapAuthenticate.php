<?php
namespace App\Models\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Log;

class LdapAuthenticate
{

    private UserProvisioner $userProvisioner = new UserProvisioner();
    private Ldap $ldap = new Ldap();
    
    
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
        [$username, $domain] = $this->splitDomainAndUser($authUser);
        
        // поиск пользователя в LDAP и извлечение данных согласно аттрибутам (self::USER_LDAP_ATTRIBUTES)
        $userLdap = $this->ldap->find($username);
        
        // // поиск/создание пользователя в БД
        $userModel = $this->userProvisioner->get($username, $domain, $userLdap);

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