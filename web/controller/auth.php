<?php
Flight::route("/auth/signin", function() use ($request) {

    $error = ["status" => 0, "message" => ""];
    $signin = (int) $request->data->btnSignin;
    if ($signin != 0)
    {
        $data = [
            "username" => (string) $request->data->username,
            "password" => (string) $request->data->password
        ];

        $response = getApiResponse(WEB_API . "auth/login", $data);
        $error["message"] = $response->message;
        if ($response->success)
        {
            $_SESSION["token"] = $response->result->token;
            $_SESSION["username"] = $response->result->data->username;
            $_SESSION["name"] = $response->result->data->full_name;
            $_SESSION["role"] = $response->result->data->role;
            Flight::redirect("dashboard/home");
        }
        else
            $error["status"] = 1;
    }
    Flight::render("auth/signin.php", ["error" => $error]);
});

Flight::route("/auth/signout", function() use ($request) {
    session_destroy();
    Flight::redirect("auth/signin");
});