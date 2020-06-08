<?php

function domena() {
    $domena = preg_replace('/[^a-zA-Z0-9\.]/', '', $_SERVER['HTTP_HOST']);
    return $domena;
}

function navitem() {
    if (isset($_SESSION['id']) && isset($_SESSION['email'])){
        print '
          <li class="nav-item">
            <a class="nav-link" href="/add_image">Dodaj zdjęcie</a>
          </li>';
    } else {
        print '
          <li class="nav-item">
            <a class="nav-link" href="/register">Rejestracja</a>
          </li>';
    }
}

// function cropimage($imgname){
//     $im = imagecreatefromjpeg('galery/cropped/' . $imgname);

//     if($im) {
//         $size = min(imagesx($im), imagesy($im));
//         $im2 = imagecrop($im, ['x' => 0, 'y' => 0, 'width' => 250, 'height' => 250]);
//         if ($im2 !== FALSE) {
//             $dest = 'galery/cropped/' . $imgname;

//             imagejpeg($im2, $dest);
//             imagedestroy($im2);
//         }
//     }
//     imagedestroy($im);
// }