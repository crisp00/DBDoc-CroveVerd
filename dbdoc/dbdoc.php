<?php
$_path = dirname(__FILE__);
$_docroot = $_SERVER['DOCUMENT_ROOT'];
require_once($_path . "/splogger/splogger.php");


class DbDoc extends Splogger{

    function __construct(DbDocConfig $config){
        parent::__construct($config);
    }

    function getCategories($parent){
        $query = "SELECT * FROM ".$this->config->db_prefix."_categoria WHERE parent_id = " . $parent->id . ";";
        $result = $this->db->query($query);
        $categories = [];
        if($this->db->error)
            echo $this->db->error;
        if(!$result){
            return $categories;
        }
        while(($row = $result->fetch_assoc()) != null){
            array_push($categories, new DbDocCategory($this, $row["id"], $row["nome"], $parent, $row["percorso"]));
        }
        return $categories;
    }

    function getRootCategory(){
        return new DbDocCategory($this, 1);
    }
}


class DbDocCategory{
    public $name;
    public $parent;
    public $path;
    public $id;
    protected $dbdoc;

    function __construct(DbDoc $dbdoc, $id = null, $name = null, $parent = null, $path = null){
        $this->dbdoc = $dbdoc;
        if($name == null && $id != null){
            $query = "SELECT * FROM ".$dbdoc->config->db_prefix."_categoria WHERE id = " . $id;
            $result = $dbdoc->db->query($query);
            if(!$result){
                return false;
            }
            $obj = $result->fetch_assoc();
            $this->name = $obj["nome"];
            if($obj["parent_id"] != null){
                $this->parent = new DbDocCategory($dbdoc, $obj["parent_id"]);
            }
            $this->path = $obj["percorso"];
        }else{
            $this->name = $name;
            $this->parent = $parent;
            $this->path = $path;
        }
        $this->id = $id;
    }

    function save(){
        if(!($this->parent != null && $this->name != null && $this->path != null)){
            throw new ErrorException("DbDoc Error: trying to save uncompiled category");
        }
        if($this->id == null){
            mkdir($_SERVER['DOCUMENT_ROOT'] . $this->getAbsolutePath(), 0777, true);
            $query = "INSERT INTO ". $this->dbdoc->config->db_prefix ."_categoria (nome, percorso, parent_id)
            VALUES (\"". $this->name ."\", \"". $this->path ."\", ". $this->parent->id .")";
            $this->dbdoc->db->query($query);
            if($this->dbdoc->db->error)
                echo $this->dbdoc->db->error;
        }else{
            $query = "UPDATE ". $this->dbdoc->config->db_prefix ."_categoria 
            SET nome = ". $this->name . ",
            percorso = ". $this->path . ",
            parent_id = ". $this->parent->id.",
            WHERE id = ". $this->id;
            mkdir($_SERVER['DOCUMENT_ROOT'] . $this->getAbsolutePath(), 0777, true);
        }
    }

    function getAbsolutePath(){
        return (($this->parent != null) ? $this->parent->getAbsolutePath() : "") . $this->path;
    }

    function toString() {
        return "Category {id: ". $this->id 
            .", name: ".$this->name.
            ", parent_id: ".(($this->parent != null) ? ($this->parent->id) : (null)).
            ", path: ".$this->path."}";
    }
}

class DbDocConfig extends SploggerConfig{    
    function __construct(){
        
    }
}

class DbDocDatabaseInitializer extends SploggerDatabaseInitializer{
    function __construct(DbDocConfig $config){
        parent::__construct($config);
    }

    function init(){
        parent::init();
        $cat_table = "CREATE TABLE IF NOT EXISTS `".$this->config->db_prefix."_categoria` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `nome` varchar(50) NOT NULL,
            `percorso` varchar(20) NOT NULL,
            `parent_id` int(11) DEFAULT NULL,
            PRIMARY KEY (`id`),
            FOREIGN KEY `parent_id` (`parent_id`) REFERENCES `".$this->config->db_prefix."_categoria` (`id`));";
        
        $cat_fill = "INSERT INTO `".$this->config->db_prefix."_categoria` (`id`, `nome`, `percorso`, `parent_id`) VALUES
        (1, 'root', '/cv/docs', NULL);";
        
        $doc_table = "CREATE TABLE IF NOT EXISTS `".$this->config->db_prefix."_documento` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `nome` varchar(50) NOT NULL,
            `percorso` varchar(50) NOT NULL,
            `cat_id` int(11) NOT NULL,
            PRIMARY KEY (`id`),
            FOREIGN KEY `cat_id` (`cat_id`) REFERENCES `".$this->config->db_prefix."_categoria` (`id`));";
        
        $this->db->query($cat_table);
        if($this->db->error){
            throw new ErrorException("DbDoc: Failed to create categories table in database: " . $this->db->error);
        }
        $this->db->query($cat_fill);
        if($this->db->error){
            throw new ErrorException("DbDoc: Failed to fill categories table in database: " . $this->db->error);
        }
        $this->db->query($doc_table);
        if($this->db->error){
            throw new ErrorException("DbDoc: Failed to create documents table in database: " . $this->db->error);
        }
        $this->splogger->createGroup("user");
        $this->splogger->register("test", "test");
    }
}

?>