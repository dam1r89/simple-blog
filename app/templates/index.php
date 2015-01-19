<?php

$r = isset($_GET['r']) ? $_GET['r'] : '';
echo shell_exec('sblg-get . '.$r);