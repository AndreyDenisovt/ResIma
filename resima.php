<!doctype html>
<html lang="en">
<head>
     <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>ResIma</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" integrity="sha384-DyZ88mC6Up2uqS4h/KRgHuoeGwBcD4Ng9SiP4dIRy0EXTlnuz47vAwmeGwVChigm" crossorigin="anonymous"/>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/js/bootstrap.min.js" integrity="sha384-VHvPCCyXqtD5DqJeNxl2dtTyhF78xXNXdkwX1CZeRusQfRKp+tA7hAShOK/B/fQ2" crossorigin="anonymous"></script>


    <link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css"/>
    <script type="text/javascript" src="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>


</head>
<body>
<?php

/*
*
* кнопка "вставить пути" - собирает список путей к файлам и копирует их в папку с названием "back-img"  /
* рядом кладет конвертированый файл с оригинальным названием                                            /
*
* создать папку(бекап) и поместить туда оригиналы файлов                                                /
* создать папку(конверты) и поместить туда конвертрованные копии картинок                               /
*
*/


$scandir = scandir(__DIR__);

$list_images = [];
$i=0;
foreach ($scandir as $k=>$val ){
    if (getimagesize($val) ){
        $imgname = explode(".",$val)[1];
        if($imgname != "ico"){
            $list_images[$i] = getimagesize($val);
            $list_images[$i]["filesrc"] = $val;
            $list_images[$i]["filename"] = explode(".",$val)[0];
            $list_images[$i]["filesize"] = filesize($val);
            $i++;
        }
    }
}

echo "<pre>";

//print_r($_GET);

echo "</pre>";

if (array_key_exists("strings",$_GET)){
    $arLinks = explode(" ",$_GET["strings"]);
    if (count($arLinks) > 0){
         foreach ($arLinks as $link){
             $serverName = stripos($link, $_SERVER["SERVER_NAME"]);
             if($serverName>0){
                 $trLink[] = substr($link,$serverName+strlen($_SERVER["SERVER_NAME"])+1);
             }elseif( stripos($link,"/")==0){
                 $trLink[] = ltrim($link,"/");
             }else{
                 $trLink[] = $link;
             }
         }
        echo "Список ссылок на изображения\n";
    }
}
if(array_key_exists("conv_img_curdir",$_GET)){
    array_pop($_GET);
    $webpImg = new Image();
    $resultArr = $webpImg->convToWebp($list_images);
    echo "Список конвертированных изображений текущей директории:\n";
    print_r($resultArr);
}



class Image{

    public function convertFromTo($inFileFormat,$outFileFormat){

    }
    public function convToWebp($inputArray){
        $returnArray = [];
        if (count($inputArray)>0){
            foreach ($inputArray as $image){
                switch ($image["mime"]){
                    case "image/jpeg":
                        $im = imagecreatefromjpeg($image["filesrc"]);
                        break;
                    case "image/gif":
                        $im = imagecreatefromgif($image["filesrc"]);
                        break;
                    case "image/png":
                        $im = imagecreatefrompng($image["filesrc"]);
                        break;
                    case "image/x-ms-bmp":
                        $im = imagecreatefromwbmp($image["filesrc"]);
                        break;
                    default:
                        $returnArray[] = $image["filesrc"]." - (non supported)";
                        break;
                }
                if ($im){
                    imagewebp($im, $image["filename"].'.webp');
                    $returnArray[] = $image["filesrc"];
                }else{
                    $returnArray[] = $image["filesrc"]." - (ERROR)";
                }
            }
        }
        return $returnArray;
    }
}

?>

<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="alert alert-warning text-center mb-4 p-2">
                <div class="h6 text-center">Форматы .bmp .png .jpg .gif</div>
                <a title="сбросить GET параметры" href="resima.php">Форматы ICO и SVG не поддерживаются !</a>
            </div>
        </div>
    </div>
</div>

<!--/*
    сделать вкладки для каждого инпута
*/-->

<div class="container" data-containerId="1">
    <div class="row">
        <div class="col-12">
            <div class="alert alert-info">
                <div class="h4 text-center">Список изображений в текущей директории:</div>
                <?if(count($list_images)>0):?>
                <form action="" class="">
                    <div class="list-images-k d-flex flex-wrap" name="list-images">
                        <?
                        foreach ($list_images as $key => $arImgInfo):?>
                            <div class="p-2" title="<?=$arImgInfo["filesize"]?> bytes | <?=$arImgInfo[0]?>x<?=$arImgInfo[1]?>  ">
                                <div class="prew-conv-img"  style="background-image: url('<?=$arImgInfo["filesrc"];?>')"></div>
                                <input id="<?=$key?>" type="checkbox" checked name="<?=$arImgInfo["filename"];?>" value="<?=$arImgInfo["filename"];?>">
                                <label for="<?=$key?>"><?=$arImgInfo["filesrc"];?></label>
                            </div>
                        <?endforeach;?>
                    </div>
                    <div class="btn btn-warning conv-img-curdir-spo">Конвертировать изображения в текущей директории</div>
                    <div class="conv-img-curdir-btn text-center d-none">
                        <input class="btn btn-success m-3" name="conv_img_curdir" value="Начать" type="submit">
                    </div>
                </form>
                <?else:?>
                    <div class="h6 text-center">Не найдено изображений</div>
                <?endif;?>
            </div>
        </div>
    </div>
</div>
<?/*

<div class="container" data-containerId="2">
    <div class="row">
        <div class="col-12">
            <div class="alert alert-info">
                <div class="h4 text-center">Список ссылок на изображения:</div>
                <p>вставлять без названия сайта, без первого слеша, через пробел. Например:</p>
                <span>local/templates/moguntia/images/about_bc.png local/templates/moguntia/images/advan1.png</span>
                <hr>
                <form action="" class="d-flex justify-content-between">
                    <textarea name="strings" class="p-2 border border-primary rounded strings" placeholder="Вставить список ссылок"></textarea>
                    <div>
                        <input type="submit" class="btn btn-info" value="Отправить">
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="container" data-containerId="3">
    <div class="row">
        <div class="col-12">
            <div class="alert alert-info">
                <div class="h4 text-center">Загрузить изображения:</div>
                <form action="" class="d-flex justify-content-between strigns">
                    <input type="file" multiple name="files[]" class="p-2 border border-primary rounded uploaded-files">
                    <button type="submit" class="btn btn-info">Отправить файлы</button>
                </form>
            </div>
        </div>
    </div>
</div>
*/?>
<!--/**/-->
<style>
    .list-images-k{
        max-height: 300px;
        overflow: auto;
    }
    .list-images-k.all{
        max-height: none;
    }
    form .strings,input.uploaded-files{
        width: 80%;
    }
    form .prew-conv-img{
        width: 150px;
        height: 100px;
        background-size: contain;
        background-repeat: no-repeat;
        background-position: center center;
    }
</style>
<!--/**/-->
<script>


    // $('.single-item').slick({
    //     dots: true,
    //     infinite: true,
    //     speed: 300,
    //     slidesToShow: 3,
    // });

    ////**////

    ////**////
    const cur_dir_spo = document.querySelector(".conv-img-curdir-spo");
    const conv_curdir_btn = document.querySelector(".conv-img-curdir-btn");

    cur_dir_spo.addEventListener("click", () => {
        if (cur_dir_spo.classList.contains("active")){
            cur_dir_spo.classList.remove("active");
            conv_curdir_btn.classList.remove("active");
            conv_curdir_btn.classList.add("d-none");
        }else{
            cur_dir_spo.classList.add("active");
            conv_curdir_btn.classList.add("active");
            conv_curdir_btn.classList.remove("d-none");
        }
    });

    console.log("aazazaza")
</script>
<!--/**/-->

</body>
</html>