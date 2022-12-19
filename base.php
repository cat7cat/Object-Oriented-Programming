<?php 

$Student=new DB('students');

//var_dump($Student);

/* 測試刪除先MARK掉
$john=$Student->find(30)['name']['age']['birthday'];
$john=$Student->find(30)->name->age->birthday; //簡寫方式 ->name->age->birthday; 是物件還是值 不一定 要小心使用
*/



//$Student->del(10);
/* 
$Student->del(['dept'=>1]);

$stus=$Student->all(['dept'=>3]);
foreach($stus as $stu){
    echo $stu['parents'] . "=>".$stu['dept'];
    echo "<br>";
} 

*/

//新增資料 測試寫入
/*
$student->save([`name`=>'張大同',`dept`=>2,`uni_id`=>'H212312356']);
echo "<br>";
*/

//更新資料 測試寫入

/*
$student->save([`name`=>'張大同',`dept`=>2,`uni_id`=>'H212312356',`id`=>3]);
$stu=$Student->find(15) ;//找第15個ID的同學
dd($stu);
$stu['name']="陳秋桂";
$Student->save($stu);
*/


/*
// 數學函式
$Student->count([]);        //求數量
$Student->max($col,[]);     //最大值
$Student->mini($col,[]);    //最小值
$Student->avg($col,[]);     //平均
$Student->sum($col,[]);     //加總
*/

/*
echo "<hr>";
echo  $Student->sum("graduate_at",['dept'=>2]); 
*/

/*
//利用$Score 把簡寫的部分值分開都列出來
$Score=new DB("student_scores");
echo $Score->max('score');  //列出最大值
echo "<hr>";
echo $Score->min('score');  //列出最小值
echo "<hr>";
echo $Score->avg('score');  //列出平均值
echo "<hr>";
echo "整張資料表筆數：".$Student->count();  //列學生有幾個
echo "<hr>";
echo "dept為2的資料筆數:".$Student->count(['dept'=>2]);  
echo "<hr>";
*/


// 使用function q 來串接資料庫 再用q來呼叫查詢列表
$rows=q("select * from `dept` order by id desc")
dd($rows);


class DB{
    protected $table;
    protected $dsn="mysql:host=localhost;charset=utf8;dbname=school";
    protected $pdo;

    public function __construct($table)
    {
        $this->pdo=new PDO($this->dsn,'root','');
        $this->table=$table;
    }


    public function all(...$args){

        $sql="select * from $this->table ";
    
        if(isset($args[0])){
            if(is_array($args[0])){
                //是陣列 ['acc'=>'mack','pw'=>'1234'];
                //是陣列 ['product'=>'PC','price'=>'10000'];
    
                foreach($args[0] as $key => $value){
                    $tmp[]="`$key`='$value'";
                }
    
                $sql=$sql ." WHERE ". join(" && " ,$tmp);
            }else{
                //是字串
                $sql=$sql . $args[0];
            }
        }
    
        if(isset($args[1])){
            $sql = $sql . $args[1];
        }
    
        echo $sql;
        return $this->pdo
                    ->query($sql)
                    ->fetchAll(PDO::FETCH_ASSOC);
    
        }

   

// ------------查詢該ID的項目-------------------
    function find($id){
        $sql="select * from `$this->table` ";

        if(is_array($id)){
            foreach($id as $key => $value){
                $tmp[]="`$key`='$value'";
            }

            $sql = $sql . " where " . join(" && ",$tmp);

        }else{

            $sql=$sql . " where `id`='$id'";
        }
        //echo $sql;
        return $this->pdo->query($sql)->fetch(PDO::FETCH_ASSOC);
    }

// --------------刪除該ID的項目-------------------
    function del($id){
        $sql="delete from `$this->table` ";

        if(is_array($id)){
            foreach($id as $key => $value){
                $tmp[]="`$key`='$value'";
            }

            $sql = $sql . " where " . join(" && ",$tmp);

        }else{

            $sql=$sql . " where `id`='$id'";
        }

        echo $sql;
        return $this->pdo->exec($sql);

    }







// ---------目的-----想要把function 減少-------------------
// -------------新增 的寫法 -------------------

// INSERT INTO `students`(`uni_id`, `seat_num`, `name`, `birthday`, `national_id`, `address`, `parent`, `telphone`, `major`, `secondary`) VALUES ('[value-1]','[value-2]','[value-3]','[value-4]','[value-5]','[value-6]','[value-7]','[value-8]','[value-9]','[value-10]','[value-11]')



// -------------修改 的寫法 -------------------

// UPDATE `students` SET `id`='[value-1]',`uni_id`='[value-2]',`seat_num`='[value-3]',`name`='[value-4]',`birthday`='[value-5]',`national_id`='[value-6]',`address`='[value-7]',`parent`='[value-8]',`telphone`='[value-9]',`major`='[value-10]',`secondary`='[value-11]' WHERE 1


// -------------新增和修改 合併的寫法 -------------------

function save($array){
    if(isset($array['id'])){
        //更新update
        /*
        foreach($array as $key => $value){
            if($key!='id'){
                $tmp[]="`$key`='$value'";
            }
        }
        */
        $id=$array['id'];
        unset($array['id']);
        $tmp=$this->arrayToSqlArray($array);
        $sql ="update $this->table set ";
        $sql .=join(",",$tmp);
        $sql .=" where `id`='$id'";

    }else{
        //新增insert
        $cols=array_keys($array);
    
        $sql="insert into $this->table (`" . join("`,`",$cols) . "`) 
                                 values('" . join("','",$array) . "')";

    }

        //echo $sql;
        return $this->pdo->exec($sql);

}


// ----------------求數量-----------------
function count(...$arg){
     /*
    if(is_array($arg)){
        foreach($arg as $key => $value){
            $tmp[]="`$key`='$value'";
        }
        $sql="select count(*) from $this->table where ";
        $sql.=join(" && ",$tmp);
    }else{

        $sql="select count($arg) from $this->table";
    }
    */
    $sql=$this->mathSql('count','*',$arg); // 利用mathSql來簡寫 把所有的值都寫進mathSql
    echo $sql;
    return $this->pdo->query($sql)->fetchColumn();
}

// ----------------求加總-----------------
function sum($col,...$arg){
    /*
    if(isset($arg[0])){
        foreach($arg[0] as $key => $value){
            $tmp[]="`$key`='$value'";
        }
        $sql="select sum($col) from $this->table where ";
        $sql.=join(" && ",$tmp);
    }else{

        $sql="select sum($col) from $this->table";
    }
    */
    $sql=$this->mathSql('sum',$col,$arg); // 利用mathSql來簡寫 把所有的值都寫進mathSql
    echo $sql;
    return $this->pdo->query($sql)->fetchColumn();
}

// ---------------求最大值-----------------
function max($col,...$arg){
    /* 
    if(isset($arg[0])){
        foreach($arg[0] as $key => $value){
            $tmp[]="`$key`='$value'";
        }
        $sql="select max($col) from $this->table where ";
        $sql.=join(" && ",$tmp);
    }else{

        $sql="select max($col) from $this->table";
    }
    */
    $sql=$this->mathSql('max',$col,$arg); // 利用mathSql來簡寫 把所有的值都寫進mathSql
    echo $sql;
    return $this->pdo->query($sql)->fetchColumn();
}


// ---------------求最小值-----------------
function min($col,...$arg){
     /* 
    if(isset($arg[0])){
        foreach($arg[0] as $key => $value){
            $tmp[]="`$key`='$value'";
        }
        $sql="select min($col) from $this->table where ";
        $sql.=join(" && ",$tmp);
    }else{

        $sql="select min($col) from $this->table";
    }
    */
    $sql=$this->mathSql('min',$col,$arg); // 利用mathSql來簡寫 把所有的值都寫進mathSql
    echo $sql;
    return $this->pdo->query($sql)->fetchColumn();
}

// ---------------求平均-----------------
 function avg($col,...$arg){
        /* 
        if(isset($arg[0])){
            foreach($arg[0] as $key => $value){
                $tmp[]="`$key`='$value'";
            }
            $sql="select avg($col) from $this->table where ";
            $sql.=join(" && ",$tmp);
        }else{
            $sql="select avg($col) from $this->table";
        } 
        */

        //dd($arg);
        $sql=$this->mathSql('avg',$col,$arg);  // 利用mathSql來簡寫 把所有的值都寫進mathSql 

        echo $sql;
        return $this->pdo->query($sql)->fetchColumn();
    }


    private function mathSql($math,$col,...$arg){
        if(isset($arg[0][0])){
            /* foreach($arg[0][0] as $key => $value){
                $tmp[]="`$key`='$value'";
            } */
            $tmp=$this->arrayToSqlArray($arg[0][0]);
            $sql="select $math($col) from $this->table where ";
            $sql.=join(" && ",$tmp);
        }else{

            $sql="select $math($col) from $this->table";
        }

        return $sql;
    }

private function arrayToSqlArray($array){
    //dd($array);
    foreach($array as $key => $value){
        $tmp[]="`$key`='$value'";
    }
    return $tmp;
    }

}

function dd($array){
    echo "<pre>";
    print_r($array);
    echo "</pre>";
}



//萬用sql函式
function q($sql){
    $dsn="mysql:host=localhost;charset=utf8;dbname=school";
    $pdo=new PDO($dsn,'root','');
    //echo $sql;
    return $pdo->query($sql)->fetchAll();
}

//header函式
function to($location){
    header("location:$location");
}




?>


<!-- 

SOLID 是什麼。

SOLID 是5大原則的簡稱，分別為：
S = Single-responsibility principle (SRP) = 單一職責原則
O = Open–closed principle (OCP) = 開放封閉原則
L =Liskov substitution principle (LSP) = 里氏替換原則
I = Interface segregation principle (ISP) = 介面隔離原則
D = Dependency inversion principle (DIP) = 依賴反向原則


 -->