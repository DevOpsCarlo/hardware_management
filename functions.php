<?php

function dd($value)
{
  echo "<pre>";
  var_dump($value);
  echo "</pre>";

  die();
}

function urlIs($value)
{
  return $_SERVER['REQUEST_URI'] === $value;
  // return strpos($_SERVER['REQUEST_URI'], $value) === 0;
}


function urlContains($value)
{
  return strpos($_SERVER['REQUEST_URI'], $value) !== false;
}
