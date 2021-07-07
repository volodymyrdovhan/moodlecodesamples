<?php

namespace local_intellicart\services;

/**
 * Jitsi functions
 *
 * @package    local_intellicart
 * @author     Volodymyr Dovhan <vlad@intelliboard.net>
 * @copyright  2020 IntelliBoard
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use \local_intellicart\payment;

/**
 * Jitsi functions.
 *
 * @package    local_intellicart
 * @author     Volodymyr Dovhan <vlad@intelliboard.net>
 * @copyright  2021 IntelliBoard
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class jitsi_service {

    const ROLE_OWNER = 'owner';
    const ROLE_MEMBER = 'member';

    private $jitsidomain;
    private $jitsiappid;
    private $jitsisecret;
    private $jitsimanagerbuttons;
    private $jitsimemberbuttons;
    private $jitsidisabledeeplinking;
    private $jitsienablelobby;
    private $jitsipassword;
    private $jitsiidentifier;

    /**
     * jitsi_service constructor.
     */
    public function __construct() {
        if (self::enabled()) {
            $this->set_config();
        }
    }

    /**
     * @throws \dml_exception
     */
    private function set_config() {
        $this->jitsidomain = get_config('local_intellicart', 'jitsidomain');
        $this->jitsiappid = get_config('local_intellicart', 'jitsiappid');
        $this->jitsisecret = get_config('local_intellicart', 'jitsisecret');
        $this->jitsimanagerbuttons = $this->get_jitsimanagerbuttons();
        $this->jitsimemberbuttons = $this->get_jitsimemberbuttons();;
        $this->jitsidisabledeeplinking = get_config('local_intellicart', 'jitsidisabledeeplinking');
        $this->jitsienablelobby = get_config('local_intellicart', 'jitsienablelobby');
        $this->jitsipassword = get_config('local_intellicart', 'jitsipassword');
        $this->jitsiidentifier = get_config('local_intellicart', 'jitsiidentifier');
    }

    /**
     * @return bool
     * @throws \dml_exception
     */
    public static function enabled() {
        return ((bool)get_config('local_intellicart', 'jitsienable')
                and !empty(get_config('local_intellicart', 'jitsidomain')));
    }

    /**
     * @return false|mixed|object|string|null
     * @throws \dml_exception
     */
    public function get_jitsimanagerbuttons() {
        $default = 'microphone, camera, closedcaptions, desktop, fullscreen, fodeviceselection, hangup, profile,
                    chat, videoquality, filmstrip, invite, feedback, shortcuts, tileview, videobackgroundblur,
                    download, mute-everyone, security, help, stats, recording';

        return (!empty(get_config('local_intellicart', 'jitsimanagerbuttons')))
                ? get_config('local_intellicart', 'jitsimanagerbuttons')
                : $default;
    }

    /**
     * @return false|mixed|object|string|null
     * @throws \dml_exception
     */
    public function get_jitsimemberbuttons() {
        $default = 'microphone, camera, closedcaptions, desktop, fullscreen, fodeviceselection, hangup, profile,
                    chat, livestreaming, etherpad, sharedvideo, settings, raisehand, videoquality, filmstrip,
                    feedback, tileview, videobackgroundblur, download, help';

        return (!empty(get_config('local_intellicart', 'jitsimemberbuttons')))
                ? get_config('local_intellicart', 'jitsimemberbuttons')
                : $default;
    }

    /**
     * @param $context
     * @param int $expiration
     * @return string
     */
    public function generate_jwt($context, $expiration = 0) {
        $header = json_encode([
            "kid" => "jitsi/custom_key_name",
            "typ" => "JWT",
            "alg" => "HS256"
        ], JSON_UNESCAPED_SLASHES);
        $base64urlheader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));

        $payload  = json_encode([
            "context" => [
                "user" => [
                    "affiliation" => $context->role,
                    "avatar" => $context->userpicture,
                    "name" => $context->userfullname,
                    "email" => $context->useremail,
                    "id" => ""
                ],
                "group" => ""
            ],
            "aud" => "jitsi",
            "iss" => $this->jitsiappid,
            "sub" => $this->jitsidomain,
            "room" => $context->roomname,
            "exp" => ($expiration) ? $expiration : time() + 24 * 3600,
            "moderator" => $context->role == self::ROLE_OWNER
        ], JSON_UNESCAPED_SLASHES);

        $base64urlpayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
        $signature = hash_hmac('sha256', $base64urlheader . "." . $base64urlpayload, $this->jitsisecret, true);
        $base64urlsignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        return $base64urlheader . "." . $base64urlpayload . "." . $base64urlsignature;
    }

    /**
     * @param $role
     * @return string
     */
    private function get_buttons($role) {
        $buttonssetting = ($role == self::ROLE_OWNER) ? $this->jitsimanagerbuttons : $this->jitsimemberbuttons;
        $buttonsarr = explode(',', $buttonssetting);
        $buttons = [];

        if ($buttonsarr and count($buttonsarr)) {
            foreach ($buttonsarr as $button) {
                $buttons[] = "'" . trim($button) . "'";
            }
        }

        return implode(', ', $buttons);
    }

    /**
     * @param $roomname
     * @return mixed|string
     */
    protected function get_roomname($roomname) {
        return ($this->jitsiidentifier) ? $this->jitsiidentifier . '-' . $roomname : $roomname;
    }

    /**
     * @param $params
     * @return mixed
     * @throws \coding_exception
     */
    public function display($params) {
        global $OUTPUT, $PAGE;

        $user = $params['user'];
        $userpicture = new \user_picture($user);

        $context = new \stdClass();
        $context->jitsidomain = $this->jitsidomain;
        $context->roomname = $this->get_roomname($params['roomname']);
        $context->userfullname = fullname($user);
        $context->useremail = $user->email;
        $context->userpicture = $userpicture->get_url($PAGE, $OUTPUT)->out();
        $context->role = $params['role'];
        $context->ismoderator = ($params['role'] == self::ROLE_OWNER);
        $context->returnurl = $params['returnurl'];
        $context->password = $this->jitsipassword;
        $context->jitsienablelobby = $this->jitsienablelobby;
        $context->jitsidisabledeeplinking = $this->jitsidisabledeeplinking;
        $context->buttons = $this->get_buttons($context->role);

        $context->jwt = (!empty($this->jitsiappid) && !empty($this->jitsisecret)) ? $this->generate_jwt($context) : null;

        return $OUTPUT->render_from_template('local_intellicart/jitsi-meet', $context);
    }
}
