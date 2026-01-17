<?php
require_once 'common.php';
$current_user_id = $_SESSION['user_id'];

try {
    $pdo_sub = getSubDb();
    $pdo_sub->beginTransaction();

    $stmt_del = $pdo_sub->prepare("DELETE FROM users_cards WHERE user_id = :uid");
    $stmt_del->execute([':uid' => $current_user_id]);

    $sql_init = "
        INSERT INTO users_cards (user_id, card_id)
        SELECT :uid, card_id FROM initial_cards
    ";
    $stmt_init = $pdo_sub->prepare($sql_init);
    $stmt_init->execute([':uid' => $current_user_id]);

    $pdo_sub->commit();
    
    echo "初期化に成功しました！ <a href='My_card.php'>戻る</a>";
    exit;

} catch (Exception $e) {
    if (isset($pdo_sub)) $pdo_sub->rollBack();
    die("初期化に失敗しました: " . $e->getMessage());
}