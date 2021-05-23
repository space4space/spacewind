<?php

namespace Spacewind\Models;

use Illuminate\Database\Eloquent\Model;
use Spacewind\Traits\Sequenced;
use Spacewind\Traits\Logged;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

abstract class User extends Model
{
    use Sequenced;
    use Logged;

    protected $title = 'Пользователь';

    protected $fillable = ['username', 'firstname', 'lastname', 'password', 'user_type_id', 'active'];
    protected $auth_fillable = ['username', 'firstname', 'lastname', 'password'];
    protected $guarded = ['id'];
    protected $appends = ['fullname'];
    public $relations = ['type'];
    

    public function type()
    {
        return $this->belongsTo('UserType', 'user_type_id');
    }

    public function getFullnameAttribute()
    {
        return $this->firstname . ' ' . $this->lastname;
    }

    public function fillFromAuth(array $values)
    {
        foreach ($values as $key => $value) {
            $property = str_replace('auth_', '', $key);
            if (in_array($property, $this->auth_fillable)) {
                $this->attributes[$property] = $value;
            }
        }
    }

    public function setActiveAttribute($value)
    {
        global $configs, $site, $user;
        $this->attributes['active'] = $value;
        if ($value==1){
            $mail = new PHPMailer(true);                              // Passing `true` enables exceptions
            try {
                //Server settings
                $mail->CharSet = 'UTF-8';
                // $mail->SMTPDebug = 2;                                 // Enable verbose debug output
                $mail->isSMTP();                                      // Set mailer to use SMTP
                $mail->Host = $configs->email->smtp->server;  // Specify main and backup SMTP servers
                $mail->SMTPAuth = true;                               // Enable SMTP authentication
                $mail->Username = $configs->email->smtp->login;                 // SMTP username
                $mail->Password = $configs->email->smtp->password;                           // SMTP password
                $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
                $mail->Port = $configs->email->smtp->port;                                    // TCP port to connect to

                //Recipients
                $mail->setFrom($configs->email->from->email, $configs->email->from->sender);
                $mail->addAddress($this->attributes['username']);     // Add a recipient
                // $mail->addReplyTo('info@example.com', 'Information');

                //Content
                $mail->isHTML(true);                                  // Set email format to HTML
                $mail->Subject = 'Вы активированы на сайте '.$site->name;
                $mail->Body = 'Ваш пользователь активирован на <a href="'.$site->url.'">'.$site->name.'</a>';
                $mail->AltBody = 'Ваш пользователь активирован на '.$site->name.' ( '.$site->url.' )';

                $mail->send();
            } catch (Exception $e) {
                $user->message->error = 'Email не отправлен. '.$mail->ErrorInfo;
            }
        }
    }

    public function preprocess($request)
    {
        global $user;
        if (isset($request['user_type_id']) && $request['user_type_id'] < $user->type->id) {
            $request['user_type_id'] = $user->type->id;
        }
        if (isset($request['password']) && $request['password'] == '') {
            unset($request['password']);
        }
        if (isset($request['password'])) {
            $request['password'] = $user->hashPassword($request['password']);
        }

        return $request;
    }

    public function postprocess($result)
    {
        if (isset($result['data'])) {
            foreach ($result['data'] as $res) {
                $res['password'] = '';
            }
        } else {
            $result['password'] = '';
        }

        return $result;
    }
}
