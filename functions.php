<?php

require_once __DIR__ . '/config.php';

// 接続処理を行う関数
function connect_db()
{
    try {
        return new PDO(
            DSN,
            USER,
            PASSWORD,
            [PDO::ATTR_ERRMODE =>
            PDO::ERRMODE_EXCEPTION]
        );
    } catch (PDOException $e) {
        echo 'システムエラーが発生しました';
        error_log($e->getMessage());
        exit;
    }
}

// エスケープ処理を行う関数
function h($str)
{
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

function find_branch($id) {
    $dbh = connect_db();

    $sql = <<<EOM
    SELECT
        *
    FROM
        branches
    WHERE
        id = :id;
    EOM;
    $stmt = $dbh->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_STR);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}