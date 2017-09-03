<?php

Flight::route("POST /user/token_check", function() use ($g, $pdo, $params) {
    // invalid token already checked in before function
    setResponseStatus(true, "Token still active!");
});


Flight::route("POST|PUT /user/create", function() use ($g, $pdo, $params) {

    // check username
    $sql = "SELECT id FROM referensi_user WHERE username = :username";
    $pdo->sth = $pdo->con->prepare($sql);
    $pdo->sth->bindParam(":username", $params->username, PDO::PARAM_STR);
    $pdo->execute();

    $isUsernameExist = $pdo->sth->fetch(PDO::FETCH_OBJ);
    if (!$isUsernameExist)
    {
        // check email
        $sql = "SELECT id FROM referensi_user WHERE email = :email";
        $pdo->sth = $pdo->con->prepare($sql);
        $pdo->sth->bindParam(":email", $params->email, PDO::PARAM_STR);
        $pdo->execute();

        $isEmailExist = $pdo->sth->fetch(PDO::FETCH_OBJ);
        if (!$isEmailExist)
        {
            $params->username = strtoupper(trim($params->username));
            $params->password = strtoupper(trim($params->password));
            $params->full_name = strtoupper(trim($params->full_name));
            $params->email = strtoupper(trim($params->email));
            $params->role = strtoupper(trim($params->role));
            $data = [
                "username" => [$params->username, "string"],
                "password" => [dec_enc("encrypt", $params->password), "string"],
                "full_name" => [$params->full_name, "string"],
                "email" => [$params->email, "string"],
                "role" => [$params->role, "string"],
                "regional" => [$params->role, "string"],
                "created_by" => [$g->logged->username, "string"]
            ];
            $pdo->insert("referensi_user", $data);

            $g->response["result"] = [
                "id" => $pdo->con->lastInsertId(),
                "username" => $params->username,
                "full_name" => $params->full_name,
                "email" => $params->email,
                "role" => $params->role,
                "dpassword" => $params->password
            ];
            setResponseStatus(true, "User baru berhasil dibuat!");
        }
        else
            setResponseStatus(false, "Email sudah digunakan!");
    }
    else
        setResponseStatus(false, "Username sudah digunakan!");
});

Flight::route("POST|PUT /user/edit/@id", function($id) use ($g, $pdo, $params) {

    // check id
    $sql = "SELECT id FROM referensi_user WHERE id = :id";
    $pdo->sth = $pdo->con->prepare($sql);
    $pdo->sth->bindParam(":id", $id, PDO::PARAM_INT);
    $pdo->execute();

    $isUserExist = $pdo->sth->fetch(PDO::FETCH_OBJ);
    if ($isUserExist)
    {
        // check available username
        $sql = "SELECT id FROM referensi_user WHERE id != :id AND username = :username";
        $pdo->sth = $pdo->con->prepare($sql);
        $pdo->sth->bindParam("id", $id, PDO::PARAM_INT);
        $pdo->sth->bindParam("username", $params->username, PDO::PARAM_STR);
        $pdo->execute();

        $isUsernameExist = $pdo->sth->fetch(PDO::FETCH_OBJ);
        if (!$isUsernameExist)
        {
            // check available email
            $sql = "SELECT id FROM referensi_user WHERE id != :id AND email = :email";
            $pdo->sth = $pdo->con->prepare($sql);
            $pdo->sth->bindParam("id", $id, PDO::PARAM_INT);
            $pdo->sth->bindParam("email", $params->email, PDO::PARAM_STR);
            $pdo->execute();

            $isEmailExist = $pdo->sth->fetch(PDO::FETCH_OBJ);
            if (!$isEmailExist)
            {
                $role = strtoupper(trim($params->role));
                $params->username = strtoupper(trim($params->username));
                $params->password = strtoupper(trim($params->password));
                $params->full_name = strtoupper(trim($params->full_name));
                $params->email = strtoupper(trim($params->email));
                $password = dec_enc("encrypt", $params->password);

                $sql = "UPDATE referensi_user SET username = :username, password = :password, email = :email, 
                    full_name = :full_name, regional = :reg, modified_at = NOW(), modified_by = :modifiedBy, role = :role 
                    WHERE id = :id";

                $pdo->sth = $pdo->con->prepare($sql);
                $pdo->sth->bindParam(':username', $params->username, PDO::PARAM_STR);
                $pdo->sth->bindParam(':password', $password, PDO::PARAM_STR);
                $pdo->sth->bindParam(':email', $params->email, PDO::PARAM_STR);
                $pdo->sth->bindParam(':full_name', $params->full_name, PDO::PARAM_STR);
                $pdo->sth->bindParam(':modifiedBy', $g->logged->username, PDO::PARAM_STR);
                $pdo->sth->bindParam(':role', $role, PDO::PARAM_STR);
                $pdo->sth->bindParam(':reg', $role, PDO::PARAM_STR);
                $pdo->sth->bindParam(':id', $id, PDO::PARAM_INT);
                $pdo->execute();

                $affectedRow = $pdo->sth->rowCount();
                if ($affectedRow > 0)
                {
                    $g->response["result"] = [
                        "id" => $id,
                        "username" => $params->username,
                        "full_name" => $params->full_name,
                        "email" => $params->email,
                        "role" => $params->role,
                        "regional" => $params->regional,
                        "dpassword" => $params->password
                    ];
                    setResponseStatus(true, "Update user berhasil!");
                }
                else
                    setResponseStatus(false, "No data changed!");
            }
            else
                setResponseStatus(false, "Email sudah digunakan!");
        }
        else
            setResponseStatus(false, "Username sudah digunakan!");
    }
    else
        setResponseStatus(false, "User tidak ditemukan!");
});

Flight::route("POST|PUT /user/delete/@id", function($id) use ($g, $pdo, $params) {
    // check id
    $sql = "SELECT id FROM referensi_user WHERE id = :id";
    $pdo->sth = $pdo->con->prepare($sql);
    $pdo->sth->bindParam(":id", $id, PDO::PARAM_INT);
    $pdo->execute();

    $isUserExist = $pdo->sth->fetch(PDO::FETCH_OBJ);
    if ($isUserExist)
    {
        $sql = "DELETE FROM referensi_user WHERE id = :id";
        $pdo->sth = $pdo->con->prepare($sql);
        $pdo->sth->bindParam(":id", $id, PDO::PARAM_INT);
        $pdo->execute();

        if ($pdo->sth->rowCount() > 0)
           setResponseStatus(true, "User berhasil dihapus!"); 
        else
           setResponseStatus(false, "Delete gagal!"); 
    }
    else
        setResponseStatus(false, "User tidak ditemukan!");
});

Flight::route("POST|PUT /user/search/@page/@limit/@key", function($page, $limit, $key) use ($g, $pdo, $params) {

    $g->response["result"]["key"] = $key;
    $g->response["result"]["total"] = 0;
    $g->response["result"]["page"] = (int) $page;
    $g->response["result"]["limit"] = (int) $limit;

    // count total data
    if (isset($params->search_by))
        $sql = "SELECT COUNT(id) as total_data FROM referensi_user 
            WHERE " . $params->search_by . " LIKE CONCAT('%', :key, '%')";
    else
        $sql = "SELECT COUNT(id) as total_data FROM referensi_user 
            WHERE username LIKE CONCAT('%', :key, '%') OR email LIKE CONCAT('%', :key, '%') OR role LIKE CONCAT('%', :key, '%')";
    
    $pdo->sth = $pdo->con->prepare($sql);
    $pdo->sth->bindParam(":key", $key, PDO::PARAM_STR);
    $pdo->execute();

    $result = $pdo->sth->fetch(PDO::FETCH_OBJ);

    $g->response["result"]["total_all"] = (int) $result->total_data;
    $divPage = $g->response["result"]["total_all"] / $g->response["result"]["limit"];

    $totalPage = 1;
    if ($divPage < 1)
        $totalPage = 1;
    else
    {
        $totalPage = floor($divPage);
        $modPage = $g->response["result"]["total_all"] % $g->response["result"]["limit"];
        if ($modPage > 0)
            $totalPage++;
    }
    $g->response["result"]["total_page"] = $totalPage;

    // get data
    $page = ($page == 1) ? 0 : ((int) $page - 1) * (int) $limit;

    if (isset($params->search_by))
        $sql = "SELECT id, username, password, email, full_name, role, created_at FROM referensi_user 
            WHERE " . $params->search_by . " LIKE CONCAT('%', :key, '%') 
            ORDER BY " . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;
    else
        $sql = "SELECT id, username, password, email, full_name, role, created_at FROM referensi_user 
            WHERE username LIKE CONCAT('%', :key, '%') OR email LIKE CONCAT('%', :key, '%') 
            OR role LIKE CONCAT('%', :key, '%') 
            ORDER BY " . $params->order . " " . $params->sort . " LIMIT :page, " . $limit;
    $pdo->sth = $pdo->con->prepare($sql);
    $pdo->sth->bindParam(":key", $key, PDO::PARAM_STR);
    $pdo->sth->bindParam(":page", $page, PDO::PARAM_INT);
    $pdo->execute();

    $g->response["result"]["data"] = [];

    while ($row = $pdo->sth->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT))
    {
        $row["dpassword"] = dec_enc("decrypt", $row["password"]);
        $g->response["result"]["total"]++;
        $g->response["result"]["data"][] = $row;
    }

    setResponseStatus(true, "". $g->response["result"]["total"] ." User ditemukan!");
});