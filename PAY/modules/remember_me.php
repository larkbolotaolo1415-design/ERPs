<?php

require_once __DIR__ . '/../init.php';
// sql helpers are auto-loaded via init.php

function remember_me($is_checked, $user_id, $days_length)
{
    global $config;

    // CONFIRM IS CHECKED AND THERE IS NO R-SESSION ID YET
    if ($is_checked == '1' && !isset($_COOKIE['remember_me'])) {

        // ADD 30 DAYS FROM NOW
        // SA PHP NAG DATE KASI BAKA MAGING INACCURATE TIMEZONE NG SQL
        $date = new DateTime();
        $date->modify('+' . strval($days_length) . ' days');
        $date = $date->format('Y-m-d H:i:s');

        $generated_r_session_id = secureSessionID($config['r_session_secret_key'], $config['r_session_id_length']);

        $is_remember_me_session_created = insert("INSERT INTO remember_sessions (r_session_id, user_id, login_time, expiry_date,r_session_status) VALUES (?, ?, NOW(), ?, 'active')", "sis", $generated_r_session_id, $user_id, $date);

        // REMEMBER ME ONLY CHECKED FOR 30 DAYS
        if ($is_remember_me_session_created) {
            setcookie("remember_me_is_checked", true, time() + (86400 * $config['default_expiry_days']), "/");
            return $generated_r_session_id;
        }

        return "";
    } else {
        if (isset($_COOKIE['remember_me'])) {
            // UPDATE R_SESSION'S STATUS TO ENDED
            $is_remember_me_session_status_updated = update("UPDATE remember_sessions SET r_session_status = 'ended' WHERE r_session_id = ? AND user_id = ?", "si", $_COOKIE['remember_me'], $user_id);

            // UNCHECK REMEMBER ME: clear remember cookies
            if ($is_remember_me_session_status_updated) {
                setcookie("remember_me", "", time() - 3600, "/");
                setcookie("remember_me_is_checked", "", time() - 3600, "/");
            }
        }

        return "";
    }
    return "";
}
