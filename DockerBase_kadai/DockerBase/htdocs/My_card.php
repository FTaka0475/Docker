<?php
require_once 'common.php'; // データベース接続やセッションの読み込み
$current_user_id = $_SESSION['user_id']; // 田中さん(1)

try {
    $pdo_sub = getSubDb();
    
    // 【重要】自分の持っているカードと、その名前をマスターから持ってくるSQL
    $sql = "
        SELECT 
            ui.id AS instance_id, 
            i.name AS card_name
        FROM 
            users_cards ui
        JOIN 
            master_db.cards i ON ui.card_id = i.id
        WHERE 
            ui.user_id = :user_id
    ";
    
    $stmt = $pdo_sub->prepare($sql);
    $stmt->execute([':user_id' => $current_user_id]);
    $my_cards = $stmt->fetchAll();
} catch (Exception $e) {
    die("エラーが発生しました: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>マイカード一覧</title>
    <style>
        body { font-family: sans-serif; text-align: center; background-color: #f4f4f4; padding: 20px; }
        .card-list { background: white; border-radius: 10px; padding: 20px; display: inline-block; min-width: 300px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .card-item { border-bottom: 1px solid #eee; padding: 10px; list-style: none; text-align: left; }
        .btn-mix { display: inline-block; margin-top: 20px; padding: 10px 20px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 5px; font-weight: bold; }
        .btn-reset { display: inline-block; margin-top: 50px; color: #ff4444; font-size: 0.8em; text-decoration: none; }
    </style>
</head>
<body>

    <h1>🗃️ あなたの所持カード</h1>
    <p>ユーザー: 田中さん (ID: <?= htmlspecialchars($current_user_id) ?>)</p>

    <div class="card-list">
        <?php if (empty($my_cards)): ?>
            <p>カードを一枚も持っていません...</p>
        <?php else: ?>
            <ul style="padding: 0;">
                <?php foreach ($my_cards as $card): ?>
                    <li class="card-item">
                        🆔 ID: <?= $card['instance_id'] ?> | <strong><?= htmlspecialchars($card['card_name']) ?></strong>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

    <br>
    
    <?php if (count($my_cards) >= 2): ?>
        <a href="Select_card.php" class="btn-mix">✨ カードを強化（ミックス）する</a>
    <?php else: ?>
        <p style="color: gray;">カードを2枚以上集めると強化できます</p>
    <?php endif; ?>

    <br>

    <a href="Reset_data.php" class="btn-reset" onclick="return confirm('本当に初期状態に戻しますか？');">
        🔄 データを初期化（テスト用）
    </a>

</body>
</html>