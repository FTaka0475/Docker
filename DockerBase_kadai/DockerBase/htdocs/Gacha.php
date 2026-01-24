<?php
require_once 'common.php';
$current_user_id = $_SESSION['user_id'] ?? null;

// もしログインしていなければ登録画面へ
if (!$current_user_id) {
    header("Location: Create_user.php");
    exit;
}

try {
    $pdo_master = getMasterDb();
    $pdo_sub = getSubDb();

    $stmt = $pdo_master->query("SELECT id, name, rate FROM cards");
    $all_cards = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $total_rate = 0;
    foreach ($all_cards as $card) {
        $total_rate += $card['rate'];
    }

    $random_number = mt_rand(1, $total_rate);

    $current_sum = 0;
    $picked_card = null;
    foreach ($all_cards as $card) {
        $current_sum += $card['rate'];
        if ($random_number <= $current_sum) {
            $picked_card = $card;
            break;
        }
    }

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
    <h1>✨ RESULT ✨</h1>
    <p>…… 魔法陣から現れたのは ……</p>
    <h2 style="font-size: 3em; color: gold;"><?= htmlspecialchars($picked_card['name']) ?></h2>

    <div style="margin-top: 30px;">
        <a href="Gacha.php" style="display: inline-block; padding: 15px 30px; background: linear-gradient(135deg, #ff9800, #f44336); color: white; text-decoration: none; border-radius: 10px; font-weight: bold; font-size: 1.1em; margin-right: 10px; box-shadow: 0 4px #e68a00;">
            🎰 もう一度引く
        </a>

        <br><br>

        <a href="My_card.php" style="display: inline-block; padding: 10px 20px; background: #555; color: white; text-decoration: none; border-radius: 5px; font-size: 0.9em;">
            一覧へ戻る
        </a>
    </div>
</div>
</body>
</html>