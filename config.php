<?php
$basePath = __DIR__;
$publicPath = $_SERVER['REQUEST_URI'];
if ($publicPath === '/') {
  $publicPath = '';
}
if (strrpos($publicPath, '/') !== FALSE && strrpos($publicPath, '/') > 1) {
  $publicPath = substr($publicPath, 0, strrpos($publicPath, '/'));
}
