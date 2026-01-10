<?php
require_once 'common.php'; // データベース接続やセッション(田中さん)の読み込み
$current_user_id = $_SESSION['user_id']; 

try {
    $pdo_sub = getSubDb();
    
    // 【SQL】自分の持っているカード(sub_db)と名前(master_db)を合体させて取得
    $sql = "
        SELECT 
            ui.id AS instance_id, 
            i.name AS card_name
        FROM 
            sub_db.users_cards ui
        JOIN 
            master_db.cards i ON ui.card_id = i.id
        WHERE 
            ui.user_id = :user_id
        ORDER BY ui.id DESC
    ";
    
    $stmt = $pdo_sub->prepare($sql);
    $stmt->execute([':user_id' => $current_user_id]);
    $my_cards = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("エラーが発生しました: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>マイカード | カードゲーム開発</title>
    <style>
        /* 全体のデザイン */
        body { font-family: 'Helvetica Neue', Arial, sans-serif; text-align: center; background-color: #f0f2f5; margin: 0; padding: 20px; color: #333; }
        h1 { color: #2c3e50; margin-bottom: 10px; }
        .user-info { color: #7f8c8d; margin-bottom: 30px; }

        /* ガチャボタン（新機能） */
        .btn-gacha {
            display: inline-block;
            margin-bottom: 30px;
            padding: 15px 40px;
            background: linear-gradient(135deg, #ff9800, #f44336);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-weight: bold;
            font-size: 1.2em;
            box-shadow: 0 4px 15px rgba(255, 152, 0, 0.4);
            transition: transform 0.2s;
        }
        .btn-gacha:hover { transform: scale(1.05); }

        /* カードリストの箱 */
        .card-container {
            max-width: 500px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
        }
        .card-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #f1f1f1;
            padding: 12px 10px;
        }
        .card-item:last-child { border-bottom: none; }
        .card-id { color: #95a5a6; font-size: 0.9em; }
        .card-name { font-weight: bold; color: #34495e; font-size: 1.1em; }

        /* 強化ボタン */
        .mix-section { margin-top: 30px; }
        .btn-mix {
            display: inline-block;
            padding: 12px 30px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            box-shadow: 0 4px #388E3C;
        }
        .btn-mix:active { transform: translateY(2px); box-shadow: 0 2px #388E3C; }
        .msg-short { color: #e74c3c; font-size: 0.9em; font-weight: bold; }

        /* 初期化ボタン */
        .btn-reset {
            display: inline-block;
            margin-top: 60px;
            color: #bdc3c7;
            text-decoration: none;
            font-size: 0.8em;
            padding: 5px 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .btn-reset:hover { background: #eee; color: #7f8c8d; }
    </style>
</head>
<body>

    <h1>🗃️ マイページ</h1>
    <p class="user-info">プレイヤー: <strong>田中さん</strong> (ID: <?= htmlspecialchars($current_user_id) ?>)</p>

    <a href="Gacha.php" class="btn-gacha">🎰 ガチャでカードを増やす</a>

    <div class="card-container">
        <h3>所持カード一覧 (<?= count($my_cards) ?>枚)</h3>
        
        <?php if (empty($my_cards)): ?>
            <p style="padding: 20px; color: #999;">カードがありません。ガチャを引きましょう！</p>
        <?php else: ?>
            <?php foreach ($my_cards as $card): ?>
                <div class="card-item">
                    <span class="card-id">#<?= $card['instance_id'] ?></span>
                    <span class="card-name">✨ <?= htmlspecialchars($card['card_name']) ?></span>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="mix-section">
        <?php if (count($my_cards) >= 2): ?>
            <p>素材が揃っています！</p>
            <a href="Select_card.php" class="btn-mix">🛠️ カードを強化（ミックス）する</a>
        <?php else: ?>
            <p class="msg-short">⚠️ 強化するにはカードが2枚以上必要です</p>
        <?php endif; ?>
    </div>

    <a href="Reset_data.php" class="btn-reset" onclick="return confirm('全ての所持カードが消去されます。本当によろしいですか？');">
        🔄 データを初期化（デバッグ用）
    </a>

</body>
</html>