<?php
require_once 'common.php';
$current_user_id = $_SESSION['user_id'];

try {
    $pdo_master = getMasterDb();
    $pdo_sub = getSubDb();

    // 1. 全カードのデータ（ID, 名前, rate）を取得
    $stmt = $pdo_master->query("SELECT id, name, rate FROM cards");
    $all_cards = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. 出現レートの合計を計算
    $total_rate = 0;
    foreach ($all_cards as $card) {
        $total_rate += $card['rate'];
    }

    // 3. 1 から 合計値(136) の間でランダムな数字を引く
    $random_number = mt_rand(1, $total_rate);

    // 4. 当たったカードを特定する
    $current_sum = 0;
    $picked_card = null;
    foreach ($all_cards as $card) {
        $current_sum += $card['rate'];
        if ($random_number <= $current_sum) {
            $picked_card = $card;
            break;
        }
    }

    // 5. 当たったカードをユーザーのDBに保存
    $sql_ins = "INSERT INTO sub_db.users_cards (user_id, card_id) VALUES (:uid, :cid)";
    $stmt_ins = $pdo_sub->prepare($sql_ins);
    $stmt_ins->execute([':uid' => $current_user_id, ':cid' => $picked_card['id']]);

} catch (Exception $e) {
    die("ガチャ失敗: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>ガチャ結果</title>
    <style>
        body { text-align: center; font-family: sans-serif; background: #222; color: white; padding-top: 100px; }
        .result-box { background: #333; border: 5px solid gold; padding: 40px; border-radius: 20px; display: inline-block; }
        .btn { display: inline-block; margin-top: 20px; padding: 10px 20px; background: gold; color: black; text-decoration: none; border-radius: 5px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="result-box">
        <h1>✨ SUMMON RESULT ✨</h1>
        <p>…… 魔法陣から現れたのは ……</p>
        <h2 style="font-size: 3em; color: gold;"><?= htmlspecialchars($picked_card['name']) ?></h2>
        <a href="My_card.php" class="btn">一覧へ戻る</a>
    </div>
</body>
</html>