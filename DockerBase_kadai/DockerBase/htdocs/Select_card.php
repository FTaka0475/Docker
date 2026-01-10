<?php
require_once 'common.php';
$current_user_id = $_SESSION['user_id'];

try {
    $pdo_sub = getSubDb();
    // 【重要】種類(master_id)ごとに枚数を数えて取得します
    $sql = "
        SELECT 
            i.id AS master_id, i.name, i.next_id, COUNT(*) AS qty
        FROM sub_db.users_cards ui
        JOIN master_db.cards i ON ui.card_id = i.id
        WHERE ui.user_id = :user_id
        GROUP BY i.id, i.name, i.next_id
        ORDER BY i.id ASC
    ";
    $stmt = $pdo_sub->prepare($sql);
    $stmt->execute([':user_id' => $current_user_id]);
    $card_options = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("エラー: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>カード強化 | 注文画面</title>
    <style>
        body { font-family: sans-serif; text-align: center; background: #f0f2f5; padding: 20px; }
        .order-sheet { background: white; padding: 30px; border-radius: 15px; display: inline-block; width: 400px; text-align: left; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        select { width: 100%; padding: 12px; margin: 10px 0 25px; border-radius: 8px; border: 1px solid #ddd; font-size: 1em; }
        button { width: 100%; padding: 15px; background: #4CAF50; color: white; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; font-size: 1.1em; }
        button:disabled { background: #ccc; }
    </style>
</head>
<body>
    <h1>🛠️ 合成の注文（Select）</h1>

    <div class="order-sheet">
        <form action="Mix_card.php" method="POST">
            
            <h3>1. ベースカードを選択</h3>
            <select name="base_master_id" id="base_select" required onchange="updateKitchenOrder()">
                <option value="">-- 種類を選ぶ --</option>
                <?php foreach ($card_options as $c): ?>
                    <?php if ($c['next_id']): // 進化先があるものだけ ?>
                        <option value="<?= $c['master_id'] ?>">
                            <?= htmlspecialchars($c['name']) ?> (所持:<?= $c['qty'] ?>枚)
                        </option>
                    <?php endif; ?>
                <?php endforeach; ?>
            </select>

            <h3>2. 素材カードを選択</h3>
            <select name="material_master_id" id="material_select" required disabled>
                <option value="">-- 先にベースを選んでください --</option>
            </select>

            <button type="submit" id="order_btn" disabled>この内容で注文する！</button>
        </form>
    </div>

    <script>
        // PHPから全在庫データをJSに渡しておく
        const inventory = <?= json_encode($card_options) ?>;

        function updateKitchenOrder() {
            const baseSelect = document.getElementById('base_select');
            const matSelect = document.getElementById('material_select');
            const btn = document.getElementById('order_btn');
            const selectedId = baseSelect.value;

            // 素材リストを一旦リセット
            matSelect.innerHTML = '<option value="">-- 素材を選ぶ --</option>';

            if (!selectedId) {
                matSelect.disabled = true;
                btn.disabled = true;
                return;
            }

            // 在庫をループして、素材として選べるものを表示
            inventory.forEach(card => {
                let availableQty = parseInt(card.qty);
                
                // 【ここがポイント】ベースと同じ種類なら、1枚差し引く
                if (card.master_id == selectedId) {
                    availableQty -= 1;
                }

                // 1枚でも余っていれば、素材としてリストに載せる
                if (availableQty > 0) {
                    const opt = document.createElement('option');
                    opt.value = card.master_id;
                    opt.textContent = `${card.name} (残り${availableQty}枚)`;
                    matSelect.appendChild(opt);
                }
            });

            matSelect.disabled = false;
            btn.disabled = false;
        }
    </script>
</body>
</html>