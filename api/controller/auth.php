<?php
Flight::route("POST /auth/login", function() use ($g, $pdo, $params) {

    $params->username = trim(strtoupper($params->username));
    $password = dec_enc("encrypt", trim(strtoupper($params->password)));
    $sql = "SELECT id, username, full_name, email, role, status FROM referensi_user WHERE username = :username AND password = :password";

    $pdo->sth = $pdo->con->prepare($sql);
    $pdo->sth->bindParam(":username", $params->username, PDO::PARAM_STR);
    $pdo->sth->bindParam(":password", $password, PDO::PARAM_STR);
    $pdo->execute();

    $user = $pdo->sth->fetch(PDO::FETCH_OBJ);
    if ($user) 
    {
        // create token
        $token = md5(uniqid());
        $sql = "UPDATE referensi_user SET token = :token, last_login = NOW(), modified_at = NOW() WHERE id = :id";
        $pdo->sth = $pdo->con->prepare($sql);
        $pdo->sth->bindParam(":token", $token , PDO::PARAM_STR);
        $pdo->sth->bindParam(":id", $user->id, PDO::PARAM_STR);
        $pdo->execute();

        $g->response["result"] = [
            "token" => $token,
            "data" => $user
        ];
        setResponseStatus(true, "User found!");
    }
    else
        setResponseStatus(false, "User not found!");
});