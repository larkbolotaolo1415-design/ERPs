<?php

include_once __DIR__ . '/../init.php';
// sql helpers are auto-loaded via init.php

// LOGIN ATTEMPT
function insert_login_attempt($user_id, $status)
{
    return insert("INSERT INTO login_attempts (user_id, attempt_time, login_status) VALUES (?, NOW(), ?)", "is", $user_id, $status);
}

// LOGOUT
function logout_session($ses_id)
{
    // REMOVE SESSION ID IF EXISTENT
    // search for the session first
    $sessions = select("SELECT * FROM sessions WHERE session_id = ? LIMIT 1", "s", $ses_id);
    // UPDATE SESSION STATUS TO ENDED
    if (!empty($sessions)) {
        return update("UPDATE sessions SET logout_time = NOW(), session_status = 'ended' WHERE session_id = ?", "s", $ses_id);
    }
    return false;
}
