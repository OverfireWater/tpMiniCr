<?php
enum B: string
{
    case A = 'a';
}
var_dump(B::A == 'a');
