<?php

    class Database {
        private $host = "localhost";
        private $db_name = "my_shop";
        private $username = "root";
        private $password = "Emmanuel2002.Z";
        private $connexion;

        public function recupConnexion(){

            $this->connexion = null;
            try {
            $this->connexion = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
                );
            } catch (PDOException $exception) {
                echo "Erreur de connexion: " . $exception->getMessage();
            }

            return $this->connexion; 
        }
    }