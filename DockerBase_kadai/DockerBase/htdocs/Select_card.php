<?php
require_once 'common.php';
$current_user_id = $_SESSION['user_id'];

try {
    $pdo_sub = getSubDb();
    $sql = "
        SELECT ui.id AS instance_id, i.name, i.next_id
        FROM sub_db.users_cards ui
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
<head>
    <meta charset="UTF-8">
    <title>カード選択</title>
    <style>
        body { font-family: sans-serif; text-align: center; background-color: #f4f4f4; padding: 20px; }
        .form-box { background: white; padding: 20px; border-radius: 10px; display: inline-block; text-align: left; box-shadow: 0 2px 5px rgba(0,0,0,0.1); width: 400px; }
        select { width: 100%; padding: 10px; margin-bottom: 20px; font-size: 1em; }
        button { width: 100%; padding: 15px; background: #4CAF50; color: white; border: none; border-radius: 5px; font-weight: bold; cursor: pointer; }
        .hidden { display: none; } /* 隠すためのスタイル */
    </style>

    <script>
    function updateMaterialList() {
        // 1. ベースカードで選ばれたIDを取得
        const baseSelect = document.getElementById('base_select');
        const materialSelect = document.getElementById('material_select');
        const selectedBaseId = baseSelect.value;

        // 2. 素材カードの全選択肢（option）をループで確認
        for (let i = 0; i < materialSelect.options.length; i++) {
            let option = materialSelect.options[i];

            // 3. 一旦すべての選択肢を表示させる（リセット）
            option.disabled = false;
            option.style.display = 'block';

            // 4. ベースで選んだIDと同じIDの選択肢を「無効化して隠す」
            if (option.value !== "" && option.value === selectedBaseId) {
                option.disabled = true;
                option.style.display = 'none'; // リストから消えたように見せる
                
                // もし素材側でも同じものが選ばれていたら、選択を解除する
                if (materialSelect.value === selectedBaseId) {
                    materialSelect.value = "";
                }
            }
        }
    }
    </script>
</head>
<body>
    <h1>🛠️ カード強化（ミックス）</h1>

    <div class="form-box">
        <?php if (count($user_cards) < 2): ?>
            <p style="color:red;">カードが2枚以上必要です。</p>
            <a href="My_card.php">戻る</a>
        <?php else: ?>
            <form action="Mix_card.php" method="GET">
                
                <h3>1. ベースカードを選択</h3>
                <select name="base_id" id="base_select" required onchange="updateMaterialList()">
                    <option value="">-- ベースを選ぶ --</option>
                    <?php foreach ($user_cards as $c): ?>
                        <?php if ($c['next_id'] !== null): ?>
                            <option value="<?= $c['instance_id'] ?>">
                                ID:<?= $c['instance_id'] ?> <?= htmlspecialchars($c['name']) ?>
                            </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>

                <h3>2. 素材カードを選択</h3>
                <select name="material_id" id="material_select" required>
                    <option value="">-- 素材を選ぶ --</option>
                    <?php foreach ($user_cards as $c): ?>
                        <option value="<?= $c['instance_id'] ?>">
                            ID:<?= $c['instance_id'] ?> <?= htmlspecialchars($c['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button type="submit">強化を実行する！</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>