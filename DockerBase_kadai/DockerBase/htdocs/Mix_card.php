<?php
require_once 'common.php';
$current_user_id = $_SESSION['user_id'];
$success = false;
$error = "";

$base_id = (int)($_GET['base_id'] ?? 0);
$material_id = (int)($_GET['material_id'] ?? 0);

if ($base_id === $material_id) {
    die("同じカードは選べません。");
}

try {
    $pdo_master = getMasterDb();
    $pdo_sub = getSubDb();

    $pdo_sub->beginTransaction();

    // 1. ベースカードが強化可能かチェック (next_idを取得)
    $sql = "SELECT ui.card_id, i.name, i.next_id 
            FROM users_cards ui 
            JOIN master_db.cards i ON ui.card_id = i.id 
            WHERE ui.id = :id AND ui.user_id = :uid";
    $stmt = $pdo_sub->prepare($sql);
    $stmt->execute([':id' => $base_id, ':uid' => $current_user_id]);
    $base_card = $stmt->fetch();

    if (!$base_card || !$base_card['next_id']) {
        throw new Exception("このカードは強化できません。");
    }

    // 2. カードを2枚消費(削除)
    $del = $pdo_sub->prepare("DELETE FROM users_cards WHERE id = :id AND user_id = :uid");
    $del->execute([':id' => $base_id, ':uid' => $current_user_id]);
    $del->execute([':id' => $material_id, ':uid' => $current_user_id]);

    // 3. 新しいカードを付与 (idはAUTO_INCREMENTなので指定しなくてOK)
    $ins = $pdo_sub->prepare("INSERT INTO users_cards (user_id, card_id) VALUES (:uid, :cid)");
    $ins->execute([':uid' => $current_user_id, ':cid' => $base_card['next_id']]);

    // 4. 新しいカードの名前を取得
    $stmt_name = $pdo_master->prepare("SELECT name FROM cards WHERE id = :id");
    $stmt_name->execute([':id' => $base_card['next_id']]);
    $new_name = $stmt_name->fetch()['name'];

    $pdo_sub->commit();
    $success = true;
} catch (Exception $e) {
    if (isset($pdo_sub)) $pdo_sub->rollBack();
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="ja">
<head><meta charset="UTF-8"><title>結果</title></head>
<body style="text-align:center;">
    <?php if ($success): ?>
        <h1 style="color:green;">強化成功！</h1>
        <p>新しいカード「<strong><?= htmlspecialchars($new_name) ?></strong>」を手に入れた！</p>
    <?php else: ?>
        <h1 style="color:red;">失敗</h1>
        <p><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <a href="My_card.php">戻る</a>
</body>
</html>