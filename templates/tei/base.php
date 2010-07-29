<?php

$book = new WP_Query(array('id'=>867, 'post_type'=>'books'));
$partsData = new WP_Query(array('post_parent'=>867, 'post_type'=>'parts'));
$libraryParts = new WP_Query(array('post_parent'=>868, 'post_type'=>'library_items'));

$author = get_userdata(1);
//$bookData = query_posts(array('child_o'=>867));

print_r($author);

print_r($book->post);
//var_dump($bookData);
print_r($partsData->posts);


die();
?>