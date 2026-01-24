<?php
// ユーザー作成
require_once 'common.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_name'])) {
    $userName = $_POST['user_name'];
    try {
        $pdo = getSubDb();

        $sql = "INSERT INTO sub_db.users_name (name) VALUES (:user_name)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':user_name' => $userName]);

        $newId = $pdo->lastInsertId();

        $_SESSION['user_id'] = $newId;
        $_SESSION['user_name'] = $userName;


        header("Location: My_card.php");
        exit;

    } catch (PDOException $e) {
        $error = "エラーが発生しました: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>新規登録</title>
    <style>
        body { font-family: sans-serif; text-align: center; background: #fffcf0; padding-top: 50px; color: #5d4037; }
        .box { background: white; padding: 30px; border-radius: 20px; display: inline-block; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        input { padding: 10px; border-radius: 5px; border: 1px solid #ddd; width: 200px; }
        button { padding: 10px 20px; background: #ff9f43; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; }
    </style>
</head>
<body>
    <div class="box">
        <h1>✨ 新しい飼い主を登録</h1>
        <?php if (isset($error)): ?> <p style="color: red;"><?= $error ?></p> <?php endif; ?>
        <form method="POST">
            <input type="text" name="user_name" placeholder="おなまえを入力" required>
            <button type="submit">登録してあそぶ！</button>
        </form>
        <p><a href="My_card.php" style="color: #888;">もどる</a></p>
    </div>
</body>
</html>