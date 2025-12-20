<?php
require_once 'common.php';
$current_user_id = $_SESSION['user_id'];

try {
    $pdo_sub = getSubDb();
    // 自分の持っているカードと、その名前を取得
    $sql = "
        SELECT ui.id AS instance_id, i.name 
        FROM users_cards ui
        JOIN master_db.cards i ON ui.card_id = i.id
        WHERE ui.user_id = :user_id
    ";
    $stmt = $pdo_sub->prepare($sql);
    $stmt->execute([':user_id' => $current_user_id]);
    $user_cards = $stmt->fetchAll();
} catch (Exception $e) {
    die("エラー: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="ja">
<head><meta charset="UTF-8"><title>カード選択</title></head>
<body>
    <h1>カード強化（ミックス）</h1>
    <p>ユーザーID: <?php echo $current_user_id; ?> の所持カード</p>

    <?php if (count($user_cards) < 2): ?>
        <p style="color:red;">カードが2枚以上必要です。</p>
    <?php else: ?>
        <form action="Mix_card.php" method="GET">
            <h3>ベースカードを選択</h3>
            <select name="base_id" required>
                <?php foreach ($user_cards as $c): ?>
                    <option value="<?= $c['instance_id'] ?>">ID:<?= $c['instance_id'] ?> <?= $c['name'] ?></option>
                <?php endforeach; ?>
            </select>

            <h3>素材カードを選択</h3>
            <select name="material_id" required>
                <?php foreach ($user_cards as $c): ?>
                    <option value="<?= $c['instance_id'] ?>">ID:<?= $c['instance_id'] ?> <?= $c['name'] ?></option>
                <?php endforeach; ?>
            </select>
            <br><br>
            <button type="submit">強化実行！</button>
        </form>
    <?php endif; ?>
</body>
</html>