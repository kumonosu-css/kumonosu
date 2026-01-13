<?php

// 記事本文抜粋表示の文字数（マルチバイト）
function new_excerpt_mblength($length) {
  return 100;
}
add_filter('excerpt_mblength', 'new_excerpt_mblength');

// 抜粋の末尾
function new_excerpt_more($more) {
  return '...';
}
add_filter('excerpt_more', 'new_excerpt_more');

// 概要（抜粋）の文字数調整（WordPress標準）
function my_excerpt_length($length) {
  return 216;
}
add_filter('excerpt_length', 'my_excerpt_length');
