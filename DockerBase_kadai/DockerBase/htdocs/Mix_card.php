<?php
// 合成結果画面 
require_once 'common.php';
require_once 'Mix_process.php';
$uid = $_SESSION['user_id'];
$base_mid = $_POST['base_master_id'] ?? null;
$materials = $_POST['materials'] ?? [];
$result = executeMixing($uid, $base_mid, $materials);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>合成結果</title>
    <style>
        body { text-align: center; padding-top: 50px; font-family: sans-serif; background: #fffcf0; }
        .res-box { display: inline-block; padding: 40px; border-radius: 30px; background: white; box-shadow: 0 15px 35px rgba(0,0,0,0.1); border: 8px solid transparent; max-width: 400px; }
        .success { border-color: #ff9f43; animation: glow 1.5s infinite alternate; }
        .fail { border-color: #cfd8dc; }
        .result-img { width: 250px; height: 250px; object-fit: contain; margin: 20px 0; }
        .btn { display: inline-block; margin-top: 25px; padding: 15px 30px; background: #3498db; color: white; text-decoration: none; border-radius: 10px; font-weight: bold; }
        @keyframes glow { from { box-shadow: 0 0 20px #ff9f43; } to { box-shadow: 0 0 40px #ff9f43; } }
    </style>
</head>
<body>
    <div class="res-box <?= $result['status'] ?>">
        <?php if ($result['status'] === 'success'): ?>
            <h1 style="color: #ff9f43;">✨ 進化おめでとう！ ✨</h1>
            <img src="img/<?= htmlspecialchars($result['new_image']) ?>" class="result-img">
            <p style="font-size: 1.5em; font-weight: bold; color: #333;">
                <?= htmlspecialchars($result['new_name']) ?> になりました！
            </p>
        <?php elseif ($result['status'] === 'fail'): ?>
            <h1 style="color: #90a4ae;">💀 失敗しちゃった...</h1>
            <p>素材のねこちゃんたちは旅立ちましたが、<br>ベースのねこちゃんは無事です。</p>
        <?php else: ?>
            <h1>エラー</h1><p><?= htmlspecialchars($result['message']) ?></p>
        <?php endif; ?>
        <br>
        <a href="My_card.php" class="btn">マイページへ戻る</a>
    </div>
</body>
</html>