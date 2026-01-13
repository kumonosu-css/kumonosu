<?php
$str='<ul class="breadcrumb" itemscope itemtype="http://schema.org/BreadcrumbList">';
if( is_front_page() || is_home() || is_404() ){

}elseif(is_page("")){
  $str.='  <li class="bred__item" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><a href="'.esc_url(home_url('/')).'" itemprop="item"><span itemprop="name">HOME</span></a><meta itemprop="position" content="1" /></li>';
  $str.='  <li class="bred__item" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><span itemprop="title">'.get_the_title().'</span></li>';

}elseif(is_archive()){//ここから
  if(is_month()) {
    $year = get_query_var('year');
    $monthnum = get_query_var('monthnum');
    $breadcrumb_date = $year . '年' .$monthnum. '月';

    $str.='  <li class="bred__item" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><a href="'.esc_url(home_url('/')).'" itemprop="item"><span itemprop="name">HOME</span></a><meta itemprop="position" content="1" /></li>';
    $str.='  <li class="bred__item" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><a href="'.esc_url(home_url('/')).esc_html(get_post_type_object(get_post_type())->name).'" itemprop="item"><span itemprop="title">'.esc_html(get_post_type_object(get_post_type())->label).'</span></a><meta itemprop="position" content="2" /></li>';
    $str.='  <li class="bred__item" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><span itemprop="title">'.$breadcrumb_date.'</span><meta itemprop="position" content="3" /></li>';
  }elseif(is_tax()) {
    $str.='  <li class="bred__item" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><a href="'.esc_url(home_url('/')).'" itemprop="item"><span itemprop="name">HOME</span></a><meta itemprop="position" content="1" /></li>';
    $str.='  <li class="bred__item" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><a href="'.esc_url(home_url('/')).esc_html(get_post_type_object(get_post_type())->name).'" itemprop="item"><span itemprop="title">'.esc_html(get_post_type_object(get_post_type())->label).'</span></a><meta itemprop="position" content="2" /></li>';
    $str.='  <li class="bred__item" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><span itemprop="title">'.single_term_title("", false).'</span><meta itemprop="position" content="3" /></li>';
  }else {
    $str.='  <li class="bred__item" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><a href="'.esc_url(home_url('/')).'" itemprop="item"><span itemprop="name">HOME</span></a><meta itemprop="position" content="1" /></li>';
    $str.='  <li class="bred__item" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><span itemprop="title">'.esc_html(get_post_type_object(get_post_type())->label).'</span><meta itemprop="position" content="2" /></li>';
  }
}elseif(is_single()){//ここから
  $str.='  <li class="bred__item" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><a href="'.esc_url(home_url('/')).'" itemprop="item"><span itemprop="name">HOME</span></a><meta itemprop="position" content="1" /></li>';
  $str.='  <li class="bred__item" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><a href="'.esc_url(home_url('/')).esc_html(get_post_type_object(get_post_type())->name).'" itemprop="item"><span itemprop="title">'.esc_html(get_post_type_object(get_post_type())->label).'</span></a><meta itemprop="position" content="2" /></li>';
  $str.='  <li class="bred__item" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><span itemprop="title">'.get_the_title().'</span><meta itemprop="position" content="3" /></li>';
}

else{

}
$str.='</ul>';

echo $str;
?>