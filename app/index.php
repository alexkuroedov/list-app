<?php

$host = 'db';
$db   = 'appdb';
$user = 'root';
$pass = '123456';
$charset = 'utf8';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$opt = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];
$pdo = new PDO($dsn, $user, $pass, $opt);


$stmt = $pdo->prepare('SELECT * FROM `comments`');
$stmt->execute();
$result = $stmt->fetchAll();

function findParents($comArr, $item){

    $parents = '';
    if($item['parent_id']!=0){
        $parents = $item['parent_id'];
        $parents .=','.findParents($comArr, $comArr[$item['parent_id']-1]);
    }

    return trim($parents,',');
}


$parents = findParents($result, $result[1]);

$comments = [];
foreach($result as $item){
    if($parents = findParents($result,$item)){
        $parr = explode(',',$parents);
        $childLevel = count($parr);
    }else{
        $parr = [];
        $childLevel = 0;
    }

    $comments[$item['id']] = [
        'id' => $item['id'],
        'text' => $item['text'],
        'parent_id' => $item['parent_id'],
        'child_level' => count($parr),
        'childs' => [],
        'deleted' => $item['deleted'],
    ];

    foreach($result as $chItem){
        if($chItem['parent_id']==$item['id']){
            $comments[$item['id']]['childs'][]  = $chItem['id'];
        }
    }
}

function listItemPrint($comments,$itemId, $num){
    $item = $comments[$itemId];
    
    if($item['deleted']!=1){
        static $rootNum = 0;
        
        if($item['parent_id']==0){
            $rootNum++;
            $num = $rootNum;
        }
        
        $childLevel = $item['child_level'];
        $text=$item['text'];
        
        print '<li class="list-group-item level'.$childLevel.'">'."<span>$num Main &nbsp;</span>$text</li>";
        
        if(empty($item['childs'])){
            $dopNum = 0;
        }
        
        $childNum = 1;
        foreach($item['childs'] as $chId){
            $tmpNum = $num;
            $tmpNum .= '.'.$childNum;
            listItemPrint($comments, $chId, $tmpNum);
            $childNum++;
        }
    }
              
}

function listItems($comments){

    // listItemPrint($comments, 2);
    foreach($comments as $item){
        if($item['parent_id']==0){
            listItemPrint($comments, $item['id'],1);
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

    <title>Document</title>
</head>
<body>
    <style>
        .level0{
            margin-left:0px;
        }
        .level1{
            margin-left:20px;
        }
        .level2{
            margin-left:40px;
        }
        .level3{
            margin-left:60px;
        }
        .wrap{
            margin-top: 60px;
        }
    </style>

    <div class="container wrap">
        <ul class="list-group">
           
           <?php print listItems($comments)?>
           
        </ul>
    </div>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
</body>
</html>
