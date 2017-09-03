<?php

class MyPdo {

    public $con;
    public $sql;
    public $sth;
    public $err;
    public $tbl;

    public function __construct()
    {
        $this->con = new PDO("mysql:dbname=siakangrosi_db;host=localhost;port=3306", "root", "");
        $this->con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->err = 0;
        $this->tbl = "";
    }

    public function execute()
    {
        try
        {
            $this->sth->execute();
        }
        catch (PDOException $e)
        {
            $this->err = 1;
            setResponseStatus(false, "Table: '" . $this->tbl . "' " . $e->getMessage());
            setExitResponse();
            if ($this->con->inTransaction())
                $this->con->rollBack();
            Flight::stop();
        }
    }

    public function insert($table, $data)
    {
        $this->tbl = $table;

        $sql = "INSERT INTO ".$table;
        $fields = "";
        $values = "";
        $count = 0;
        foreach ($data as $key => $field)
        {
            if ($count == 0)
            {
                $fields .= $key;
                $values .= ":" . $key;
            }
            else
            {
                $fields .= "," . $key;
                $values .= ", :" . $key;
            }

            $count++;
        }

        $sql .= "(" . $fields . ") VALUES (" . $values . ")";
        
        $this->sth = $this->con->prepare($sql);
        foreach ($data as $key => $field)
        {
            if (strtoupper($field[1]) == "STRING")
                $this->sth->bindParam(':'.$key, $field[0], PDO::PARAM_STR);
            else
                $this->sth->bindParam(':'.$key, $field[0], PDO::PARAM_INT);
        }

        $this->execute();
    }

    public function select()
    {
        
    }

    public function setUpdate($param)
    {
        /*
            [
                "datetime" => [NOW(), "value"],
                "qty" => ["qty + :qty_new + :qty_new2 + 1", "syntax"]
            ]
        */
    }

    public function where()
    {
        // id = 1 AND username = 'adit'
        // id != 9 AND username = 'salman'
        // id = 9 OR username = 'umar' OR email = 'um@ar.com'
        /*
            [
                "id" => [$value, "=", "OR"]
            ]
        */
    }
}