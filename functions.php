<?php

// functions/ ディレクトリ内のファイルを自動読み込み
foreach ( glob( get_template_directory() . '/functions/*.php' ) as $file ) {
  require_once $file;
}


