<?php
@unlink(__DIR__ . '/debug.txt');
@unlink(__FILE__);
echo "Cleaned!";
