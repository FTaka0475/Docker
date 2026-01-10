<?php
require_once 'common.php'; 
$current_user_id = $_SESSION['user_id']; 

try {
    $pdo_sub = getSubDb();
    
    // 【SQLの修正ポイント】
    // 1. i.id を GROUP BY に含めることでエラーを回避します。
    // 2. COUNT(*) で枚数を数えます。
    // 3. i.id で並び替えることで、マスターデータの登録順に綺麗に並びます。
    $sql = "
        SELECT 
            i.id AS card_id,
            i.name AS card_name,
            COUNT(*) AS quantity
        FROM 
            sub_db.users_cards ui
        JOIN 
            master_db.cards i ON ui.card_id = i.id
        WHERE 
            ui.user_id = :user_id
        GROUP BY 
            i.id, i.name
        ORDER BY 
            i.id ASC
    ";
    
    $stmt = $pdo_sub->prepare($sql);
    $stmt->execute([':user_id' => $current_user_id]);
    $my_cards_summary = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 合計枚数（カードの総数）を取得
    $total_count = 0;
    foreach ($my_cards_summary as $card) {
        $total_count += $card['quantity'];
    }

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
        body { font-family: 'Helvetica Neue', Arial, sans-serif; text-align: center; background-color: #f0f2f5; margin: 0; padding: 20px; color: #333; }
        h1 { color: #2c3e50; margin-bottom: 10px; }
        
        .btn-gacha {
            display: inline-block;
            margin-bottom: 30px;
            padding: 15px 40px;
            background: linear-gradient(135deg, #ff9800, #f44336);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-weight: bold;
            box-shadow: 0 4px 15px rgba(255, 152, 0, 0.4);
        }

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
            padding: 15px 10px;
        }
        .card-item:last-child { border-bottom: none; }
        .card-name { font-weight: bold; color: #34495e; font-size: 1.1em; }
        
        /* 枚数表示のバッジ */
        .card-quantity { 
            background-color: #3498db; 
            color: white; 
            padding: 4px 15px; 
            border-radius: 20px; 
            font-size: 0.9em;
            font-weight: bold;
        }

        .mix-section { margin-top: 30px; }
        .btn-mix {
            display: inline-block;
            padding: 12px 30px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <h1>🗃️ マイページ</h1>
    <p>プレイヤー: <strong>田中さん</strong></p>

    <a href="Gacha.php" class="btn-gacha">🎰 ガチャを引く</a>

    <div class="card-container">
        <h3>📦 あなたの所持カード (合計 <?= $total_count ?> 枚)</h3>
        
        <?php if (empty($my_cards_summary)): ?>
            <p style="padding: 20px; color: #999;">まだカードを持っていません。</p>
        <?php else: ?>
            <?php foreach ($my_cards_summary as $card): ?>
                <div class="card-item">
                    <span class="card-name">✨ <?= htmlspecialchars($card['card_name']) ?></span>
                    <span class="card-quantity">x <?= $card['quantity'] ?></span>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="mix-section">
        <?php if ($total_count >= 2): ?>
            <a href="Select_card.php" class="btn-mix">🛠️ カードを強化する</a>
        <?php endif; ?>
    </div>

</body>
</html>