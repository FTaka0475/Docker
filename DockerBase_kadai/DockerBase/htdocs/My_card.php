<?php
require_once 'common.php';

// 今誰がログインしているか確認
$uid = $_SESSION['user_id'] ?? null;
$uName = $_SESSION['user_name'] ?? '';

$my_cards = [];
if ($uid) {
    try {
        $pdo_sub = getSubDb();

        $sql = "SELECT c.name, c.rare, img.image_path, COUNT(*) as qty 
                FROM users_cards uc
                JOIN master_db.cards c ON uc.card_id = c.id
                JOIN master_db.images img ON c.id = img.card_id
                WHERE uc.user_id = :uid
                GROUP BY c.id, c.name, c.rare, img.image_path";
                
        $stmt = $pdo_sub->prepare($sql);
        $stmt->execute([':uid' => $uid]);
        $my_cards = $stmt->fetchAll();
    } catch (Exception $e) { 
        echo "<p style='color:red;'>SQLエラー: " . $e->getMessage() . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>マイねこ帳</title>
    <style>
        body { font-family: sans-serif; background: #fffcf0; text-align: center; color: #5d4037; margin: 0; }
        .header { background: #fff; padding: 20px; border-bottom: 5px solid #ffcb81; }
        .card-grid { display: flex; flex-wrap: wrap; justify-content: center; gap: 20px; padding: 20px; }
        .card { background: white; border: 2px solid #ffcb81; border-radius: 15px; padding: 10px; width: 140px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .card img { width: 100px; height: 100px; object-fit: contain; }
        .btn { display: inline-block; padding: 12px 24px; background: #ff9f43; color: white; text-decoration: none; border-radius: 10px; margin-top: 20px; font-weight: bold; }
    </style>
</head>
<body>

<?php if (!$uid): ?>
    <div style="margin-top: 100px; padding: 20px;">
        <h1>🐾 飼い主さんが見つかりません</h1>
        <p>ねこちゃんを見るには、登録が必要です。</p>
        <a href="Create_user.php" class="btn">新しい飼い主を登録する</a>
        <br>
        <a href="select_user.php" style="display:inline-block; margin-top:15px; color:#888;">登録済みの人で遊ぶ</a>
    </div>

<?php else: ?>
    <div class="header">
        <h1>🐾 <?= htmlspecialchars($uName) ?> さんのねこ帳</h1>
        <nav>
            <a href="Gacha.php">💎ガチャ</a> | 
            <a href="Select_card.php">🛠️強化</a> | 
            <a href="Create_user.php">👤交代</a>
        </nav>
    </div>

    <div class="card-grid">
        <?php foreach ($my_cards as $card): ?>
            <div class="card">
                <img src="img/<?= htmlspecialchars($card['image_path'] ?? 'no_image.png') ?>">
                <div><strong><?= htmlspecialchars($card['name']) ?></strong></div>
                <div style="color: #f1c40f;">★<?= $card['rare'] ?></div>
                <div style="font-size: 0.8em; background:#f0f0f0; border-radius: 5px; margin-top: 5px;">所持: <?= $card['qty'] ?>枚</div>
            </div>
        <?php endforeach; ?>

        <?php if (empty($my_cards)): ?>
            <p>まだねこちゃんを持っていません。<br>ガチャを引きに行こう！</p>
        <?php endif; ?>
    </div>
<?php endif; ?>

</body>
</html>