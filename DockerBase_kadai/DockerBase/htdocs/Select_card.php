<?php
// 合成選択画面
require_once 'common.php';
$current_user_id = $_SESSION['user_id'];

try {
    $pdo_sub = getSubDb();

    $sql = "SELECT i.id AS master_id, i.name, i.next_id, i.rare, img.image_path, COUNT(*) AS qty
            FROM sub_db.users_cards ui
            JOIN master_db.cards i ON ui.card_id = i.id
            LEFT JOIN master_db.images img ON i.id = img.card_id
            WHERE ui.user_id = :user_id
            GROUP BY i.id, i.name, i.next_id, i.rare, img.image_path
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
        body { font-family: 'Helvetica Neue', Arial, sans-serif; text-align: center; background: #f0f2f5; padding: 20px; color: #444; }
        .box { background: white; padding: 25px; border-radius: 20px; display: inline-block; width: 480px; text-align: left; box-shadow: 0 10px 25px rgba(0,0,0,0.08); }
        .material-row { display: flex; justify-content: space-between; align-items: center; padding: 12px; border-bottom: 1px solid #f0f0f0; }
        .card-info { display: flex; align-items: center; gap: 15px; }
        .thumb { width: 50px; height: 50px; object-fit: contain; background: #fffaf0; border: 2px solid #ffebcd; border-radius: 10px; }
        .rate-display { font-size: 2em; color: #e67e22; font-weight: bold; text-align: center; margin: 25px 0; background: #fff5eb; padding: 10px; border-radius: 10px; }
        select { width: 100%; padding: 12px; border-radius: 10px; border: 2px solid #ddd; font-size: 1em; margin-bottom: 20px; }
        input[type="number"] { width: 60px; padding: 8px; border-radius: 8px; border: 1px solid #ccc; text-align: center; font-weight: bold; }
        button { width: 100%; padding: 18px; background: #ff9f43; color: white; border: none; border-radius: 12px; font-weight: bold; cursor: pointer; font-size: 1.2em; transition: 0.3s; }
        button:hover:not(:disabled) { background: #ee5253; transform: translateY(-2px); }
        button:disabled { background: #ccc; cursor: not-allowed; }
    </style>
</head>
<body>
    <h1>🐾 カード強化センター</h1>
    <div class="box">
        <form action="Mix_card.php" method="POST">
            <h3>1. ベースを選択</h3>
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

            <h3>2. 素材を選択</h3>
            <div id="material_list">
                <p style="color:gray; text-align:center;">先にベースを選んでね</p>
            </div>

            <div class="rate-display">成功確率: <span id="total_rate">0</span> %</div>
            <button type="submit" id="submit_btn" disabled>強化を実行する！</button>
        </form>
    </div>

    <script>
        const myCards = <?= json_encode($card_options) ?>;
        const rateTable = { 1:{1:100}, 2:{2:100, 1:50}, 3:{3:100, 2:25, 1:5}, 4:{4:100, 3:10, 2:2, 1:1} };

        function initMaterialList() {
            const baseSelect = document.getElementById('base_select');
            const listDiv = document.getElementById('material_list');
            const baseId = baseSelect.value;
            const baseRare = parseInt(baseSelect.selectedOptions[0]?.dataset.rare || 0);

            listDiv.innerHTML = '';
            if (!baseId) { updateTotal(); return; }

            myCards.forEach(card => {
                let maxQty = parseInt(card.qty);
                if (card.master_id == baseId) maxQty--;

                if (maxQty > 0) {
                    const ratePer = rateTable[baseRare]?.[card.rare] ?? (card.rare >= baseRare ? 100 : 0);
                    const row = document.createElement('div');
                    row.className = 'material-row';
                    row.innerHTML = `
                        <div class="card-info">
                            <img src="img/${card.image_path || 'no_image.png'}" class="thumb">
                            <div>
                                <strong>${card.name}</strong><br>
                                <small>★${card.rare} / +${ratePer}%</small>
                            </div>
                        </div>
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
            inputs.forEach(input => { total += parseInt(input.value) * parseInt(input.dataset.rate); });

            if (total >= 100) {
                total = 100;
                inputs.forEach(input => { if (parseInt(input.value) === '0') input.disabled = true; });
            } else {
                inputs.forEach(input => input.disabled = false);
            }
            document.getElementById('total_rate').innerText = total;
            document.getElementById('submit_btn').disabled = (total <= 0);
        }
    </script>
</body>
</html>