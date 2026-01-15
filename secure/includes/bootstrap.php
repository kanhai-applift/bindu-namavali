<?php
require_once __DIR__ . "/../config/constant.php";
require_once __DIR__ .'/../vendor/autoload.php';
use Hashids\Hashids;

$hashids = new Hashids(
    HASHID_SALT, // 🔐 secret salt (never change)
    11,                                  // minimum hash length
    'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890'
);

function e($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function baseUrl($path){
    return BASE_URL.$path;
}