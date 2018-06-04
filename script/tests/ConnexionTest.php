<?php

require ("../scriptLoader.php");

$traffickerId  = (new \App\Dfp\UserManager)->getUserId();

echo $traffickerId;