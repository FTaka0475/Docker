<?php
// common.php

// エラーを画面に表示する(開発用)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// セッションを開始
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ★★★ user_idの設定（ログインがないため固定）★★★
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1; // ユーザーIDを1に固定
}
// ===============================================

// データベース接続設定を読み込む
require_once 'db_config.php';

/**
 * master_db (カード種類, レシピ) へのPDO接続を取得
 * @return PDO
 */
function getMasterDb(): PDO
{
    global $master_host, $master_database, $master_username, $master_password; 
    
    $dsn = "mysql:host={$master_host};dbname={$master_database};charset=utf8mb4";
    
    $pdo = new PDO($dsn, $master_username, $master_password); 
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $pdo;
}

/**
 * sub_db (ユーザー所持カード) へのPDO接続を取得
 * @return PDO
 */
function getSubDb(): PDO
{
    global $sub_host, $sub_database, $sub_username, $sub_password; 
    
    $dsn = "mysql:host={$sub_host};dbname={$sub_database};charset=utf8mb4";
    
    $pdo = new PDO($dsn, $sub_username, $sub_password); 
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $pdo;
}
?>