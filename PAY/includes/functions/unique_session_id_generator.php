<?php

// UNIQUE SESSION ID WITH SECRET KEY
function secureSessionID($secretKey, $length)
{
    $random = random_bytes($length);
    return hash_hmac('sha256', $random, $secretKey);
}
