<?php
// db_config.php

// データベース接続設定

// --- MASTER DB (カード種類/レシピ/ユーザー) ---
$master_host = 'mysql';
$master_database = 'master_db'; // カード種類(cards)やレシピ(craft_recipes)用
$master_username = 'root';
$master_password = 'p@ssword';

// --- SUB DB (所持カード) ---
// SUB DBもDocker環境で同じホスト（mysql）にあると仮定
$sub_host = 'mysql';
$sub_database = 'sub_db'; // 所持カード(user_card)用
$sub_username = 'root';
$sub_password = 'p@ssword';
?>