<?php
$pdo = new PDO('mysql:host=mainline.proxy.rlwy.net;port=51274;dbname=misc', 
   'fred', 'zap');
// See the "errors" folder for details...
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);