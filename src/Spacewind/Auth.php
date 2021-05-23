<?php

namespace Spacewind;

class Auth
{
    public $logged;
    public $registered;
    public $message;
    public $account;
    public $id = null;
    private $salt;
    private $userClass;
    private $hashClass;

    public function __construct(\User $userClass, \UserRememberHash $hashClass)
    {
        $this->userClass = $userClass;
        $this->hashClass = $hashClass;

        ini_set('session.use_strict_mode', 1);
        ini_set('session.gc_maxlifetime', 31104000);
        ini_set('session.cookie_lifetime', 31104000);
        ini_set('session.gc_divisor', 200);

        session_name('auth');
        session_start();
        $this->message = (object) ['success' => null, 'error' => null];
    }

    public function init(array $auth)
    {
        $auth['password'] = isset($auth['auth_password']) ? $this->hashPassword($auth['auth_password']) : null;
        $command = $auth['auth_oper'] ?? 'check';
        try {
            switch ($command) {
                case 'signup': $this->signup($auth); break;
                case 'login': $this->login($auth); break;
                case 'logout': $this->logout(); break;
                case 'check': $this->check(); break;
            }
        } catch (\Exception $e) {
            $this->message->error = $e->getMessage();
        }

        return $this;
    }

    public function signup($auth)
    {
        if (empty($auth['auth_firstname'])) {
            throw new \Exception('Не указано <strong>имя</strong>');
        }
        if (empty($auth['auth_lastname'])) {
            throw new \Exception('Не указана <strong>фамилия</strong>');
        }
        if (empty($auth['auth_username'])) {
            throw new \Exception('Не заполнен <strong>email</strong>');
        }
        if (empty($auth['auth_password'])) {
            throw new \Exception('Пароль не должен быть пустым');
        }
        if ($this->userClass::where('username', $auth['auth_username'])->exists()) {
            throw new \Exception('Такой email уже зарегистрирован в системе.');
        }

        $account = new $this->userClass();
        $account->fillFromAuth($auth);
        $account->save();
        $this->registered = true;
        $this->message->success .= 'Успешная регистрация.';

        return true;
    }

    public function login($auth)
    {
        $account = $this->userClass::where('username', $auth['auth_username'])->where('password', $auth['password'])->first();

        if ($account) {
            if (!$account->active) {
                $this->logged = false;
                throw new \Exception('Ваш аккаунт не активирован. Подождите активации или обратитесь к администратору.');
            } else {
                foreach ($account->getAttributes() as $key => $value) {
                    $this->$key = $value;
                }

                $_SESSION['auth_username'] = $account->username;
                $_SESSION['auth_password'] = $account->password;
                $_SESSION['user_ip'] = $_SERVER['REMOTE_ADDR'];

                $this->logged = true;
                $this->account = $account;
                $this->message->success = 'Успешная аутентификация';
            }
        } else {
            $this->logged = false;
            throw new \Exception('Пользователь не существует, либо пароль введен неверно.');
        }

        return $this->logged;
    }

    public function check()
    {
        if (!empty($this->sessionData())) {
            if (!$this->login($this->sessionData())) {
                $this->logout();
            }

            if (isset($_SESSION['user_ip'])) {
                if ($_SESSION['user_ip'] != $_SERVER['REMOTE_ADDR']) {
                    $this->logout();
                }
            }
        }

        return $this->logged;
    }

    public function logout()
    {
        $this->restartSession();
        $this->logged = false;

        return false;
    }

    public function createPasswordHash($username)
    {
        $account = $this->userClass::where('username', $username)->first();
        if ($account) {
            $hash = new $this->hashClass();
            $hash->user_id = $account->id;
            $hash->hash = substr('abcdefghijklmnopqrstuvwxyz', mt_rand(0, 35), 1).substr(md5(time()), 1);
            $hash->save();

            return $hash;
        } else {
            $this->message->error = 'Пользователь с таким email не найден.';

            return false;
        }
    }

    public function changePassword($hash, $password)
    {
        $hash = $this->hashClass::where('hash', $hash)->whereRaw('`created_at`>DATE_SUB(DATE(NOW()), INTERVAL 7 DAY)')->first();
        if ($hash) {
            $account = $this->userClass::find($hash->user->id);
            $account->password = $this->hashPassword($password);
            $account->save();
            $hash->delete();
            $this->message->success = 'Пароль успешно изменен.';

            return true;
        } else {
            $this->message->error = 'Такой хеш-ключ не существует или устарел.';

            return false;
        }
    }

    private function sessionData()
    {
        if (isset($_SESSION['auth_username']) && isset($_SESSION['auth_password'])) {
            return array('auth_username' => $_SESSION['auth_username'], 'password' => $_SESSION['auth_password']);
        } else {
            return false;
        }
    }

    private function restartSession()
    {
        session_unset();
        session_destroy();
        ini_set('session.use_strict_mode', 1);
        ini_set('session.gc_maxlifetime', 31104000);
        ini_set('session.cookie_lifetime', 31104000);
        ini_set('session.gc_divisor', 200);
        session_name('auth');

        session_start();
        session_regenerate_id(true);
    }

    public function setSalt($salt)
    {
        $this->salt = $salt;

        return $this;
    }

    public function loadRelation($relation)
    {
        if (isset($this->account)) {
            $this->{$relation} = $this->account->{$relation};
        }

        return $this;
    }

    public function hashPassword($password)
    {
        return hash('whirlpool', $password.$this->salt);
    }
}
