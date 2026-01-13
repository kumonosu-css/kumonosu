<?php

// プラグインが読み込むファイルハンドル一覧を表示
function dp_display_pluginhandles() {
  $wp_styles = wp_styles();
  $wp_scripts = wp_scripts();
  $handlename = '<dl><dt>Queuing styles</dt><dd><ul>';
  foreach( $wp_styles->queue as $handle ) :
    $handlename .=  '<li>' . $handle .'</li>';
  endforeach;
  $handlename .= '</ul></dd>';
  $handlename .= '<dt>Queuing scripts</dt><dd><ul>';
  foreach( $wp_scripts->queue as $handle ) :
    $handlename .=  '<li>' . $handle .'</li>';
  endforeach;
  $handlename .= '</ul></dd></dl>';
  return $handlename;
}
add_shortcode( 'pluginhandles', 'dp_display_pluginhandles');
