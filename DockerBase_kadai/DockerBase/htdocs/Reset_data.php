<?php
require_once 'common.php';
$current_user_id = $_SESSION['user_id'];

try {
    $pdo_sub = getSubDb();
    $pdo_sub->beginTransaction();

    // 1. 現在の所持カードを全て削除
    $stmt_del = $pdo_sub->prepare("DELETE FROM users_cards WHERE user_id = :uid");
    $stmt_del->execute([':uid' => $current_user_id]);

    // 2. initial_cardsテーブルの内容を、自分のuser_idでコピーして挿入
    // このSQL一行で、テーブル間のコピーが完結します
    $sql_init = "
        INSERT INTO users_cards (user_id, card_id)
        SELECT :uid, card_id FROM initial_cards
    ";
    $stmt_init = $pdo_sub->prepare($sql_init);
    $stmt_init->execute([':uid' => $current_user_id]);

    $pdo_sub->commit();
    
    // 一覧画面へ戻る
    // header("Location: My_card.php");
    echo "初期化に成功しました！ <a href='My_card.php'>戻る</a>";
    exit;

} catch (Exception $e) {
    if (isset($pdo_sub)) $pdo_sub->rollBack();
    die("初期化に失敗しました: " . $e->getMessage());
}