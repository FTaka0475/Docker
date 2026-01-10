<?php
require_once 'common.php';
$current_user_id = $_SESSION['user_id'];

try {
    $pdo_sub = getSubDb();
    // rarity を rare に変更
    $sql = "SELECT i.id AS master_id, i.name, i.next_id, i.rare, COUNT(*) AS qty
            FROM sub_db.users_cards ui
            JOIN master_db.cards i ON ui.card_id = i.id
            WHERE ui.user_id = :user_id
            GROUP BY i.id, i.name, i.next_id, i.rare
            ORDER BY i.rare ASC, i.id ASC";
    $stmt = $pdo_sub->prepare($sql);
    $stmt->execute([':user_id' => $current_user_id]);
    $card_options = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) { die("エラー: " . $e->getMessage()); }
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>強化センター</title>
    <style>
        body { font-family: sans-serif; text-align: center; background: #f0f2f5; padding: 20px; }
        .box { background: white; padding: 25px; border-radius: 15px; display: inline-block; width: 450px; text-align: left; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .material-row { display: flex; justify-content: space-between; align-items: center; padding: 10px; border-bottom: 1px solid #eee; }
        .rate-display { font-size: 1.8em; color: #e67e22; font-weight: bold; text-align: center; margin: 20px 0; }
        input[type="number"] { width: 60px; padding: 5px; border-radius: 5px; border: 1px solid #ddd; }
        button { width: 100%; padding: 15px; background: #4CAF50; color: white; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; font-size: 1.1em; }
        button:disabled { background: #ccc; cursor: not-allowed; }
    </style>
</head>
<body>
    <h1>🛠️ カード強化</h1>
    <div class="box">
        <form action="Mix_card.php" method="POST">
            <h3>1. ベースカードを選択</h3>
            <select name="base_master_id" id="base_select" required onchange="initMaterialList()">
                <option value="">-- ベースを選ぶ --</option>
                <?php foreach ($card_options as $c): ?>
                    <?php if ($c['next_id']): ?>
                        <option value="<?= $c['master_id'] ?>" data-rare="<?= $c['rare'] ?>">
                            [★<?= $c['rare'] ?>] <?= htmlspecialchars($c['name']) ?> (所持:<?= $c['qty'] ?>)
                        </option>
                    <?php endif; ?>
                <?php endforeach; ?>
            </select>

            <h3>2. 素材カードを選択</h3>
            <div id="material_list">
                <p style="color:gray;">先にベースを選んでください</p>
            </div>

            <div class="rate-display">成功確率: <span id="total_rate">0</span> %</div>
            <button type="submit" id="submit_btn" disabled>強化を実行する</button>
        </form>
    </div>

    <script>
        const myCards = <?= json_encode($card_options) ?>;
        const rateTable = {
            1: { 1: 100 },
            2: { 2: 100, 1: 50 },
            3: { 3: 100, 2: 25, 1: 5 },
            4: { 4: 100, 3: 10, 2: 2, 1: 1 }
        };

        function initMaterialList() {
            const baseSelect = document.getElementById('base_select');
            const listDiv = document.getElementById('material_list');
            const baseId = baseSelect.value;
            // dataset.rarity を dataset.rare に変更
            const baseRare = parseInt(baseSelect.selectedOptions[0]?.dataset.rare || 0);

            listDiv.innerHTML = '';
            if (!baseId) { updateTotal(); return; }

            myCards.forEach(card => {
                let maxQty = parseInt(card.qty);
                if (card.master_id == baseId) maxQty--;

                if (maxQty > 0) {
                    // card.rarity を card.rare に変更
                    const ratePer = rateTable[baseRare]?.[card.rare] ?? (card.rare >= baseRare ? 100 : 0);
                    const row = document.createElement('div');
                    row.className = 'material-row';
                    row.innerHTML = `
                        <span>${card.name} (★${card.rare}) <small>+${ratePer}%/枚</small></span>
                        <input type="number" name="materials[${card.master_id}]" value="0" min="0" max="${maxQty}" data-rate="${ratePer}" onchange="updateTotal()">
                    `;
                    listDiv.appendChild(row);
                }
            });
            updateTotal();
        }

        function updateTotal() {
            let total = 0;
            const inputs = document.querySelectorAll('#material_list input');
            inputs.forEach(input => {
                total += parseInt(input.value) * parseInt(input.dataset.rate);
            });

            if (total >= 100) {
                total = 100;
                inputs.forEach(input => { if (parseInt(input.value) === 0) input.disabled = true; });
            } else {
                inputs.forEach(input => input.disabled = false);
            }
            document.getElementById('total_rate').innerText = total;
            document.getElementById('submit_btn').disabled = (total <= 0);
        }
    </script>
</body>
</html>